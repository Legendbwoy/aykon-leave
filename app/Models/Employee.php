<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'employee_id',
        'phone',
        'address',
        'hire_date',
        'position',
        'salary',
        'profile_photo',
        'face_registered',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'face_registered' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function faceDescriptors()
    {
        return $this->hasMany(FaceDescriptor::class);
    }
}