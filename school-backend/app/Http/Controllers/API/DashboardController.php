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
            },
            // ✅ ខ្វះមុន — នេះជាមូលហេតុដែល "មានច្បាប់" មិនបង្ហាញលើ column chart
            'attendance as excused_count' => function ($query) use ($date) {
                $query->where('status', 'excused')->whereDate('date', $date);
            }
        ])->get();

        \Illuminate\Support\Facades\Log::info("Classes counts: " . $classes->toJson());

        $chartClassesLabels = $classes->map(fn($c) => $c->grade_level . $c->name)->toArray();
        $chartPresentCounts = $classes->pluck('present_count')->toArray();
        $chartAbsentCounts = $classes->pluck('absent_count')->toArray();
        // ✅ បន្ថែម៖ ទាញយកចំនួន "មានច្បាប់" តាមថ្នាក់ ក្នុងលំដាប់ដូចគ្នានឹង labels
        $chartExcusedCounts = $classes->pluck('excused_count')->toArray();

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
                // ✅ បន្ថែម key នេះ — Dashboard.vue អាន data.chart_excused_counts ដោយផ្ទាល់
                'chart_excused_counts' => $chartExcusedCounts,
                'attendance_stats' => [85, 5, 7, 3] // គំរូ
            ]
        ]);
    }

    public function getTeacherDashboard(Request $request)
    {
        try {
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) return response()->json(['total_students' => 0, 'present_count' => 0, 'total_hours' => 0, 'today_schedule' => []]);

            $myClass = ClassRoom::where('teacher_id', $teacher->id)->first();
            $totalStudents = 0;
            $presentCount = 0;
            if ($myClass) {
                $totalStudents = $myClass->student()->count();
                if ($totalStudents > 0) {
                    $studentIds = $myClass->student()->pluck('students.id');
                    $presentCount = Attendance::where('status', 'present')
                        ->whereDate('date', now()->format('Y-m-d'))
                        ->whereIn('student_id', $studentIds)
                        ->count();
                    $presentCount = $presentCount ?? 0;
                }
            }

            // 💡 ប្រែប្រួលឈ្មោះថ្ងៃពី Carbon (English) ទៅជាភាសាខ្មែរ
            // ដូចគ្នានឹងទម្រង់ដែលប្រើក្នុងតារាង schedules (column: day)
            $dayMapping = [
                'Monday'    => 'ច័ន្ទ',
                'Tuesday'   => 'អង្គារ',
                'Wednesday' => 'ពុធ',
                'Thursday'  => 'ព្រហស្បតិ៍',
                'Friday'    => 'សុក្រ',
                'Saturday'  => 'សៅរ៍',
                'Sunday'    => 'អាទិត្យ',
            ];

            $todayEnglish = now()->format('l');       // ឧ. "Monday"
            $todayKhmer   = $dayMapping[$todayEnglish] ?? null;

            // ✅ Total hours សរុបប្រចាំសប្តាហ៍ (មិនកំណត់តែថ្ងៃនេះ)
            $totalHours = Schedule::where('teacher_id', $teacher->id)->count();

            // ✅ កាលវិភាគសម្រាប់ថ្ងៃនេះប៉ុណ្ណោះ
            $todaySchedules = Schedule::where('teacher_id', $teacher->id)
                ->where(function ($query) use ($todayKhmer, $todayEnglish) {
                    // គាំទ្រទាំងទម្រង់ខ្មែរ និង English ក្នុងករណីទិន្នន័យចម្រុះ
                    $query->where('day', $todayKhmer)
                          ->orWhere('day', $todayEnglish);
                })
                ->with(['subject', 'class'])
                ->orderBy('time')
                ->get();

            return response()->json([
                'total_students' => $totalStudents,
                'present_count' => $presentCount,
                'total_hours' => $totalHours,
                'today_schedule' => $todaySchedules->map(fn($s) => [
                    'id' => $s->id,
                    'time' => $s->time ?? '00:00',
                    'subject' => $s->subject->name ?? 'N/A',
                    'class_name' => $s->class
                        ? ($s->class->grade_level . $s->class->name)
                        : 'N/A',
                    'room' => $s->room ?? ''
                ])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}