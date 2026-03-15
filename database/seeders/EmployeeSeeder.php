<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            $this->command->error('No departments found. Please run DepartmentSeeder first.');
            return;
        }

        $employees = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice.johnson@example.com',
                'employee_id' => 'EMP002',
                'phone' => '+1234567893',
                'address' => '456 Oak Avenue',
                'position' => 'Software Developer',
                'salary' => 60000.00,
                'department_code' => 'IT001',
            ],
            [
                'name' => 'Bob Smith',
                'email' => 'bob.smith@example.com',
                'employee_id' => 'EMP003',
                'phone' => '+1234567894',
                'address' => '789 Pine Road',
                'position' => 'Financial Analyst',
                'salary' => 55000.00,
                'department_code' => 'FIN001',
            ],
            [
                'name' => 'Carol Davis',
                'email' => 'carol.davis@example.com',
                'employee_id' => 'EMP004',
                'phone' => '+1234567895',
                'address' => '321 Elm Street',
                'position' => 'Marketing Specialist',
                'salary' => 52000.00,
                'department_code' => 'MKT001',
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david.wilson@example.com',
                'employee_id' => 'EMP005',
                'phone' => '+1234567896',
                'address' => '654 Maple Lane',
                'position' => 'Sales Representative',
                'salary' => 48000.00,
                'department_code' => 'SAL001',
            ],
            [
                'name' => 'Eva Brown',
                'email' => 'eva.brown@example.com',
                'employee_id' => 'EMP006',
                'phone' => '+1234567897',
                'address' => '987 Cedar Court',
                'position' => 'Operations Coordinator',
                'salary' => 50000.00,
                'department_code' => 'OPS001',
            ],
            [
                'name' => 'Frank Miller',
                'email' => 'frank.miller@example.com',
                'employee_id' => 'EMP007',
                'phone' => '+1234567898',
                'address' => '147 Birch Drive',
                'position' => 'Customer Support Lead',
                'salary' => 45000.00,
                'department_code' => 'CUS001',
            ],
            [
                'name' => 'Grace Lee',
                'email' => 'grace.lee@example.com',
                'employee_id' => 'EMP008',
                'phone' => '+1234567899',
                'address' => '258 Spruce Way',
                'position' => 'R&D Engineer',
                'salary' => 65000.00,
                'department_code' => 'RND001',
            ],
            [
                'name' => 'Henry Taylor',
                'email' => 'henry.taylor@example.com',
                'employee_id' => 'EMP009',
                'phone' => '+1234567800',
                'address' => '369 Willow Street',
                'position' => 'IT Support Specialist',
                'salary' => 47000.00,
                'department_code' => 'IT001',
            ],
            [
                'name' => 'Ivy Martinez',
                'email' => 'ivy.martinez@example.com',
                'employee_id' => 'EMP010',
                'phone' => '+1234567801',
                'address' => '741 Ash Boulevard',
                'position' => 'HR Coordinator',
                'salary' => 49000.00,
                'department_code' => 'HR001',
            ],
            [
                'name' => 'Jack Anderson',
                'email' => 'jack.anderson@example.com',
                'employee_id' => 'EMP011',
                'phone' => '+1234567802',
                'address' => '852 Poplar Avenue',
                'position' => 'Marketing Manager',
                'salary' => 62000.00,
                'department_code' => 'MKT001',
            ],
        ];

        foreach ($employees as $employeeData) {
            // Find department by code
            $department = Department::where('code', $employeeData['department_code'])->first();
            
            if (!$department) {
                $this->command->warn("Department {$employeeData['department_code']} not found for {$employeeData['name']}. Assigning to first department.");
                $department = $departments->first();
            }

            // Create or get user
            $user = User::firstOrCreate(
                ['email' => $employeeData['email']],
                [
                    'name' => $employeeData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'employee',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Create or update employee
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $department->id,
                    'employee_id' => $employeeData['employee_id'],
                    'phone' => $employeeData['phone'],
                    'address' => $employeeData['address'],
                    'hire_date' => now()->subMonths(rand(1, 24)),
                    'position' => $employeeData['position'],
                    'salary' => $employeeData['salary'],
                    'face_registered' => false,
                ]
            );
        }

        $this->command->info(count($employees) . ' employees created successfully!');
    }
}