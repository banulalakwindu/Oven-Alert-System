<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbRefreshAndSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh and seed the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('migrate:refresh');
        $this->call('db:seed');
    }

}
