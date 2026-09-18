<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AgentStepLog;
use App\Models\ApiCache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use View;

class AdminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('admin::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('admin::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }
}
