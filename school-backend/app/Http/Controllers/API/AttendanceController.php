<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private function getTodayKhmer()
    {
        $days = [
            0 => 'អាទិត្យ',
            1 => 'ច័ន្ទ',
            2 => 'អង្គារ',
            3 => 'ពុធ',
            4 => 'ព្រហស្បតិ៍',
            5 => 'សុក្រ',
            6 => 'សៅរ៍',
        ];

        return $days[Carbon::now()->dayOfWeek];
    }

   
    public function getTeacherSchedulesToday(Request $request)
    {
        $teacher = Teacher::where('user_id', $request->user()->id)->first();
        if (!$teacher) return response()->json(['message' => 'មិនឃើញទិន្នន័យគ្រូ'], 404);
        $todayKhmer = $this->getTodayKhmer();
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('day', $todayKhmer)
            ->with(['class'])
            ->get();
        return response()->json($schedules);
    }

   
    public function getStudentsByClass(Request $request, $class_id)
    {
        $date = $request->query('date', Carbon::now()->format('Y-m-d'));
        $classRoom = ClassRoom::find($class_id);
        if (!$classRoom) return response()->json(['message' => 'មិនឃើញថ្នាក់'], 404);
        $students = $classRoom->student()->select('students.id', 'students.name_kh', 'students.student_id_card')->get();
        $existing = Attendance::where('class_id', $class_id)->where('date', $date)->get()->keyBy('student_id');
        $data = $students->map(function ($student) use ($existing) {
            return [
                'student_id' => $student->id,
                'name_kh' => $student->name_kh,
                'student_id_card' => $student->student_id_card,
                'status' => $existing->has($student->id) ? $existing[$student->id]->status : 'present'
            ];
        });

        return response()->json($data);
    }

    public function saveAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'date' => 'required|date',
            'attendance' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $item) {
                $status = $item['status'] ?? 'present';

                Attendance::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'class_id' => $request->class_id,
                        'date' => $request->date
                    ],
                    [
                        'status' => $status,
                        'notes' => $item['notes'] ?? null
                    ]
                );
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'រក្សាទុកជោគជ័យ']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Attendance Save Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'មានបញ្ហានៅខាង Server: ' . $e->getMessage()], 500);
        }
    }

    public function getAttendanceSheet(Request $request)
    {
        try {
            $request->validate([
                'class_id' => 'required',
                'date' => 'required|date'
            ]);

            $class_id = $request->class_id;
            $date = $request->date;
            $students = DB::table('students')
                ->join('classroom_student', 'students.id', '=', 'classroom_student.student_id')
                ->where('classroom_student.class_id', $class_id)
                ->select('students.id', 'students.name_kh')
                ->get();
            $existing = Attendance::where('class_id', $class_id)
                ->where('date', $date)
                ->get()
                ->keyBy('student_id');
            $data = $students->map(function ($student) use ($existing) {
                return [
                    'student_id'   => $student->id,
                    'student_name' => $student->name_kh,
                    'status'       => $existing->has($student->id) ? $existing[$student->id]->status : null,
                    'notes'        => $existing->has($student->id) ? $existing[$student->id]->notes : ''
                ];
            });
            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យ!', 'error' => $e->getMessage()], 500);
        }
    }

  
    public function getReport(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'year_id' => 'required'
        ]);

        $class_id = $request->class_id;
        $year_id = $request->year_id;

        $report = Attendance::join('classes', 'attendance.class_id', '=', 'classes.id')
            ->where('attendance.class_id', $class_id)
            ->where('classes.year_id', $year_id)
            ->select(
                'attendance.student_id',
                DB::raw("COUNT(CASE WHEN attendance.status = 'present' THEN 1 END) as total_present"),
                DB::raw("COUNT(CASE WHEN attendance.status = 'absent' THEN 1 END) as total_absent"),
                DB::raw("COUNT(CASE WHEN attendance.status = 'excused' THEN 1 END) as total_excused"),
                DB::raw("COUNT(CASE WHEN attendance.status = 'late' THEN 1 END) as total_late")
            )
            ->groupBy('attendance.student_id')
            ->get();

        $data = $report->map(function ($item) {
            $student = Student::find($item->student_id);
            $total_attended = $item->total_present + $item->total_excused + $item->total_late;
            $total_days = $total_attended + $item->total_absent;

            $percentage = $total_days > 0 ? round(($total_attended / $total_days) * 100, 2) : 0;

            return [
                'student_id'    => $item->student_id,
                'student_name'  => $student ? $student->name_kh : 'N/A',
                'total_present' => $item->total_present,
                'total_absent'  => $item->total_absent,
                'total_excused' => $item->total_excused,
                'total_late'    => $item->total_late,
                'percentage'    => $percentage
            ];
        });

        $top_absentees = $data->sortByDesc('total_absent')->take(5)->values();

        return response()->json([
            'data' => $data,
            'top_absentees' => $top_absentees
        ]);
    }

   
    public function getMyClassReport(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'year_id'  => 'required'
        ]);

        $teacher = Teacher::where('user_id', $request->user()->id)->first();
        if (!$teacher) {
            return response()->json(['message' => 'មិនឃើញទិន្នន័យគ្រូ'], 404);
        }

        $class_id = $request->class_id;
        $year_id  = $request->year_id;

       
        $ownsClass = ClassRoom::where('id', $class_id)
            ->where('teacher_id', $teacher->id)
            ->exists();

        if (!$ownsClass) {
            return response()->json([
                'message' => 'អ្នកមិនមែនជាគ្រូបន្ទប់ថ្នាក់នេះទេ ដូច្នេះមិនអាចមើលរបាយការណ៍នេះបានទេ!'
            ], 403);
        }

        $report = Attendance::join('classes', 'attendance.class_id', '=', 'classes.id')
            ->where('attendance.class_id', $class_id)
            ->where('classes.year_id', $year_id)
            ->select(
                'attendance.student_id',
                DB::raw("COUNT(CASE WHEN attendance.status = 'present' THEN 1 END) as total_present"),
                DB::raw("COUNT(CASE WHEN attendance.status = 'absent' THEN 1 END) as total_absent"),
                DB::raw("COUNT(CASE WHEN attendance.status = 'excused' THEN 1 END) as total_excused"),
                DB::raw("COUNT(CASE WHEN attendance.status = 'late' THEN 1 END) as total_late")
            )
            ->groupBy('attendance.student_id')
            ->get();

        $data = $report->map(function ($item) {
            $student = Student::find($item->student_id);
            $total_attended = $item->total_present + $item->total_excused + $item->total_late;
            $total_days = $total_attended + $item->total_absent;

            $percentage = $total_days > 0 ? round(($total_attended / $total_days) * 100, 2) : 0;

            return [
                'student_id'    => $item->student_id,
                'student_name'  => $student ? $student->name_kh : 'N/A',
                'total_present' => $item->total_present,
                'total_absent'  => $item->total_absent,
                'total_excused' => $item->total_excused,
                'total_late'    => $item->total_late,
                'percentage'    => $percentage
            ];
        });

        $top_absentees = $data->sortByDesc('total_absent')->take(5)->values();

        return response()->json([
            'data' => $data,
            'top_absentees' => $top_absentees
        ]);
    }
}

     /**
     * ៤. មុខងារពិសេស៖ ឆែកមើលម៉ោងបង្រៀនជាក់ស្តែង (Active Schedule)
     * បើដល់ម៉ោងបង្រៀន ទើបអនុញ្ញាតឱ្យចូលកត់វត្តមាន
     */
    // public function getActiveAttendanceSheet(Request $request)
    // {
    //     $teacher = Teacher::where('user_id', $request->user()->id)->first();
    //     if (!$teacher) return response()->json(['message' => 'មិនឃើញទិន្នន័យគ្រូ'], 404);

    //     $todayKhmer = $this->getTodayKhmer(); 
    //     $currentTime = now()->format('H:i:s'); 

    //     // ឆែកមើលថាតើមានម៉ោងបង្រៀនឥឡូវនេះដែរឬទេ?
    //     $activeSchedule = Schedule::where('teacher_id', $teacher->id)
    //         ->where('day', $todayKhmer)
    //         ->where('start_time', '<=', $currentTime)
    //         ->where('end_time', '>=', $currentTime)
    //         ->with('class')
    //         ->first();

    //     if (!$activeSchedule) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'លោកគ្រូ/អ្នកគ្រូ មិនមានម៉ោងបង្រៀននៅក្នុងខណៈពេលនេះទេ!'
    //         ], 403);
    //     }

    //     $students = Student::whereHas('classroom', function($q) use ($activeSchedule) {
    //         $q->where('class_id', $activeSchedule->class_id);
    //     })->get();

    //     return response()->json([
    //         'success' => true,
    //         'schedule' => [
    //             'id' => $activeSchedule->id,
    //             'class_name' => ($activeSchedule->class->grade_level ?? '') . ($activeSchedule->class->name ?? ''),
    //             'time' => $activeSchedule->start_time . ' - ' . $activeSchedule->end_time
    //         ],
    //         'students' => $students
    //     ]);
    // }


