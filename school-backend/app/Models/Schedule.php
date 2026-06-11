<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'year_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'day',
        'time',
        'shift',
        'room',
    ];

    // 🟢 ១. ទំនាក់ទំនងទៅកាន់តារាង Subjects
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // 🟢 ២. ទំនាក់ទំនងទៅកាន់តារាង Teachers
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    // 🟢 ៣. ទំនាក់ទំនងទៅកាន់តារាង Classes (ត្រូវតែឈ្មោះ class មិនមែន classroom)
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id'); 
        // 💡 ចំណាំ៖ បើ Model ថ្នាក់រៀនរបស់ប្អូនឈ្មោះ Classroom សូមប្តូរទៅ Classroom::class
    }

    // 🟢 ៤. ទំនាក់ទំនងទៅកាន់តារាង Academic Years
    public function year()
    {
        return $this->belongsTo(Year::class, 'year_id');
        // 💡 ចំណាំ៖ ពិនិត្យមើលឈ្មោះ Model ឆ្នាំសិក្សា ថាតើឈ្មោះ AcademicYear ឬ Year
    }
}