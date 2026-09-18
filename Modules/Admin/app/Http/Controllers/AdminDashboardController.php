<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AgentStepLog;
use App\Models\ApiCache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $userMetrics = [
            'total' => User::count(),
            'active' => User::where('is_suspended', false)->count(),
            'suspended' => User::where('is_suspended', true)->count(),
            'new_this_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $cacheMetrics = [
            'total_keys' => ApiCache::count(),
            'total_hits' => ApiCache::sum('hit_count') ?? 0,
            'expired' => ApiCache::where('expires_at', '<', now())->count(),
        ];

        $agentMetrics = [
            'total_steps' => AgentStepLog::count(),
            'failed_steps' => AgentStepLog::where('status', 'failed')->count(),
            'avg_duration_ms' => round((float) AgentStepLog::avg('duration_ms'), 2),
        ];

        $recentAudits = DB::table('admin_audit_logs')
            ->join('users', 'admin_audit_logs.admin_id', '=', 'users.id')
            ->select('admin_audit_logs.*', 'users.name as admin_name')
            ->orderByDesc('admin_audit_logs.created_at')
            ->limit(10)
            ->get();

        return view('admin::dashboard', compact('userMetrics', 'cacheMetrics', 'agentMetrics', 'recentAudits'));
    }

    public function create()
    {
        return view('admin::create');
    }

    public function store(Request $request) {}

    public function show($id)
    {
        return view('admin::show');
    }

    public function edit($id)
    {
        return view('admin::edit');
    }

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
