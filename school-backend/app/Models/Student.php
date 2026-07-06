<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 'students';
    protected $fillable = [
        'user_id',
        'year_id', 
        'class_id',// កុំភ្លេចបន្ថែម year_id ព្រោះមានក្នុង Migration
        'student_id_card',
        'name_kh',
        'name_en',
        'photo',
        'date_of_birth',
        'pri_school',
        'from_class',
        
        // ✅ ទីកន្លែងកំណើត (POB) ដែលបំបែករួច
        'pob_province',
        'pob_district',
        'pob_commune',
        'pob_village',
        
        'gender',
        'email',
        'password',
        'phone',
        
        // ✅ អាសយដ្ឋានបច្ចុប្បន្ន (Current Address) ដែលបំបែករួច
        'province',
        'district',
        'commune',
        'village',

        // ✅ ព័ត៌មានអាណាព្យាបាល
        'guardian_mom_name',
        'guardian_mom_job',
        'guardian_mom_phone',
        'guardian_dad_name',
        'guardian_dad_job',
        'guardian_dad_phone',

        'enrollment_date',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'status' => 'integer',
    ];

    // ទាក់ទងជាមួយ User (ម្ចាស់ Account)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
    public function scores()
{
    return $this->hasMany(Score::class, 'student_id');
}

        

public function classroom()
{
    return $this->belongsToMany(ClassRoom::class, 'classroom_student', 'student_id', 'class_id')
                ->withPivot('status', 'year_id')
                ->withTimestamps();
}


    public function year()
    {
        return $this->belongsTo(Year::class, 'year_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    // បង្កើត URL សម្រាប់រូបភាពសិស្សស្វ័យប្រវត្តិ
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : asset('images/default-avatar.png');
    }
}