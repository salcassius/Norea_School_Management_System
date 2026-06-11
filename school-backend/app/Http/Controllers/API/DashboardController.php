<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom; // 💡 ប្រសិនបើ Model ថ្នាក់រៀនរបស់ប្អូនឈ្មោះ Class សូមប្តូរទៅជា Class::class
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * ទាញយកទិន្នន័យស្ថិតិរួមសម្រាប់បង្ហាញនៅលើផ្ទាំង Dashboard
     * URL: GET /api/dashboard-stats?year_id=X
     */
    public function getDashboardStats(Request $request)
    {
        $yearId = $request->query('year_id');

        // ១. រាប់ចំនួនទិន្នន័យសរុប និងបែងចែកតាមភេទ (ផ្អែកលើ year_id)
        $totalStudents = Student::when($yearId, function($query) use ($yearId) {
            return $query->where('year_id', $yearId);
        })->count();

        $studentsMale = Student::when($yearId, function($query) use ($yearId) {
            return $query->where('year_id', $yearId);
        })->where('gender', 'male')->count(); // 💡 ប្រសិនបើក្នុង DB ប្រើ 'M' ឬ 'ប្រុស' សូមប្តូរត្រង់នេះ

        $studentsFemale = $totalStudents - $studentsMale;

        $totalTeachers = Teacher::count();
        $teachersMale = Teacher::where('gender', 'male')->count(); 
        $teachersFemale = $totalTeachers - $teachersMale;

        $totalUsers = User::count();
        
        $totalClasses = ClassRoom::when($yearId, function($query) use ($yearId) {
            return $query->where('year_id', $yearId);
        })->count();

        // ២. ទាញទិន្នន័យសម្រាប់គូរ Bar Chart (បែងចែកតាមភេទ និងថ្នាក់សិក្សា)
        // 💡 បានកែប្រែពី 'students' មកជា 'student' ទៅតាមឈ្មោះ Relationship ក្នុង Model របស់ប្អូន
        $classes = ClassRoom::when($yearId, function($query) use ($yearId) {
            return $query->where('year_id', $yearId);
        })->withCount([
            'student as male_count' => function($query) {
                $query->where('gender', 'male');
            },
            'student as female_count' => function($query) {
                $query->where('gender', 'female');
            }
        ])->get();

        $chartClassesLabels = $classes->pluck('name')->toArray(); 
        $chartStudentsMale = $classes->pluck('male_count')->toArray();
        $chartStudentsFemale = $classes->pluck('female_count')->toArray();

        // ៣. ទិន្នន័យសម្រាប់គូរ Pie Chart (ស្ថិតិវត្តមានគ្រូ - ដាក់ជាទិន្នន័យគំរូសិន)
        $presentCount = 85;   
        $permissionCount = 5; 
        $lateCount = 7;       
        $absentCount = 3;     

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => $totalStudents,
                'students_male' => $studentsMale,
                'students_female' => $studentsFemale,
                'total_teachers' => $totalTeachers,
                'teachers_male' => $teachersMale,
                'teachers_female' => $teachersFemale,
                'total_users' => $totalUsers,
                'total_classes' => $totalClasses,
                'chart_classes_labels' => $chartClassesLabels,
                'chart_students_male' => $chartStudentsMale,
                'chart_students_female' => $chartStudentsFemale,
                'attendance_stats' => [$presentCount, $permissionCount, $lateCount, $absentCount]
            ]
        ]);
    }

    /**
     * ទាញយកទិន្នន័យសម្រាប់គ្រូ (Teacher Dashboard)
     */
    public function getTeacherDashboard(Request $request)
{
    $teacher = $request->user();
    $today = now()->format('l'); // ឈ្មោះថ្ងៃ (Monday, Tuesday...)

    // ១. កាលវិភាគថ្ងៃនេះ
    $schedules = \App\Models\Schedule::where('teacher_id', $teacher->id)
        ->where('day_of_week', $today)
        ->with(['class', 'subject'])
        ->get();

    // ២. ទាញសិស្សសរុប (ក្នុងថ្នាក់ដែលគ្រូនេះជាគ្រូប្រចាំថ្នាក់ - Homeroom)
    $myClass = \App\Models\ClassRoom::where('teacher_id', $teacher->id)->first();
    $totalStudents = $myClass ? \App\Models\Student::where('class_id', $myClass->id)->count() : 0;

    // ៣. គណនាវត្តមានសិស្ស (សន្មតថាមាន table 'attendances')
    // រាប់សិស្សដែលមកថ្ងៃនេះ ធៀបនឹងសិស្សសរុបក្នុងថ្នាក់
    $presentCount = \App\Models\Attendance::where('status', 'present')
        ->whereDate('created_at', today())
        ->whereIn('student_id', $myClass ? $myClass->students()->pluck('id') : [])
        ->count();
    
    $attendanceRate = $totalStudents > 0 ? ($presentCount / $totalStudents) * 100 : 0;

    return response()->json([
        'total_students' => $totalStudents,
        'attendance_rate' => round($attendanceRate),
        'total_hours' => $schedules->count() * 1.5, // សន្មតថា ១ ម៉ោង = 1.5h
        'today_schedule' => $schedules->map(fn($s) => [
            'id' => $s->id,
            'time' => "{$s->start_time} - {$s->end_time}",
            'subject' => $s->subject->name ?? 'N/A',
            'class_name' => $s->class->name ?? 'N/A',
            'room' => $s->room_number ?? 'N/A'
        ])
    ]);
}

}