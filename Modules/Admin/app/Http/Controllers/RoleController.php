<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $paginator = Role::withCount('permissions')
            ->orderBy('id', 'asc')
            ->paginate(5)
            ->withQueryString();

        $roles = collect($paginator->items())->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'guard' => $role->guard_name ?? 'web',
                'permissions' => $role->permissions_count,
            ];
        });

        $page = $paginator->currentPage();
        $totalPages = $paginator->lastPage();
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $paginator->total();

        return view('admin::roles.index', compact(
            'roles',
            'paginator',
            'page',
            'totalPages',
            'from',
            'to',
            'total'
        ));
    }

    public function create()
    {
        $roleId = null;
        $roleName = '';
        $isSuperAdmin = false;
        $rolePermissions = [];
        $permissionGroups = $this->buildPermissionGroups();

        return view('admin::roles.edit', compact(
            'roleId',
            'roleName',
            'isSuperAdmin',
            'rolePermissions',
            'permissionGroups'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $permissions = $validated['permissions'] ?? [];
        $role->syncPermissions($permissions);

        $this->recordAudit($request, 'CREATE_ROLE', 'roles', (string) $role->id, null, [
            'name' => $role->name,
            'permissions' => $permissions,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' berhasil dibuat.");
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        $roleId = $role->id;
        $roleName = $role->name;
        $isSuperAdmin = ($role->name === 'super-admin');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $permissionGroups = $this->buildPermissionGroups();

        return view('admin::roles.edit', compact(
            'roleId',
            'roleName',
            'isSuperAdmin',
            'rolePermissions',
            'permissionGroups'
        ));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $isSuperAdmin = ($role->name === 'super-admin');

        $validated = $request->validate([
            'name' => [
                $isSuperAdmin ? 'nullable' : 'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->toArray(),
        ];

        if (!$isSuperAdmin && isset($validated['name'])) {
            $role->name = $validated['name'];
            $role->save();
        }

        if (!$isSuperAdmin) {
            $permissions = $validated['permissions'] ?? [];
            $role->syncPermissions($permissions);
        }

        $after = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->toArray(),
        ];

        $this->recordAudit($request, 'UPDATE_ROLE', 'roles', (string) $role->id, $before, $after);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    public function destroy(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'super-admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role super-admin tidak dapat dihapus.');
        }

        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->toArray(),
        ];

        $roleName = $role->name;
        $role->delete();

        $this->recordAudit($request, 'DELETE_ROLE', 'roles', (string) $id, $before, null);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' berhasil dihapus.");
    }

    protected function buildPermissionGroups(): array
    {
        $schema = [
            'Auth & Profile' => [
                'Profile' => [
                    'view' => 'profile.view',
                    'update' => 'profile.update',
                    'delete' => 'profile.delete',
                ],
            ],
            'Agent Workspace' => [
                'Chat Session' => [
                    'view' => 'agent.chat.view',
                    'create' => 'agent.chat.create',
                    'delete' => 'agent.chat.delete',
                ],
                'Report Export' => [
                    'PDF' => 'agent.report.export-pdf',
                    'Markdown' => 'agent.report.export-markdown',
                ],
                'Agent Tools' => [
                    'Screener' => 'agent.tools.screener',
                    'Financials' => 'agent.tools.financials',
                    'Valuation Matrix' => 'agent.tools.valuation-matrix',
                ],
            ],
            'Sectors Data' => [
                'Sectors Cache' => [
                    'view' => 'sectors.cache.view',
                    'flush' => 'sectors.cache.flush',
                ],
                'Sectors Metrics' => [
                    'view' => 'sectors.api.metrics.view',
                ],
            ],
            'Dashboard & Features' => [
                'Dashboard' => [
                    'view' => 'dashboard.view',
                ],
                'Watchlist' => [
                    'view' => 'watchlist.view',
                    'create' => 'watchlist.create',
                    'update' => 'watchlist.update',
                    'delete' => 'watchlist.delete',
                ],
                'Market Insights' => [
                    'view' => 'market-insights.view',
                ],
                'Telemetry & Quota' => [
                    'view' => 'telemetry.view-quota',
                ],
            ],
            'Admin Panel' => [
                'Dashboard' => [
                    'view' => 'admin.dashboard.view',
                ],
                'Users' => [
                    'view' => 'admin.users.view',
                    'manage' => 'admin.users.manage',
                ],
                'Market Articles' => [
                    'manage' => 'admin.insights.manage',
                ],
                'Prompt Starters' => [
                    'manage' => 'admin.prompts.manage',
                ],
                'System Cache' => [
                    'manage' => 'admin.system.cache-manage',
                ],
                'Audit Logs' => [
                    'view' => 'admin.audit-logs.view',
                ],
                'Roles & Access' => [
                    'view' => 'admin.roles.view',
                    'manage' => 'admin.roles.manage',
                ],
            ],
        ];

        return $schema;
    }

    protected function recordAudit(Request $request, $action, $targetTable, $targetId, $before = null, $after = null)
    {
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