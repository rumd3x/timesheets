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
use App\Utils\Calculator;
use App\Repositories\TimestampRepository;

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

        $configuredPersonNameCell = AppSetting::where('name', AppSetting::SPREADSHEET_HEADER_PERSON_NAME)->first();
        if (!$configuredPersonNameCell) {
            throw new ConfigurationException(sprintf('Missing %s configuration', AppSetting::SPREADSHEET_HEADER_PERSON_NAME));
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
            Log::info("Generating {$user->first_name}'s timesheet");
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $worksheet->setCellValue($configuredPersonNameCell->value, $user->name);
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
                $user->first_name,
                $user->id,
                pathinfo($filePath, PATHINFO_EXTENSION)
            );

            Log::info("Saving {$user->first_name} timesheet");

            $writer = IOFactory::createWriter($spreadsheet, ucfirst(pathinfo($filePath, PATHINFO_EXTENSION)));
            $writer->save(Storage::disk('local')->path($outputFilename));

            Log::info("Dispatching {$user->first_name} timesheet mail");
            $message = new SpreadsheetMail();
            $message->attach(Storage::disk('local')->path($outputFilename));
            $recipients = [$user->email];
            if ($configuredRecipients && $configuredRecipients->value) {
                $recipients = array_merge($recipients, array_filter(explode(',', $configuredRecipients->value)));
            }

            Log::info("{$user->first_name} timesheet recipients:", $recipients);
            $message->to($recipients);
            $message->subject(sprintf('%s Timesheet %s', $user->first_name, $generationDate->format('F Y')));
            Mail::queue($message);
        }
    }

    /**
     * Get normalized timestamps
     *
     * @param Carbon $day
     * @param User $user
     * @return Carbon[]
     */
    private function getEntriesByDay(Carbon $day, User $user)
    {
        $entries = TimestampRepository::getByDay($day, $user);
        if ($entries->isEmpty()) {
            return [];
        }

        if ($entries->count() === 1 && $entries->first()->entry) {
            return [
                $entries->first()->carbon,
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries->first()->carbon->addMinute(),
            ];
        }

        if ($entries->count() === 1 && !$entries->first()->entry) {
            return [
                $entries->first()->carbon->subMinute(),
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries->first()->carbon,
            ];
        }

        if ($entries->count() === 2 && $entries->first()->entry && !$entries[1]->entry) {
            return [
                $entries->first()->carbon,
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries[1]->carbon,
            ];
        }

        if ($entries->count() === 2 && !$entries->first()->entry && $entries[1]->entry) {
            return [
                $entries->first()->carbon,
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries->first()->carbon->setTimeFromTimeString('00:00'),
                $entries[1]->carbon,
            ];
        }

        if ($entries->count() === 2 && $entries->first()->entry && $entries[1]->entry) {
            return [
                $entries->first()->carbon,
                $entries[1]->carbon->subMinute(),
                $entries[1]->carbon,
                $entries[1]->carbon->addMinute(),
            ];
        }

        if ($entries->count() === 2 && !$entries->first()->entry && !$entries[1]->entry) {
            return [
                $entries->first()->carbon->subMinute(),
                $entries->first()->carbon,
                $entries[1]->carbon->subMinute(),
                $entries[1]->carbon,
            ];
        }

        $earliestEntry = TimestampRepository::findEarliest($day, $user, Timestamp::ENTRY_STATE_ENTER);
        $latestEntry = TimestampRepository::findLatest($day, $user, Timestamp::ENTRY_STATE_ENTER);
        $earliestExit = TimestampRepository::findEarliest($day, $user, Timestamp::ENTRY_STATE_EXIT);
        $latestExit = TimestampRepository::findLatest($day, $user, Timestamp::ENTRY_STATE_EXIT);

        if (empty($earliestEntry)) {
            return [
                $entries->first()->carbon->subMinute(),
                $entries->first()->carbon,
                $entries[$entries->count()-1]->carbon->subMinute(),
                $entries[$entries->count()-1]->carbon,
            ];
        }

        if (empty($earliestExit)) {
            return [
                $entries->first()->carbon,
                $entries->first()->carbon->addMinute(),
                $entries[$entries->count()-1]->carbon,
                $entries[$entries->count()-1]->carbon->addMinute(),
            ];
        }

        $timeInsideInMinutes = Calculator::timeInside($day, $user);

        $rawSum = $earliestEntry->carbon->diffInMinutes($latestExit->carbon);

        $lunchTimeInMinutes = $rawSum - $timeInsideInMinutes;

        if ($latestExit->id === $earliestExit->id) {
            return [
                $earliestEntry->carbon,
                $latestEntry->carbon->subMinutes($lunchTimeInMinutes),
                $latestEntry->carbon,
                $latestExit->carbon,
            ];
        }

        return [
            $earliestEntry->carbon,
            $earliestExit->carbon,
            $earliestExit->carbon->addMinutes($lunchTimeInMinutes),
            $latestExit->carbon,
        ];
    }
}
