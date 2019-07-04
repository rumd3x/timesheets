<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupTimestamps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timestamps:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes duplicated and pointless timestamps to keep them from causing trouble.';

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
        //
    }
}
