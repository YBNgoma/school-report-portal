<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Dumps the PostgreSQL database to a custom-format file (restore with pg_restore).
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Dump the database to storage/app/backups using pg_dump';

    public function handle(): int
    {
        $connection = config('database.connections.pgsql');

        if (config('database.default') !== 'pgsql') {
            $this->error('Backups are only supported for the pgsql connection.');

            return self::FAILURE;
        }

        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $file = $directory.'/backup-'.now()->format('Y-m-d-His').'.dump';

        $result = Process::timeout(600)
            ->env(['PGPASSWORD' => $connection['password']])
            ->run([
                'pg_dump',
                '--host='.$connection['host'],
                '--port='.$connection['port'],
                '--username='.$connection['username'],
                '--format=custom',
                '--file='.$file,
                $connection['database'],
            ]);

        if (! $result->successful()) {
            File::delete($file);
            $this->error('pg_dump failed: '.trim($result->errorOutput()));

            return self::FAILURE;
        }

        $this->info("Database backed up to {$file}");

        return self::SUCCESS;
    }
}
