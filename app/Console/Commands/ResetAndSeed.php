<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetAndSeed extends Command
{
    protected $signature = 'db:reset-and-seed';
    protected $description = 'Reset database, run migrations and seed with initial data';

    public function handle()
    {
        if ($this->confirm('This will delete all existing data. Are you sure?')) {
            $this->info('Dropping all tables...');
            Schema::disableForeignKeyConstraints();
            
            $tables = DB::select('SHOW TABLES');
            $dbName = 'Tables_in_' . env('DB_DATABASE');
            
            foreach ($tables as $table) {
                $tableName = $table->$dbName;
                if ($tableName != 'migrations') {
                    DB::table($tableName)->truncate();
                    $this->line("Truncated: $tableName");
                }
            }
            
            Schema::enableForeignKeyConstraints();
            
            $this->info('Running migrations...');
            $this->call('migrate');
            
            $this->info('Running seeders...');
            $this->call('db:seed');
            
            $this->info('=====================================');
            $this->info('Database has been reset and seeded successfully!');
            $this->info('=====================================');
        }
    }
}