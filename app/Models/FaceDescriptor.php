<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceDescriptor extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'descriptor_data',
        'image_path',
        'confidence_score',
    ];

    protected $casts = [
        'confidence_score' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getDescriptorArray()
    {
        return json_decode($this->descriptor_data, true);
    }
}