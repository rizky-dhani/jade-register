<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class DatabaseBackupController extends Controller
{
    public function download(string $filename)
    {
        if (! auth()->user()?->hasRole('Super Admin')) {
            abort(403, 'Unauthorized to download backups.');
        }

        $backupPath = storage_path("app/backups/{$filename}");

        if (! File::exists($backupPath)) {
            abort(404, 'Backup file not found.');
        }

        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'sqlite') {
            abort(400, 'Invalid file type.');
        }

        $mimeType = 'application/octet-stream';

        return response()->download($backupPath, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }
}
