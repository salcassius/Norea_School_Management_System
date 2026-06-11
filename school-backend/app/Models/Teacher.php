<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teacher_id_card',
        'name_kh',
        'name_en',
        'specialty',
        'gender',
        'dob',
        // ទីកន្លែងកំណើត (POB) ដែលបំបែករួច
        'pob_province',
        'pob_district',
        'pob_commune',
        'pob_village',
        'email',
        'status',
        'phone',
        // អាសយដ្ឋានបច្ចុប្បន្ន (Current Address) ដែលបំបែករួច
        'province',
        'district',
        'commune',
        'village',
        'photo',
        'hire_date',
    ];

    // ទាក់ទងជាមួយ User (ម្ចាស់ Account)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $casts = [
        'specialty' => 'array',
        'status' => 'integer',
        'hire_date' => 'date',
        'dob' => 'date',
    ];

    // បង្កើត URL សម្រាប់រូបភាពស្វ័យប្រវត្តិ
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : asset('images/default-avatar.png');
    }

    public function class()
    {
        // គ្រូម្នាក់អាចបង្រៀនបានច្រើនថ្នាក់ (Has Many)
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }
}