<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_suspended', false)->count();
        $suspendedUsers = User::where('is_suspended', true)->count();

        $stats = [
            ['label' => 'Total Users', 'value' => number_format($totalUsers)],
            ['label' => 'Active Users', 'value' => number_format($activeUsers)],
            ['label' => 'Suspended Users', 'value' => number_format($suspendedUsers)],
        ];

        // 2. Query data users dengan pagination
        $paginator = User::with('roles')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        // 3. Mapping data user agar pas dengan format blade table & Alpine.js editUser
        $users = collect($paginator->items())->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name ?? 'User',
                'status' => $user->is_suspended ? 'inactive' : 'active',
                'created_at' => $user->created_at ? $user->created_at->format('M d, Y') : '-',
            ];
        });

        // 4. Data metadata untuk komponen pagination
        $page = $paginator->currentPage();
        $totalPages = $paginator->lastPage();
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = $paginator->total();

        // 5. List available roles dari Spatie untuk dropdown modal form
        $availableRoles = Role::pluck('name');

        return view('admin::users.index', compact(
            'stats',
            'users',
            'paginator',
            'page',
            'totalPages',
            'from',
            'to',
            'total',
            'availableRoles'
        ));
    }

    /**
     * Membuat akun user baru dan menugaskan role Spatie.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_suspended' => $validated['status'] === 'inactive',
        ]);

        // Assign role via Spatie
        $user->assignRole($validated['role']);

        $this->recordAudit($request, 'CREATE_USER', 'users', (string) $user->id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $validated['role'],
            'is_suspended' => $user->is_suspended,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Memperbarui detail akun, role Spatie, dan status suspend.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
            'is_suspended' => $user->is_suspended,
        ];

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_suspended' => $validated['status'] === 'inactive',
        ]);

        // Sync role via Spatie
        $user->syncRoles([$validated['role']]);

        $after = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $validated['role'],
            'is_suspended' => $user->is_suspended,
        ];

        $this->recordAudit($request, 'UPDATE_USER', 'users', (string) $user->id, $before, $after);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Toggle status suspend user dari tombol action table.
     */
    public function toggleSuspend(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $before = ['is_suspended' => $user->is_suspended];
        $user->is_suspended = !$user->is_suspended;
        $user->save();

        $action = $user->is_suspended ? 'SUSPEND_USER' : 'UNSUSPEND_USER';

        $this->recordAudit($request, $action, 'users', (string) $user->id, $before, [
            'is_suspended' => $user->is_suspended,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User account status has been changed.");
    }

    /**
     * Helper untuk mencatat log audit ke tabel admin_audit_logs.
     */
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