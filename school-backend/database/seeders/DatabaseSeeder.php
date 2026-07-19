<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
            'status' => true,
        ]);


        // Teacher
        User::create([
            'name' => 'Teacher User',
            'email' => 'teacher01@gmail.com',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
            'status' => true,
        ]);


        // Student
        User::create([
            'name' => 'Student User',
            'email' => 'student01@gmail.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'status' => true,
        ]);
    }
}