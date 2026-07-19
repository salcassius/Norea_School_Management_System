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
        User::updateOrCreate(
            [
                'email' => 'admin@gmail.com'
            ],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'status' => true,
            ]
        );


        // Teacher
        User::updateOrCreate(
            [
                'email' => 'teacher01@gmail.com'
            ],
            [
                'name' => 'Teacher User',
                'password' => Hash::make('teacher123'),
                'role' => 'teacher',
                'status' => true,
            ]
        );


        // Student
        User::updateOrCreate(
            [
                'email' => 'student01@gmail.com'
            ],
            [
                'name' => 'Student User',
                'password' => Hash::make('student123'),
                'role' => 'student',
                'status' => true,
            ]
        );
    }
}