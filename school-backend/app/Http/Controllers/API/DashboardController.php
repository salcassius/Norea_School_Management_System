<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        $yearId = $request->query('year_id');
        $dateInput = $request->query('date', now()->format('Y-m-d'));
        $date = Carbon::parse($dateInput)->format('Y-m-d');
        \Illuminate\Support\Facades\Log::info("Dashboard stats requested for date: " . $date . " and Year ID: " . $yearId);
        $totalStudents = Student::when($yearId, fn($q) => $q->where('year_id', $yearId))->count();
        $studentsMale = Student::when($yearId, fn($q) => $q->where('year_id', $yearId))->where('gender', 'male')->count();
        $studentsFemale = $totalStudents - $studentsMale;
        $totalTeachers = Teacher::count();
        $teachersMale = Teacher::where('gender', 'male')->count();
        $teachersFemale = $totalTeachers - $teachersMale;
        $totalUsers = User::count();
        $totalClasses = ClassRoom::when($yearId, fn($q) => $q->where('year_id', $yearId))->count();
        $classes = ClassRoom::when($yearId, function ($query) use ($yearId) {
            return $query->where('year_id', $yearId);
        })->withCount([
            'attendance as present_count' => function ($query) use ($date) {
                $query->where('status', 'present')->whereDate('date', $date);
            },
            'attendance as absent_count' => function ($query) use ($date) {
                $query->where('status', 'absent')->whereDate('date', $date);
            }
        ])->get();

        \Illuminate\Support\Facades\Log::info("Classes counts: " . $classes->toJson());

        $chartClassesLabels = $classes->map(fn($c) => $c->grade_level . $c->name)->toArray();
        $chartPresentCounts = $classes->pluck('present_count')->toArray();
        $chartAbsentCounts = $classes->pluck('absent_count')->toArray();

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
                'chart_present_counts' => $chartPresentCounts,
                'chart_absent_counts' => $chartAbsentCounts,
                'attendance_stats' => [85, 5, 7, 3] // គំរូ
            ]
        ]);
    }

    public function getTeacherDashboard(Request $request)
    {
        try {
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) return response()->json(['total_students' => 0, 'attendance_rate' => 0, 'total_hours' => 0, 'today_schedule' => []]);

            $myClass = ClassRoom::where('teacher_id', $teacher->id)->first();
            $totalStudents = 0;
            $attendanceRate = 0;

            if ($myClass) {
                $totalStudents = $myClass->student()->count();
                if ($totalStudents > 0) {
                    $studentIds = $myClass->student()->pluck('students.id');
                    $presentCount = Attendance::where('status', 'present')
                        ->whereDate('date', now()->format('Y-m-d'))
                        ->whereIn('student_id', $studentIds)
                        ->count();
                    $attendanceRate = ($presentCount / $totalStudents) * 100;
                }
            }

            $schedules = Schedule::where('teacher_id', $teacher->id)->with(['subject', 'class'])->get();
            return response()->json([
                'total_students' => $totalStudents,
                'attendance_rate' => round($attendanceRate),
                'total_hours' => $schedules->count(),
                'today_schedule' => $schedules->map(fn($s) => [
                    'id' => $s->id,
                    'time' => $s->time ?? '00:00',
                    'subject' => $s->subject->name ?? 'N/A',
                    'class_name' => $s->class->grade_level ?? 'N/A',
                    'room' => $s->room ?? 'N/A'
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}