<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

/**
 * Read-only view of recorded actions, newest first.
 */
class AuditLogController extends Controller
{
    public function index()
    {
        return view('audit.index', [
            'logs' => AuditLog::with('user')->latest('id')->limit(200)->get(),
        ]);
    }
}
