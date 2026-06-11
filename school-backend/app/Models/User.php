<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Auto hash password
    // public function setPasswordAttribute($value)
    // {
    //     $this->attributes['password'] = bcrypt($value);
    // }

    // ក្នុង class User extends Authenticatable
    public function teacher() // ដូរពី teachers មក teacher
    {
    return $this->hasOne(Teacher::class, 'user_id');
    }

    public function student() // ដូរពី students មក student
    {
    return $this->hasOne(Student::class, 'user_id');
    }
}