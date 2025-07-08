<?php

namespace Anwar\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SafetyCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anwar:safety-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if CRUD Generator package migrations are safe to run';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("🛡️  CRUD Generator Safety Check");
        $this->info(str_repeat("=", 50));

        // Check if package tables already exist
        $packageTables = [
            'anwar_crud_generator',
            'anwar_crud_menus',
            'admin_activity_logs'
        ];

        $existingTables = [];
        foreach ($packageTables as $table) {
            if (Schema::hasTable($table)) {
                $existingTables[] = $table;
            }
        }

        if (!empty($existingTables)) {
            $this->warn("⚠️  The following package tables already exist:");
            foreach ($existingTables as $table) {
                $this->line("   - {$table}");
            }
            $this->info("");
            $this->info("This means the package migrations have already been run.");
            $this->info("Running migrations again might cause conflicts.");
        } else {
            $this->info("✅ No package tables found. Migrations appear safe to run.");
        }

        // Check migration status
        $this->info("");
        $this->info("📋 Migration Status:");

        try {
            $migrations = DB::table('migrations')
                ->where('migration', 'like', '%anwar%')
                ->orWhere('migration', 'like', '%crud%')
                ->get();

            if ($migrations->isEmpty()) {
                $this->info("   No package migrations found in migrations table.");
            } else {
                foreach ($migrations as $migration) {
                    $this->line("   ✓ {$migration->migration}");
                }
            }
        } catch (\Exception $e) {
            $this->error("   Could not check migrations table: " . $e->getMessage());
        }

        // Safety recommendations
        $this->info("");
        $this->info("🔒 Safety Recommendations:");
        $this->info("   1. Always backup your database before running migrations");
        $this->info("   2. Test in development environment first");
        $this->info("   3. Review migration files before running them");
        $this->info("   4. Use version control for your project");

        // Show backup command
        $this->info("");
        $this->info("💾 Quick Backup Command:");
        $dbName = config('database.connections.' . config('database.default') . '.database');
        $this->line("   mysqldump -u user -p {$dbName} > backup_" . date('Y_m_d_His') . ".sql");

        return 0;
    }
}
