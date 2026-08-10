<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.read',
            'users.create',
            'users.update',
            'users.delete',

            'products.read',
            'products.create',
            'products.update',
            'products.delete',

            'purchases.read',
            'purchases.create',
            'purchases.update',
            'purchases.delete',

            'sales.read',
            'sales.create',
            'sales.update',
            'sales.delete',

            'customers.read',
            'customers.create',
            'customers.update',
            'customers.delete',

            'suppliers.read',
            'suppliers.create',
            'suppliers.update',
            'suppliers.delete',

            'testimonials.read',
            'testimonials.create',
            'testimonials.update',
            'testimonials.delete',

            'reports.read',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);

        $admin->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
