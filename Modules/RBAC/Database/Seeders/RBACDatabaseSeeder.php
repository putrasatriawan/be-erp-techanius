<?php

namespace Modules\RBAC\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RBACDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // contoh permission ERP (nanti per module)
            'user.view',
            'user.create',
            'user.update',
            'user.delete',

            'hr.employee.view',
            'hr.employee.create',
            'hr.employee.update',
            'hr.employee.delete',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'api']);
        }

        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);
        $admin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $staff      = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'api']);

        // superadmin bisa semua via Gate::before, tapi boleh juga sync semua permission biar kelihatan di UI
        $superadmin->syncPermissions(Permission::all());

        // role lain diset dari DB (bisa CRUD nanti)
        $admin->syncPermissions([
            'user.view',
            'user.create',
            'user.update',
            'hr.employee.view',
        ]);

        $staff->syncPermissions([
            'hr.employee.view',
        ]);
    }
}
