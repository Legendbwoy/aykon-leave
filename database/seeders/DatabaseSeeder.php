<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        
        // Clear tables in correct order
        $this->command->info('Clearing existing data...');
        
        // Run seeders in correct order
        $this->call(DepartmentSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(AttendanceSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(SettingsSeeder::class);
        
        $this->command->info('=====================================');
        $this->command->info('Database seeding completed successfully!');
        $this->command->info('=====================================');
        $this->command->info('');
        $this->command->info('Default Login Credentials:');
        $this->command->info('--------------------------');
        $this->command->info('Admin   : superadmin@aykon.com / Admin123!');
        $this->command->info('Manager : hr.manager@aykon.com / Manager123!');
        $this->command->info('Employee: john.doe@aykon.com / Employee123!');
        $this->command->info('');
        $this->command->info('All employee passwords: password123');
    }
}