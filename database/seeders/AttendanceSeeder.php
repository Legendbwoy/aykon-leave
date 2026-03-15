<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        
        if ($employees->isEmpty()) {
            $this->command->error('No employees found. Please run EmployeeSeeder first.');
            return;
        }

        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($employees as $employee) {
            // Create attendance records for the last 30 days
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Skip weekends (Saturday and Sunday)
                if ($date->isWeekend()) {
                    continue;
                }

                // Randomly decide if employee was present (80% chance)
                if (rand(1, 100) <= 80) {
                    // Random check-in time between 8:30 AM and 9:30 AM
                    $checkInHour = rand(8, 9);
                    $checkInMinute = $checkInHour == 8 ? rand(30, 59) : rand(0, 30);
                    $checkIn = $date->copy()->setTime($checkInHour, $checkInMinute, rand(0, 59));

                    // Random check-out time between 4:30 PM and 6:30 PM
                    $checkOutHour = rand(16, 18);
                    $checkOutMinute = $checkOutHour == 16 ? rand(30, 59) : rand(0, 30);
                    $checkOut = $date->copy()->setTime($checkOutHour, $checkOutMinute, rand(0, 59));

                    // Calculate work hours
                    $workHours = $checkIn->diffInMinutes($checkOut) / 60;

                    // Determine status
                    $status = 'present';
                    if ($checkIn->format('H:i') > '09:15') {
                        $status = 'late';
                    } elseif ($workHours < 4) {
                        $status = 'half-day';
                    } elseif ($workHours > 9) {
                        $status = 'overtime';
                    }

                    Attendance::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'check_in' => $checkIn,
                        ],
                        [
                            'check_out' => $checkOut,
                            'check_in_method' => 'face',
                            'check_out_method' => 'face',
                            'check_in_confidence' => rand(85, 99) / 100,
                            'check_out_confidence' => rand(85, 99) / 100,
                            'work_hours' => round($workHours, 2),
                            'status' => $status,
                            'notes' => null,
                        ]
                    );
                } else {
                    // Employee was absent - create record with null check_in
                    Attendance::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'check_in' => null,
                            'date' => $date->toDateString(),
                        ],
                        [
                            'check_out' => null,
                            'check_in_method' => null,
                            'check_out_method' => null,
                            'check_in_confidence' => null,
                            'check_out_confidence' => null,
                            'work_hours' => 0,
                            'status' => 'absent',
                            'notes' => 'Absent',
                        ]
                    );
                }
            }
        }

        // Create today's attendance (partial - some checked in, some not)
        foreach ($employees as $employee) {
            if (rand(1, 100) <= 70) { // 70% checked in today
                $checkIn = Carbon::now()->setTime(rand(8, 10), rand(0, 59), rand(0, 59));
                
                Attendance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'check_in' => $checkIn,
                    ],
                    [
                        'check_out' => null,
                        'check_in_method' => 'face',
                        'check_out_method' => null,
                        'check_in_confidence' => rand(85, 99) / 100,
                        'check_out_confidence' => null,
                        'work_hours' => null,
                        'status' => $checkIn->format('H:i') > '09:15' ? 'late' : 'present',
                        'notes' => null,
                    ]
                );
            }
        }

        $this->command->info('Attendance records created successfully!');
    }
}