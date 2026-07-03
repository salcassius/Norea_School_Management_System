<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'year_id', 'class_id', 'exam_date', 'start_time', 'end_time', 'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function year()
    {
        return $this->belongsTo(Year::class, 'year_id');
    }

    // ប្តូរឈ្មោះពី class() មកជា classRoom()
    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }
}