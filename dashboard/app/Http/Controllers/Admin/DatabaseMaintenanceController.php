<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DatabaseMaintenanceController extends Controller
{
    public function migrate(): RedirectResponse
    {
        return $this->runDatabaseCommand(
            'migrate',
            [],
            'Migrate database berhasil dijalankan.'
        );
    }

    public function migrateFresh(): RedirectResponse
    {
        return $this->runDatabaseCommand(
            'migrate:fresh',
            ['--force' => true],
            'Migrate fresh berhasil dijalankan (semua tabel lama dihapus).'
        );
    }

    public function migrateFreshSeed(): RedirectResponse
    {
        return $this->runDatabaseCommand(
            'migrate:fresh',
            ['--seed' => true, '--force' => true],
            'Migrate fresh + seed berhasil dijalankan.'
        );
    }

    private function runDatabaseCommand(string $command, array $options, string $successMessage): RedirectResponse
    {
        $admin = Auth::guard('admin_config')->user();

        if (!$admin || ($admin->role ?? null) !== 'super_admin') {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk super_admin.');
        }

        try {
            Artisan::call($command, $options);
            $output = trim(Artisan::output());

            if ($output !== '') {
                Log::info('Database maintenance output: ' . $output);
            }

            return redirect()->back()->with('success', $successMessage);
        } catch (\Throwable $e) {
            Log::error('Database maintenance error: ' . $e->getMessage(), [
                'command' => $command,
                'options' => $options,
            ]);

            return redirect()->back()->with('error', 'Gagal menjalankan command database: ' . $e->getMessage());
        }
    }
}
