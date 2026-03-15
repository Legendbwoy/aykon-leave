<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Administration',
                'description' => 'Company administration and management',
                'code' => 'ADM001',
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Manages employee relations, recruitment, and company culture',
                'code' => 'HR001',
                'is_active' => true,
            ],
            [
                'name' => 'Information Technology',
                'description' => 'Handles all technical infrastructure and software development',
                'code' => 'IT001',
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'description' => 'Manages company finances, accounting, and budgeting',
                'code' => 'FIN001',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Handles marketing campaigns and brand management',
                'code' => 'MKT001',
                'is_active' => true,
            ],
            [
                'name' => 'Sales',
                'description' => 'Manages client relationships and sales operations',
                'code' => 'SAL001',
                'is_active' => true,
            ],
            [
                'name' => 'Operations',
                'description' => 'Oversees daily operations and logistics',
                'code' => 'OPS001',
                'is_active' => true,
            ],
            [
                'name' => 'Customer Support',
                'description' => 'Provides customer service and technical support',
                'code' => 'CUS001',
                'is_active' => true,
            ],
            [
                'name' => 'Research & Development',
                'description' => 'Focuses on innovation and product development',
                'code' => 'RND001',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['code' => $department['code']],
                $department
            );
        }

        $this->command->info(count($departments) . ' departments created successfully!');
    }
}