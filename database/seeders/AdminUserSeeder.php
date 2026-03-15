<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create departments
        $adminDept = Department::firstOrCreate(
            ['code' => 'ADM001'],
            [
                'name' => 'Administration',
                'description' => 'Administration Department',
                'is_active' => true,
            ]
        );

        $hrDept = Department::firstOrCreate(
            ['code' => 'HR001'],
            [
                'name' => 'Human Resources',
                'description' => 'HR Department',
                'is_active' => true,
            ]
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@attendance.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create admin employee record
        Employee::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'department_id' => $adminDept->id,
                'employee_id' => 'ADM001',
                'phone' => '+1234567890',
                'address' => 'Head Office',
                'hire_date' => now()->subYears(2),
                'position' => 'System Administrator',
                'salary' => 75000.00,
                'face_registered' => false,
            ]
        );

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@attendance.com'],
            [
                'name' => 'Department Manager',
                'password' => Hash::make('manager123'),
                'role' => 'manager',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create manager employee record
        Employee::firstOrCreate(
            ['user_id' => $manager->id],
            [
                'department_id' => $hrDept->id,
                'employee_id' => 'MGR001',
                'phone' => '+1234567891',
                'address' => 'HR Department',
                'hire_date' => now()->subYear(),
                'position' => 'HR Manager',
                'salary' => 65000.00,
                'face_registered' => false,
            ]
        );

        // Create test employee
        $employee = User::firstOrCreate(
            ['email' => 'employee@attendance.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('employee123'),
                'role' => 'employee',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create employee record
        Employee::firstOrCreate(
            ['user_id' => $employee->id],
            [
                'department_id' => $hrDept->id,
                'employee_id' => 'EMP001',
                'phone' => '+1234567892',
                'address' => '123 Main Street',
                'hire_date' => now()->subMonths(6),
                'position' => 'HR Assistant',
                'salary' => 45000.00,
                'face_registered' => false,
            ]
        );

        $this->command->info('Admin, Manager, and Test Employee users created successfully!');
        $this->command->info('Admin Login: admin@attendance.com / admin123');
        $this->command->info('Manager Login: manager@attendance.com / manager123');
        $this->command->info('Employee Login: employee@attendance.com / employee123');
    }
}