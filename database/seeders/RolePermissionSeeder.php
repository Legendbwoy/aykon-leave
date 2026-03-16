<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default roles
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full access to the system', 'is_active' => true],
            ['name' => 'manager', 'display_name' => 'Manager', 'description' => 'Can view and manage employees and reports', 'is_active' => true],
            ['name' => 'employee', 'display_name' => 'Employee', 'description' => 'Can clock in/out and view own attendance', 'is_active' => true],
        ];

        foreach ($roles as $roleData) {
            \App\Models\Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Default permissions (example set)
        $permissions = [
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'description' => 'Create/edit/delete users', 'group' => 'User Management', 'is_active' => true],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'description' => 'Create/edit/delete roles', 'group' => 'User Management', 'is_active' => true],
            ['name' => 'manage_permissions', 'display_name' => 'Manage Permissions', 'description' => 'Create/edit/delete permissions', 'group' => 'User Management', 'is_active' => true],
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'description' => 'Update application settings', 'group' => 'System', 'is_active' => true],
            ['name' => 'manage_attendance', 'display_name' => 'Manage Attendance', 'description' => 'View and edit attendance logs', 'group' => 'Attendance', 'is_active' => true],
        ];

        foreach ($permissions as $permissionData) {
            \App\Models\Permission::updateOrCreate(['name' => $permissionData['name']], $permissionData);
        }

        // Attach permissions to admin role
        $admin = \App\Models\Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(\App\Models\Permission::pluck('id')->toArray());
        }
    }
}
