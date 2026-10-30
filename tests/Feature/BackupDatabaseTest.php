<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class BackupDatabaseTest extends TestCase
{
    public function test_backup_runs_pg_dump_in_custom_format(): void
    {
        Process::fake(['*' => Process::result(exitCode: 0)]);

        $this->artisan('backup:database')->assertSuccessful();

        Process::assertRan(fn ($process) => $process->command[0] === 'pg_dump'
            && in_array('--format=custom', $process->command, true));
    }

    public function test_failed_dump_reports_an_error(): void
    {
        Process::fake(['*' => Process::result(errorOutput: 'connection refused', exitCode: 1)]);

        $this->artisan('backup:database')
            ->expectsOutputToContain('connection refused')
            ->assertFailed();
    }
}
