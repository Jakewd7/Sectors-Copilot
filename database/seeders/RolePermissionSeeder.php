<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'auth' => [
                'profile.view',
                'profile.update',
                'profile.delete',
            ],
            'agent' => [
                'agent.chat.create',
                'agent.chat.view',
                'agent.chat.delete',
                'agent.report.export-pdf',
                'agent.report.export-markdown',
                'agent.tools.screener',
                'agent.tools.financials',
                'agent.tools.valuation-matrix',
            ],
            'sectors-data' => [
                'sectors.cache.view',
                'sectors.cache.flush',
                'sectors.api.metrics.view',
            ],
            'dashboard' => [
                'dashboard.view',
                'watchlist.view',
                'watchlist.create',
                'watchlist.update',
                'watchlist.delete',
                'telemetry.view-quota',
                'market-insights.view',
            ],
            'admin' => [
                'admin.dashboard.view',
                'admin.insights.manage',
                'admin.prompts.manage',
                'admin.users.view',
                'admin.users.manage',
                'admin.system.cache-manage',
                'admin.audit-logs.view',
            ],
        ];

        $allPermissions = [];
        foreach ($modules as $moduleName => $permissions) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
                $allPermissions[$permissionName] = $permission;
            }
        }

        $investorRole = Role::firstOrCreate(['name' => 'investor', 'guard_name' => 'web']);
        $investorRole->syncPermissions([
            'profile.view',
            'profile.update',
            'dashboard.view',
            'market-insights.view',
            'watchlist.view',
            'watchlist.create',
            'watchlist.update',
            'watchlist.delete',
            'telemetry.view-quota',
            'agent.chat.create',
            'agent.chat.view',
            'agent.chat.delete',
            'agent.report.export-pdf',
            'agent.report.export-markdown',
            'agent.tools.screener',
            'agent.tools.financials',
            'agent.tools.valuation-matrix',
        ]);

        $analystRole = Role::firstOrCreate(['name' => 'analyst', 'guard_name' => 'web']);
        $analystRole->syncPermissions([
            'profile.view',
            'profile.update',
            'dashboard.view',
            'market-insights.view',
            'watchlist.view',
            'watchlist.create',
            'watchlist.update',
            'watchlist.delete',
            'telemetry.view-quota',
            'agent.chat.create',
            'agent.chat.view',
            'agent.report.export-pdf',
            'agent.report.export-markdown',
            'agent.tools.screener',
            'agent.tools.financials',
            'agent.tools.valuation-matrix',
            'sectors.cache.view',
            'sectors.api.metrics.view',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'profile.view',
            'profile.update',
            'dashboard.view',
            'admin.dashboard.view',
            'admin.insights.manage',
            'admin.prompts.manage',
            'admin.users.view',
            'admin.users.manage',
            'admin.system.cache-manage',
            'admin.audit-logs.view',
            'sectors.cache.view',
            'sectors.cache.flush',
            'sectors.api.metrics.view',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());
    }
}