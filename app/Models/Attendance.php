<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'check_in',
        'check_out',
        'check_in_method',
        'check_out_method',
        'check_in_photo',
        'check_out_photo',
        'check_in_confidence',
        'check_out_confidence',
        'work_hours',
        'status',
        'notes',
        'date',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'check_in_confidence' => 'decimal:2',
        'check_out_confidence' => 'decimal:2',
        'work_hours' => 'decimal:2',
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function calculateWorkHours()
    {
        if ($this->check_out) {
            $hours = $this->check_in->diffInMinutes($this->check_out) / 60;
            $this->work_hours = round($hours, 2);
            $this->save();
        }
    }

    public function determineStatus()
    {
        $checkInTime = $this->check_in;
        $officeStartTime = $checkInTime->copy()->setTime(9, 0, 0); // 9:00 AM
        $lateThreshold = $officeStartTime->copy()->addMinutes(15); // 9:15 AM

        if ($checkInTime->gt($officeStartTime->copy()->addHours(2))) {
            $this->status = 'absent';
        } elseif ($checkInTime->gt($lateThreshold)) {
            $this->status = 'late';
        } elseif ($this->check_out && $this->work_hours < 4) {
            $this->status = 'half-day';
        } elseif ($this->check_out && $this->work_hours > 9) {
            $this->status = 'overtime';
        } else {
            $this->status = 'present';
        }
        
        $this->save();
    }
}