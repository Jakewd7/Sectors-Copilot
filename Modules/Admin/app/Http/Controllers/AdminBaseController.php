<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBaseController extends Controller
{
    protected function recordAudit(
        Request $request,
        string $action,
        string $targetTable,
        ?string $targetId,
        ?array $before = null,
        ?array $after = null
    ): void {
        AdminAuditLog::create([
            'admin_id' => Auth::id(),
            'action' => strtoupper($action),
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'before_payload' => $before,
            'after_payload' => $after,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
