<?php

namespace App\Console\Commands;

use App\User;
use App\Timestamp;
use Carbon\Carbon;
use App\AppSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exceptions\ConfigurationException;
use App\Exceptions\InvalidJobArgumentException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SpreadsheetMail;

class GenerateSpreadsheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timesheet:generate
                            {month? : The month to generate the timesheet, defaults to last month}
                            {year? : The year to generate the timesheet, default to current year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Timesheet from Template';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $generationDate = Carbon::now()->subMonth()->day(1);

        if (is_numeric($this->argument('year')) && ($this->argument('year') <= 1000 || $this->argument('year') >= 3000)) {
            throw new InvalidJobArgumentException(sprintf('"%d" is not a valid year.', $this->argument('year')));
        }

        if (is_numeric($this->argument('month')) && ($this->argument('month') <= 0 || $this->argument('month') >= 13)) {
            throw new InvalidJobArgumentException(sprintf('"%d" is not a valid month.', $this->argument('month')));
        }

        if ($this->argument('month') !== null && $this->argument('year') === null) {
            $currentYear = Carbon::now()->format('Y');
            $generationDate = Carbon::parse(sprintf('%d-%d-01', $currentYear, $this->argument('month')));
        }

        if ($this->argument('year') !== null) {
            $generationDate = Carbon::parse(sprintf('%d-%d-01', $this->argument('year'), $this->argument('month')));
        }

        $configuredHeaderCell = AppSetting::where('name', AppSetting::SPREADSHEET_HEADER_MONTH_CELL)->first();
        if (!$configuredHeaderCell) {
            throw new ConfigurationException(sprintf('Missing %s configuration', AppSetting::SPREADSHEET_HEADER_MONTH_CELL));
        }

        $configuredHeaderFormat = AppSetting::where('name', AppSetting::SPREADSHEET_HEADER_MONTH_FORMAT)->first();
        if (!$configuredHeaderFormat) {
            Log::warning('Using default Y-m-d as header format');
            $configuredHeaderFormat = (object) ['value' => 'Y-m-d'];
        }

        $configuredTemplate = AppSetting::where('name', AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME)->first();
        if (!$configuredTemplate) {
            throw new ConfigurationException(sprintf('Missing %s configuration', AppSetting::SPREADSHEET_CURRENT_TEMPLATE_FILENAME));
        }

        $configuredInitialRow = AppSetting::where('name', AppSetting::SPREADSHEET_INITIAL_ROW)->first();
        if (!$configuredInitialRow) {
            throw new ConfigurationException(sprintf('Missing %s configuration', AppSetting::SPREADSHEET_INITIAL_ROW));
        }

        $configuredInitialColumn = AppSetting::where('name', AppSetting::SPREADSHEET_INITIAL_COLUMN)->first();
        if (!$configuredInitialColumn) {
            throw new ConfigurationException(sprintf('Missing %s configuration', AppSetting::SPREADSHEET_INITIAL_COLUMN));
        }

        $configuredRecipients = AppSetting::where('name', AppSetting::SPREADSHEET_GENERATION_EMAILS_REAL_RECIPIENTS)->first();
        if (!$configuredRecipients) {
            Log::warning('No Recipients configured!');
        }

        if (!Storage::disk('local')->exists($configuredTemplate->value)) {
            throw new ConfigurationException(sprintf('File "%s" on configuration does not exist', $configuredTemplate->value));
        }

        Log::info("Started {$generationDate->format('F')} timesheet generation");

        Storage::disk('local')->makeDirectory('generated');
        $filePath = Storage::disk('local')->path($configuredTemplate->value);

        foreach (User::all() as $user) {
            Log::info("Generating {$user->name} timesheet");
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $worksheet->setCellValue($configuredHeaderCell->value, $generationDate->format($configuredHeaderFormat->value));

            $currentDate = clone $generationDate;
            $currentRow = $configuredInitialRow->value;
            while ($currentDate->month === $generationDate->month) {
                $currentCol = $configuredInitialColumn->value;
                $entries = $this->getEntriesByDay($currentDate, $user);
                foreach ($entries as $entry) {
                    $cell = $worksheet->getCell(sprintf('%s%d', $currentCol, $currentRow));
                    $cell->setValue($entry->format('H:i'));
                    $currentCol++;
                }
                $currentDate->addDay();
                $currentRow++;
            }

            $outputFilename = sprintf(
                'generated%s%s Timesheet - %s[%d].%s',
                DIRECTORY_SEPARATOR,
                $generationDate->format('m. F'),
                $user->name,
                $user->id,
                pathinfo($filePath, PATHINFO_EXTENSION)
            );

            Log::info("Saving {$user->name} timesheet");

            $writer = IOFactory::createWriter($spreadsheet, ucfirst(pathinfo($filePath, PATHINFO_EXTENSION)));
            $writer->save(Storage::disk('local')->path($outputFilename));

            Log::info("Dispatching {$user->name} timesheet mail");
            $message = new SpreadsheetMail();
            $message->attach(Storage::disk('local')->path($outputFilename));
            $recipients = [$user->email];
            if ($configuredRecipients && $configuredRecipients->value) {
                $recipients = array_merge($recipients, array_filter(explode(',', $configuredRecipients->value)));
            }

            Log::info("{$user->name} timesheet recipients:", $recipients);
            $message->to($recipients);
            $message->subject(sprintf('%s Timesheet %s', $user->name, $generationDate->format('F Y')));
            Mail::queue($message);
        }
    }

    private function getEntriesByDay(Carbon $day, User $user)
    {
        $entriesCount = Timestamp::where('user_id', $user->id)->where('date', $day->format('Y-m-d'))->orderBy('time')->count();
        if ($entriesCount === 0) {
            return [];
        }

        $entries = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time')->get();
        if (count($entries) === 1 && $entries[0]->entry) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time))->addMinute(),
            ];
        }

        if (count($entries) === 1 && !$entries[0]->entry) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time))->subMinute(),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
            ];
        }

        if (count($entries) === 2 && $entries[0]->entry && !$entries[1]->entry) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s %s', $entries[1]->date, $entries[1]->time)),
            ];
        }

        if (count($entries) === 2 && !$entries[0]->entry && $entries[1]->entry) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s 0:00', $entries[0]->date)),
                Carbon::parse(sprintf('%s %s', $entries[1]->date, $entries[1]->time)),
            ];
        }

        if (count($entries) === 2 && $entries[0]->entry && $entries[1]->entry) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
                Carbon::parse(sprintf('%s %s', $entries[1]->date, $entries[1]->time))->subMinute(),
                Carbon::parse(sprintf('%s %s', $entries[1]->date, $entries[1]->time)),
                Carbon::parse(sprintf('%s %s', $entries[1]->date, $entries[1]->time))->addMinute(),
            ];
        }

        if (count($entries) === 2 && !$entries[0]->entry && !$entries[1]->entry) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time))->subMinute(),
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
                Carbon::parse(sprintf('%s %s', $entries[1]->date, $entries[1]->time))->subMinute(),
                Carbon::parse(sprintf('%s %s', $entries[1]->date, $entries[1]->time)),
            ];
        }

        $earliestEntry = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time')->where('entry', 1)->get()->first();
        $latestEntry = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time')->where('entry', 1)->get()->last();
        $earliestExit = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time')->where('entry', 0)->get()->first();
        $latestExit = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time')->where('entry', 0)->get()->last();

        if (empty($earliestEntry)) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time))->subMinute(),
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
                Carbon::parse(sprintf('%s %s', $entries[$entriesCount-1]->date, $entries[$entriesCount-1]->time))->subMinute(),
                Carbon::parse(sprintf('%s %s', $entries[$entriesCount-1]->date, $entries[$entriesCount-1]->time)),
            ];
        }

        if (empty($earliestExit)) {
            return [
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time)),
                Carbon::parse(sprintf('%s %s', $entries[0]->date, $entries[0]->time))->addMinute(),
                Carbon::parse(sprintf('%s %s', $entries[$entriesCount-1]->date, $entries[$entriesCount-1]->time)),
                Carbon::parse(sprintf('%s %s', $entries[$entriesCount-1]->date, $entries[$entriesCount-1]->time))->addMinute(),
            ];
        }

        $lastTs = null;
        $timeInsideInMinutes = 0;
        foreach ($entries as $ts) {
            if (!$lastTs) {
                $lastTs = $ts;
                continue;
            }

            if ($ts->entry == $lastTs->entry) {
                continue;
            }

            if ($lastTs->entry) {
                $timeInsideInMinutes += Carbon::parse("$lastTs->date $lastTs->time")->diffInMinutes(new Carbon("$ts->date $ts->time"));
            }

            $lastTs = $ts;
        }

        $rawSum = Carbon::parse(sprintf('%s %s', $earliestEntry->date, $earliestEntry->time))
                    ->diffInMinutes(Carbon::parse(sprintf('%s %s', $latestExit->date, $latestExit->time)));

        $lunchtTimeInMinutes = $rawSum - $timeInsideInMinutes;

        if ($latestExit->id === $earliestExit->id) {
            return [
                Carbon::parse(sprintf('%s %s', $earliestEntry->date, $earliestEntry->time)),
                Carbon::parse(sprintf('%s %s', $latestEntry->date, $latestEntry->time))->subMinutes($lunchtTimeInMinutes),
                Carbon::parse(sprintf('%s %s', $latestEntry->date, $latestEntry->time)),
                Carbon::parse(sprintf('%s %s', $latestExit->date, $latestExit->time)),
            ];
        }

        return [
            Carbon::parse(sprintf('%s %s', $earliestEntry->date, $earliestEntry->time)),
            Carbon::parse(sprintf('%s %s', $earliestExit->date, $earliestExit->time)),
            Carbon::parse(sprintf('%s %s', $earliestExit->date, $earliestExit->time))->addMinutes($lunchtTimeInMinutes),
            Carbon::parse(sprintf('%s %s', $latestExit->date, $latestExit->time)),
        ];
    }
}
