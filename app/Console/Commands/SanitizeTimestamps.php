<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class SanitizeTimestamps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timestamps:sanitize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure timestamps are well formed.';

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
        $timestamps = Timestamp::where('date', Carbon::yesterday()->format('Y-m-d'))->orderBy('time')->get();

        if (!$timestamps) {
            return;
        }

        
    }
}
