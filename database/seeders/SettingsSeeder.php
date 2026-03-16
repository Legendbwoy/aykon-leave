<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['key' => 'work_start_time', 'value' => '09:00', 'group' => 'attendance', 'description' => 'Official work start time'],
            ['key' => 'grace_period_minutes', 'value' => '15', 'group' => 'attendance', 'description' => 'Allowed grace period (in minutes) before an employee is marked late'],
            ['key' => 'work_end_time', 'value' => '17:00', 'group' => 'attendance', 'description' => 'Official work end time'],
            ['key' => 'enable_qr_checkin', 'value' => '1', 'group' => 'features', 'description' => 'Enable QR check-in functionality'],
            ['key' => 'enable_face_recognition', 'value' => '1', 'group' => 'features', 'description' => 'Enable face recognition check-in functionality'],
        ];

        foreach ($defaults as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
