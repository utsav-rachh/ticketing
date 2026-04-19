<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = AuditLog::with('user')->latest('created_at');

        if ($request->filled('user_id'))        $q->where('user_id', $request->input('user_id'));
        if ($request->filled('auditable_type')) $q->where('auditable_type', $request->input('auditable_type'));
        if ($request->filled('action'))         $q->where('action', $request->input('action'));
        if ($request->filled('from'))           $q->whereDate('created_at', '>=', $request->input('from'));
        if ($request->filled('to'))             $q->whereDate('created_at', '<=', $request->input('to'));

        $logs       = $q->paginate(50)->withQueryString();
        $types      = AuditLog::query()->select('auditable_type')->distinct()->pluck('auditable_type');
        $actions    = AuditLog::query()->select('action')->distinct()->pluck('action');

        return view('admin.audit-logs.index', compact('logs','types','actions'));
    }
}
