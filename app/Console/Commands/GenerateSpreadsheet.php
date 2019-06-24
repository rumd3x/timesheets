<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exceptions\InvalidJobArgumentException;
use App\AppSetting;
use App\Exceptions\ConfigurationException;

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
    protected $description = 'Generate Spreadsheet from Template';

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

        if (!Storage::disk('local')->exists($configuredTemplate->value)) {
            throw new ConfigurationException(sprintf('File "%s" on configuration does not exist', $configuredTemplate->value));
        }

        Storage::disk('local')->makeDirectory('generated');
        $filePath = Storage::disk('local')->path($configuredTemplate->value);
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $cell = $worksheet->getCell(sprintf('%s%s', $configuredInitialColumn->value, $configuredInitialRow->value));
        $cell->setValue('12:00');

        $writer = IOFactory::createWriter($spreadsheet, ucfirst(pathinfo($filePath, PATHINFO_EXTENSION)));
        $writer->save(Storage::disk('local')->path(sprintf('generated%s%s Timesheet.%s', DIRECTORY_SEPARATOR, $generationDate->format('m. F'), pathinfo($filePath, PATHINFO_EXTENSION))));
    }
}
