<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallApi extends Command
{
    // Define the command name and description
    protected $signature = 'install:api';
    protected $description = 'Installs API components, including Passport';

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
     * @return int
     */
    public function handle()
    {
        // Command logic here
        $this->info("API installation started...");
        // Include any setup steps, like Passport installation
        $this->call('passport:install');
        $this->info("API installation completed.");
        return 0;
    }
}
 