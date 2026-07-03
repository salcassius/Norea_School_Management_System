<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;
    protected $table = 'classes';
    protected $fillable = [
        'name',
        'year_id',
        'grade_level',
        'is_active',
        'teacher_id'
    ];

    public function student() 
{
    // ប្រាកដថា table, foreignKey និង relatedKey ត្រឹមត្រូវ
    return $this->belongsToMany(Student::class, 'classroom_student', 'class_id', 'student_id')
                ->withPivot('status', 'year_id')
                ->withTimestamps();
}

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // App\Models\Classes.php

    public function year()
    {
        // ត្រូវប្រើ AcademicYear::class ឬ Year::class ឱ្យត្រូវតាមឈ្មោះ Model ដែលអ្នកមាន
        return $this->belongsTo(Year::class, 'year_id');
    }


    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function attendance() {
    return $this->hasMany(Attendance::class, 'class_id'); 
}

}
