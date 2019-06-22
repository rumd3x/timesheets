<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Exceptions\InvalidJobArgumentException;

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

        dump($generationDate->format('Y-m-d'));
    }
}
