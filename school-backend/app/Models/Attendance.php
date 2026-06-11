<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
        'notes',
    ];
   
    protected $casts = [
        'date' => 'date', 
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}