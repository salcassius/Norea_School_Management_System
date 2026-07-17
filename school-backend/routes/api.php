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
use App\Http\Controllers\API\ScoreController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('years', [AcademicController::class, 'index']);
Route::get('subjects', [SubjectController::class, 'index']);
Route::get('teachers', [TeacherController::class, 'index']);
Route::get('schedules', [ScheduleController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Common Universal API
    Route::get('users/stats', [UserController::class, 'getStats']);
    Route::apiResource('users', UserController::class);
    Route::get('classes/{class_id}/students', [StudentController::class, 'getStudentsByClass']);

    // Attendance Routes
    Route::get('/attendance/sheet', [AttendanceController::class, 'getAttendanceSheet']);
    Route::post('/attendance/save', [AttendanceController::class, 'saveAttendance']);
    Route::get('/attendance/report', [AttendanceController::class, 'getReport']);

    // =========================================================================
    // ✅ Admin & Teacher Shared Access (គ្រូបន្ទប់ថ្នាក់មានសិទ្ធិដូច Admin)
    // =========================================================================
    Route::middleware('role:admin,teacher')->group(function () {
        // គ្រប់គ្រងពិន្ទុ (Scores)
        Route::post('/scores/save', [ScoreController::class, 'saveScores']);
        Route::get('/scores', [ScoreController::class, 'getScores']);
        Route::get('/scores/export-pdf/exam/{examId}/class/{classId}', [ScoreController::class, 'exportPdf']);

        // គ្រប់គ្រងថ្នាក់ មុខវិជ្ជា និងសិស្ស (សម្រាប់មើល និងស្រង់ពិន្ទុ)
        Route::get('classes', [ClassController::class, 'index']);
        Route::get('classes/{id}', [ClassController::class, 'show']);
        Route::get('subjects/{id}', [SubjectController::class, 'show']);
        Route::get('teachers/{id}', [TeacherController::class, 'show']);
        Route::get('years/{id}', [AcademicController::class, 'show']);
        
        Route::get('schedules/{id}', [ScheduleController::class, 'show']);
        Route::post('schedules', [ScheduleController::class, 'store']);
        
        Route::get('students', [StudentController::class, 'index']);
        Route::get('students/{id}', [StudentController::class, 'show']);
        Route::get('students/{id}/history', [StudentController::class, 'getStudentHistory']);
        
        Route::get('exams', [ExamController::class, 'index']);
        Route::get('exams/{id}', [ExamController::class, 'show']);
        Route::get('/dashboard-stats', [DashboardController::class, 'getDashboardStats']);
    });

    // =========================================================================
    // Admin Only Access
    // =========================================================================
    Route::middleware('role:admin')->group(function () {
        Route::post('classes', [ClassController::class, 'store']);
        Route::put('classes/{id}', [ClassController::class, 'update']);
        Route::delete('classes/{id}', [ClassController::class, 'destroy']);
        Route::post('/classes/{class_id}/assign-student', [ClassController::class, 'addStudentToClass']);
        Route::delete('/classes/{class_id}/students/{student_id}', [ClassController::class, 'removeStudentFromClass']);

        Route::post('students', [StudentController::class, 'store']);
        Route::put('students/{id}', [StudentController::class, 'update']);
        Route::delete('students/{id}', [StudentController::class, 'destroy']);

        Route::post('teachers', [TeacherController::class, 'store']);
        Route::put('teachers/{id}', [TeacherController::class, 'update']);
        Route::delete('teachers/{id}', [TeacherController::class, 'destroy']);

        Route::post('subjects', [SubjectController::class, 'store']);
        Route::put('subjects/{id}', [SubjectController::class, 'update']);
        Route::delete('subjects/{id}', [SubjectController::class, 'destroy']);

        Route::post('years', [AcademicController::class, 'store']);
        Route::put('years/{id}', [AcademicController::class, 'update']);
        Route::delete('years/{id}', [AcademicController::class, 'destroy']);
        Route::post('years/{id}/set-active', [AcademicController::class, 'setActive']);

        Route::put('schedules/{id}', [ScheduleController::class, 'update']);
        Route::delete('schedules/{id}', [ScheduleController::class, 'destroy']);

        Route::post('exams', [ExamController::class, 'store']);
        Route::put('exams/{id}', [ExamController::class, 'update']);
        Route::delete('exams/{id}', [ExamController::class, 'destroy']);

        Route::get('/users/export-pdf', [UserController::class, 'exportUsersPdf']);
        Route::get('/admin/stats', [AcademicController::class, 'getStats']);
    });

    // =========================================================================
    // Teacher Only Specific Access
    // =========================================================================
    Route::middleware('role:teacher')->group(function () {
        Route::get('/teacher/dashboard', [DashboardController::class, 'getTeacherDashboard']);
        Route::get('/my-students', [StudentController::class, 'getMyStudents']);
        Route::get('/teacher/my-schedules', [ScheduleController::class, 'getTeacherSchedule']);
        Route::get('/teacher/attendance/schedules', [AttendanceController::class, 'getTeacherSchedulesToday']);
        Route::get('/teacher/attendance/students/{class_id}', [AttendanceController::class, 'getStudentsByClass']);
        Route::post('/teacher/attendance/save', [AttendanceController::class, 'saveAttendance']);
        Route::get('/teacher/classes', [TeacherController::class, 'getMyClasses']);
        Route::get('/attendance/my-report', [AttendanceController::class, 'getMyClassReport']);
    });
});