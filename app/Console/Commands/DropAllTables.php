<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DropAllTables extends Command
{
    protected $signature = 'db:drop-all';
    protected $description = 'Drop all tables in the database';

    public function handle()
    {
        if (!$this->confirm('Are you sure you want to drop all tables? This cannot be undone!')) {
            return;
        }

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        
        // Column name varies by DB driver
        $tableName = 'Tables_in_' . env('DB_DATABASE');
        
        foreach ($tables as $table) {
            $dropQuery = "DROP TABLE IF EXISTS `{$table->$tableName}`";
            $this->info("Dropping table: {$table->$tableName}");
            DB::statement($dropQuery);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('All tables have been dropped!');
    }
} 