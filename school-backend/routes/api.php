<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ClassController;
use App\Http\Controllers\API\SubjectController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\TeacherController;
use App\Http\Controllers\API\AcademicController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ExamController;

Route::post('/login', [AuthController::class, 'login']);

Route::get('years', [AcademicController::class, 'index']);
Route::get('classes', [ClassController::class, 'index']);
Route::get('subjects', [SubjectController::class, 'index']);
Route::get('teachers', [TeacherController::class, 'index']);
Route::get('schedules', [ScheduleController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    // Auth & User Management
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('users/stats', [UserController::class, 'getStats']);
    Route::apiResource('users', UserController::class);
    Route::get('/attendance/sheet', [AttendanceController::class, 'getAttendanceSheet']);
    Route::get('/attendance/sheet', [AttendanceController::class, 'getAttendanceSheet']);
    // ✅ កូដថ្មី៖ ប្តូរមកហៅ getStudentsByClass វិញឱ្យត្រូវតាមឈ្មោះ function ក្នុង StudentController
    // Route::get('/my-students', [StudentController::class, 'getMyStudents']);
    Route::get('classes/{class_id}/students', [StudentController::class, 'getStudentsByClass']);

    Route::middleware('role:admin,teacher')->group(function () {
        // មើលព័ត៌មានលម្អិតតាម ID (Show)
        Route::get('classes/{id}', [ClassController::class, 'show']);
        Route::get('subjects/{id}', [SubjectController::class, 'show']);
        Route::get('teachers/{id}', [TeacherController::class, 'show']);
        Route::get('years/{id}', [AcademicController::class, 'show']);
        Route::get('schedules/{id}', [ScheduleController::class, 'show']);

        Route::get('schedules', [ScheduleController::class, 'index']);
        Route::post('schedules', [ScheduleController::class, 'store']);

        // សម្រាប់សិស្ស
        Route::get('students', [StudentController::class, 'index']);
        Route::get('students/{id}', [StudentController::class, 'show']);

       

        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::get('/attendance/sheet', [AttendanceController::class, 'getAttendanceSheet']);
        Route::post('/attendance/save', [AttendanceController::class, 'saveAttendance']);

        Route::get('exams', [ExamController::class, 'index']);
        Route::get('exams/{id}', [ExamController::class, 'show']);

        // Dashboard Stats
        Route::get('/dashboard-stats', [DashboardController::class, 'getDashboardStats']);
    });


    Route::middleware('role:admin')->group(function () {
        // Classes: បង្កើត កែប្រែ លុប
        Route::post('classes', [ClassController::class, 'store']);
        Route::put('classes/{id}', [ClassController::class, 'update']);
        Route::delete('classes/{id}', [ClassController::class, 'destroy']);

        Route::get('classes/{class_id}/students', [StudentController::class, 'getStudentsByClass']);
        Route::post('/classes/{class_id}/assign-student', [ClassController::class, 'addStudentToClass']);


        // Students: បង្កើត កែប្រែ លុប
        Route::post('students', [StudentController::class, 'store']);
        Route::put('students/{id}', [StudentController::class, 'update']);
        Route::delete('students/{id}', [StudentController::class, 'destroy']);

        // Teachers: បង្កើត កែប្រែ លុប
        Route::post('teachers', [TeacherController::class, 'store']);
        Route::put('teachers/{id}', [TeacherController::class, 'update']);
        Route::delete('teachers/{id}', [TeacherController::class, 'destroy']);

        // Subjects: បង្កើត កែប្រែ លុប
        Route::post('subjects', [SubjectController::class, 'store']);
        Route::put('subjects/{id}', [SubjectController::class, 'update']);
        Route::delete('subjects/{id}', [SubjectController::class, 'destroy']);

        // Academic Years: បង្កើត កែប្រែ លុប
        Route::post('years', [AcademicController::class, 'store']);
        Route::put('years/{id}', [AcademicController::class, 'update']);
        Route::delete('years/{id}', [AcademicController::class, 'destroy']);
        Route::post('years/{id}/set-active', [AcademicController::class, 'setActive']);

        Route::post('schedules', [ScheduleController::class, 'store']);
        Route::put('schedules/{id}', [ScheduleController::class, 'update']);
        Route::delete('schedules/{id}', [ScheduleController::class, 'destroy']);

        Route::post('exams', [ExamController::class, 'store']);
        Route::put('exams/{id}', [ExamController::class, 'update']);
        Route::delete('exams/{id}', [ExamController::class, 'destroy']);

        Route::get('/admin/stats', [AcademicController::class, 'getStats']);
    });


    Route::middleware('role:teacher')->group(function () {
        Route::get('/teacher/dashboard', [DashboardController::class, 'getTeacherDashboard']);
        Route::get('/my-students', [StudentController::class, 'getMyStudents']);
        Route::get('/teacher/my-schedules', [ScheduleController::class, 'getTeacherSchedule']);
        
        // អ្នកអាចបន្ថែម Route ផ្សេងៗទៀតសម្រាប់គ្រូនៅទីនេះ
    });
});
