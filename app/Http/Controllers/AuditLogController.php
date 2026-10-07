<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Read-only audit trail (admin only).
     *
     * Ordering/pagination is done in the database on purpose: the audit table
     * grows without bound, so it must never be loaded into memory for MergeSort.
     */
    public function index(Request $request)
    {
        // Defense in depth — the route middleware already enforces this.
        abort_unless($request->user()?->isAdmin(), 403);

        $logs = AuditLog::with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn ($w) => $w
                    ->where('description', 'like', $s)
                    ->orWhere('action', 'like', $s)
                    ->orWhere('module', 'like', $s)
                    ->orWhere('ip_address', 'like', $s)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $s)->orWhere('email', 'like', $s)));
            })
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->module))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('audit.index', [
            'logs'    => $logs,
            'users'   => User::orderBy('name', 'asc')->get(['id', 'name']),
            'actions' => AuditLog::distinct()->orderBy('action')->pluck('action'),
            'modules' => AuditLog::whereNotNull('module')->distinct()->orderBy('module')->pluck('module'),
        ]);
    }
}