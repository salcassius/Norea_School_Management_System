<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    use HasFactory;

    protected $table = 'years';

    protected $fillable = [
        'year_name', 
        'start_date', 
        'end_date', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date', // បន្ថែម casts ជា date ដើម្បីងាយស្រួល format ក្នុង Vue
        'end_date'   => 'date',
    ];


    public function student() {
    return $this->hasMany(Student::class, 'year_id');
    }
    /**
     * ទំនាក់ទំនងទៅកាន់ Table Classes
     */
    // public function classes() {
    //     // បញ្ជាក់ 'year_id' ជា Foreign Key ដើម្បីការពារការភាន់ច្រឡំ
    //     return $this->hasMany(Class::class, 'year_id');
    // }

    public function class(){
        return $this->hasMany(ClassRoom::class, 'year_id');
    }
}