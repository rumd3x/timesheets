<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EnsureEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:ensure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure .env file exists and is populated properly and consistently';

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
        if (!File::exists('.env')) {
            $this->info('Creating .env file');
            File::copy('.env.example', '.env');

            $this->call('key:generate');
        }

        $this->setEnvironmentValue('MAIL_DRIVER', $this->getOSEnvVar('MAIL_DRIVER'));
        $this->setEnvironmentValue('MAIL_HOST', $this->getOSEnvVar('MAIL_HOST'));
        $this->setEnvironmentValue('MAIL_PORT', $this->getOSEnvVar('MAIL_PORT'));
        $this->setEnvironmentValue('MAIL_USERNAME', $this->getOSEnvVar('MAIL_USERNAME'));
        $this->setEnvironmentValue('MAIL_PASSWORD', $this->getOSEnvVar('MAIL_PASSWORD'));
        $this->setEnvironmentValue('MAIL_ENCRYPTION', $this->getOSEnvVar('MAIL_ENCRYPTION'));

        $this->info('Environment file generated!');
    }

    private function getOSEnvVar(string $var, $default = '')
    {
        return getenv($var) ?: $default;
    }

    private function setEnvironmentValue(string $key, string $value)
    {
        $key = strtoupper($key);
        $file_content = explode("\n", File::get('.env'));

        $found = false;
        for ($i=0; $i < count($file_content); $i++) {
            if (strpos(strtoupper($file_content[$i]), $key) !== false) {
                $found = true;
                $file_content[$i] = "$key=$value";
            }
        }

        if ($found) {
            File::put('.env', implode("\n", $file_content));
            return;
        }

        File::append('.env', "$key=$value\n");
    }
}
