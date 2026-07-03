<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name']; // ថែម teacher_id បើមាន

    // Relationship ទៅកាន់ Class
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id'); 
        // ឬត្រឡប់ទៅ Class::class អាស្រ័យលើឈ្មោះ Model ថ្នាក់រៀនរបស់អ្នក
    }

    // 💡 បន្ថែម Relationship នេះដើម្បីបំបាត់ Error
    public function teacher() {
        return $this->belongsToMany(Teacher::class, 'subject_teacher', 'subject_id', 'teacher_id');
    }
}