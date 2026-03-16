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
            ['email' => 'superadmin@aykon.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('Admin123!'),
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
                'phone' => '+1-555-0100',
                'address' => 'Corporate Headquarters',
                'hire_date' => now()->subYears(3),
                'position' => 'Chief Technology Officer',
                'salary' => 120000.00,
                'face_registered' => false,
            ]
        );

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'hr.manager@aykon.com'],
            [
                'name' => 'Sarah Johnson',
                'password' => Hash::make('Manager123!'),
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
                'phone' => '+1-555-0101',
                'address' => 'HR Department, Building A',
                'hire_date' => now()->subYears(2),
                'position' => 'Human Resources Manager',
                'salary' => 85000.00,
                'face_registered' => false,
            ]
        );

        // Create test employee
        $employee = User::firstOrCreate(
            ['email' => 'john.doe@aykon.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('Employee123!'),
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
                'phone' => '+1-555-0102',
                'address' => '456 Employee Lane, Suite 100',
                'hire_date' => now()->subMonths(8),
                'position' => 'HR Coordinator',
                'salary' => 55000.00,
                'face_registered' => false,
            ]
        );

        $this->command->info('Admin, Manager, and Test Employee users created successfully!');
        $this->command->info('Admin Login: superadmin@aykon.com / Admin123!');
        $this->command->info('Manager Login: hr.manager@aykon.com / Manager123!');
        $this->command->info('Employee Login: john.doe@aykon.com / Employee123!');
    }
}