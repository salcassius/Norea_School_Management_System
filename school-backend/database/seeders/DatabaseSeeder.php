<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
{
    // Admin
    \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@gmail.com',
        'password' => 'admin1234',
        'role' => 'admin'
    ]);

    // Teacher ✅ (ត្រូវថែមត្រង់នេះ)
    \App\Models\User::create([
        'name' => 'Teacher User',
        'email' => 'teacher01@gmail.com',
        'password' => 'teacher123',
        'role' => 'teacher'
    ]);

    // Student ✅ (ត្រូវថែមត្រង់នេះ)
    \App\Models\User::create([
        'name' => 'Student User',
        'email' => 'student01@gmail.com',
        'password' => 'student123',
        'role' => 'student'
    ]);
}
}