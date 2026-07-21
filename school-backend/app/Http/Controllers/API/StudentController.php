<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * បង្ហាញបញ្ជីឈ្មោះសិស្ស (និងគាំទ្រការស្វែងរកតាមឈ្មោះ ឬ កូដសិស្ស)
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'year']);

        // គាំទ្រការ Filter តាមឆ្នាំសិក្សា
        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        // 💡 Support filtering students by class_id for class detail suggested students
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // 💡 បន្ថែមការស្វែងរកសិស្សតាម ឈ្មោះ ឬ លេខកូដសិស្ស (សម្រាប់ Add Student Modal លើ Vue)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_kh', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('student_id_card', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->latest()->get(), 200);
    }


    public function store(Request $request)
    {
        try {
            // ១. ផ្ទៀងផ្ទាត់ទិន្នន័យពី Form (Validation)
            $validatedData = $request->validate([
                'year_id'            => 'nullable|exists:years,id',
                'class_id'           => 'nullable',
                'student_id_card'    => 'nullable|string|unique:students,student_id_card',
                'name_kh'            => 'required|string|max:255',
                'name_en'            => 'nullable|string|max:255',
                'gender'             => 'required|string|in:male,female,other',
                // 'password'        => 'required|string|min:6',
                'password'           => 'nullable|string|min:6',
                // 'email'              => 'required|email|unique:students,email|unique:users,email',
                'email' => 'nullable|email|unique:users,email|unique:students,email',
                'phone'              => 'nullable|string',
                'status'             => 'nullable|integer',
                'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'date_of_birth'      => 'nullable|date',
                'pri_school'        => 'nullable|string',
                'from_class'        => 'nullable|string',

                // POB Fields
                'pob_province'       => 'nullable|string',
                'pob_district'       => 'nullable|string',
                'pob_commune'        => 'nullable|string',
                'pob_village'        => 'nullable|string',
                // Address Fields
                'province'           => 'nullable|string',
                'district'           => 'nullable|string',
                'commune'            => 'nullable|string',
                'village'            => 'nullable|string',
                // Guardian Fields
                'guardian_mom_name'  => 'nullable|string',
                'guardian_mom_job'   => 'nullable|string',
                'guardian_mom_phone' => 'nullable|string',
                'guardian_dad_name'  => 'nullable|string',
                'guardian_dad_job'   => 'nullable|string',
                'guardian_dad_phone' => 'nullable|string',
                'enrollment_date'    => 'nullable|date',
            ]);

            return DB::transaction(function () use ($request, $validatedData) {

                // ២. បង្កើតគណនី User សម្រាប់សិស្សនេះជាមុនសិន ដើម្បីទទួលបាន user_id
                $username = $request->name_en ?? $request->name_kh;

                $user = \App\Models\User::create([
    'name'     => $username,
    'email'    => $validatedData['email'] ?? null,
    'password' => bcrypt('password'),
    'role'     => 'student',
    'status'   => $validatedData['status'] ?? 1,
]);

                // ៣. បញ្ចូល user_id ទៅក្នុងអារេកូដសិស្ស
                $validatedData['user_id'] = $user->id;

                // ៤. គ្រប់គ្រងការរក្សាទុករូបថត
                if ($request->hasFile('photo')) {
                    $path = $request->file('photo')->store('students', 'public');
                    $validatedData['photo'] = $path;
                }

                // ៥. 💡 ប្រព័ន្ធឆ្លាតវៃ៖ បើផ្ញើ class_id មក តែក្នុង DB ប្រើ classroom_id វានឹងប្តូរឱ្យស្វ័យប្រវត្ត
                $studentInstance = new Student();
                if (Schema::hasColumn($studentInstance->getTable(), 'classroom_id')) {
                    if (array_key_exists('class_id', $validatedData)) {
                        $validatedData['classroom_id'] = $validatedData['class_id'];
                        unset($validatedData['class_id']);
                    }
                }

                // ៦. បង្កើតទិន្នន័យសិស្សក្នុងតារាង students
                $student = Student::create($validatedData);

                return response()->json([
                    'success' => true,
                    'message' => 'បានបង្កើតទិន្នន័យសិស្ស និងគណនីប្រើប្រាស់ដោយជោគជ័យ',
                    'data'    => $student->load(['year', 'user'])
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'ទិន្នន័យបញ្ចូលមិនត្រឹមត្រូវ (អ៊ីមែល ឬលេខកូដសិស្សអាចមានរួចហើយ)!',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ការបង្កើតសិស្សបរាជ័យ៖ ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudentsByClass($class_id)
    {
        try {
            // ពិនិត្យថាតើថ្នាក់រៀនមានមែនដែរឬទេ
            $classRoom = \App\Models\ClassRoom::find($class_id);
            if (!$classRoom) {
                return response()->json(['message' => 'រកមិនឃើញថ្នាក់នេះទេ'], 404);
            }

            // Query students via the classroom_student pivot relation so we can
            // include pivot info (status, year_id, etc.) if needed.
            $students = $classRoom->student()
                ->with(['user', 'year'])
                ->get()
                ->map(function ($s) {
                    // expose pivot explicitly on the model for frontend convenience
                    $s->pivot_data = $s->pivot ?? null;
                    return $s;
                });

            return response()->json([
                'success' => true,
                'data' => $students
            ], 200);
        } catch (\Exception $e) {
            // នេះជាចំណុចសំខាន់៖ បើវានៅតែ Error ៥០០ វានឹងបង្ហាញសារនេះមក
            return response()->json(['error_message' => $e->getMessage()], 500);
        }
    }
    /**
     * កែប្រែទិន្នន័យសិស្ស (Full Update with Image Handling)
     */
    public function update(Request $request, $id)
    {
        // ស្វែងរកទិន្នន័យសិស្សពី Database
        $student = Student::with(['user'])->find($id);

        if (!$student) {
            return response()->json([
                'message' => 'រកមិនឃើញទិន្នន័យសិស្សដែលត្រូវកែប្រែឡើយ'
            ], 404);
        }

        // ១. Validation: បន្ថែម class_id និង classroom_id ឱ្យរត់បានទាំងពីរទម្រង់
        $validatedData = $request->validate([
            'year_id'            => 'sometimes|nullable|exists:years,id',
            'class_id'           => 'sometimes|nullable',
            'classroom_id'       => 'sometimes|nullable',
            'student_id_card'    => [
                'sometimes',
                'nullable',
                'string',
                Rule::unique('students')->ignore($student->id)
            ],
            'name_kh'            => 'sometimes|nullable|string|max:255',
            'name_en'            => 'sometimes|nullable|string|max:255',
            'gender'             => 'sometimes|nullable|in:male,female,other',
            'email'              => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('students')->ignore($student->id),
                Rule::unique('users')->ignore($student->user_id ?? 0)
            ],
            'password'           => 'nullable|string|min:6',
            'phone'              => 'sometimes|nullable|string',
            'status'             => 'sometimes|nullable|integer',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date_of_birth'      => 'sometimes|nullable|date',
            'pri_school'         => 'sometimes|nullable|string',
            'from_class'         => 'sometimes|nullable|string',

            // ទីកន្លែងកំណើត និង អាសយដ្ឋាន
            'pob_province'       => 'sometimes|nullable|string',
            'pob_district'       => 'sometimes|nullable|string',
            'pob_commune'        => 'sometimes|nullable|string',
            'pob_village'        => 'sometimes|nullable|string',
            'province'           => 'sometimes|nullable|string',
            'district'           => 'sometimes|nullable|string',
            'commune'            => 'sometimes|nullable|string',
            'village'            => 'sometimes|nullable|string',

            // ព័ត៌មានអាណាព្យាបាល
            'guardian_mom_name'  => 'sometimes|nullable|string',
            'guardian_mom_job'   => 'sometimes|nullable|string',
            'guardian_mom_phone' => 'sometimes|nullable|string',
            'guardian_dad_name'  => 'sometimes|nullable|string',
            'guardian_dad_job'   => 'sometimes|nullable|string',
            'guardian_dad_phone' => 'sometimes|nullable|string',
            'enrollment_date'    => 'sometimes|nullable|date',
        ]);

        try {
            return DB::transaction(function () use ($request, $student, &$validatedData) {

                // ២. គ្រប់គ្រងរូបថត (Image Handling)
                if ($request->hasFile('photo')) {
                    if ($student->photo) {
                        Storage::disk('public')->delete($student->photo);
                    }
                    $path = $request->file('photo')->store('students', 'public');
                    $validatedData['photo'] = $path;
                } else {
                    unset($validatedData['photo']);
                }

                // 💡 ប្រព័ន្ធឆ្លាតវៃ៖ បើផ្ញើ class_id មក តែក្នុង DB ប្រើ classroom_id វានឹងដូរឱ្យស្វ័យប្រវត្ត
                $studentInstance = new Student();
                if (Schema::hasColumn($studentInstance->getTable(), 'classroom_id')) {
                    if (array_key_exists('class_id', $validatedData)) {
                        $validatedData['classroom_id'] = $validatedData['class_id'];
                        unset($validatedData['class_id']);
                    }
                }

                // ៣. Update ទៅក្នុង Table Students
                $student->update($validatedData);

                // ៤. Update ទៅក្នុង Table Users
                // ផ្នែក Update ទៅក្នុង Table Users
                if ($student->user) {
                    // រៀបចំទិន្នន័យសម្រាប់ Update
                    $userData = [
                        'name'   => $request->name_en ?? $student->user->name,
                        'email'  => $request->email ?? $student->user->email,
                        'status' => $request->status ?? $student->user->status,
                    ];

                    // ពិនិត្យមើលថាតើមាន password ផ្ញើមកដែរឬទេ (បើមានទើប Update)
                    if ($request->filled('password')) {
                        $userData['password'] = bcrypt($request->password);
                    }

                    // អនុវត្តការ Update
                    $student->user->update($userData);
                }

                // ឆ្លើយតបទៅ Frontend វិញ
                return response()->json([
                    'message' => 'កែប្រែទិន្នន័យជោគជ័យ',
                    'data'    => $student->load(['year', 'user'])
                ], 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'ការកែប្រែបរាជ័យ៖ ' . $e->getMessage()
            ], 500);
        }
    }



    public function getMyStudents(Request $request)
    {
        // ១. យក User ID ដែលកំពុង Login
        $userId = auth()->id();

        // ២. រកមើលថា User នេះជាគ្រូណា នៅក្នុងតារាង teachers
        $teacher = \App\Models\Teacher::where('user_id', $userId)->first();

        if (!$teacher) {
            return response()->json(['message' => 'មិនមានព័ត៌មានគ្រូសម្រាប់ User នេះទេ'], 404);
        }

        // ៣. រកមើលថ្នាក់ដែលគ្រូនេះកាន់
        $classRoom = \App\Models\ClassRoom::where('teacher_id', $teacher->id)->first();

        if (!$classRoom) {
            return response()->json(['message' => 'លោកគ្រូមិនទាន់មានថ្នាក់ទទួលខុសត្រូវទេ'], 404);
        }

        $students = $classRoom->student()->with(['user', 'year'])->get();

        return response()->json(['data' => $students]);
    }

    /**
 * លុបទិន្នន័យសិស្ស (និងគណនី User ដែលពាក់ព័ន្ធ)
 */
public function destroy($id)
{
    $student = Student::with(['user'])->find($id);

    if (!$student) {
        return response()->json([
            'message' => 'រកមិនឃើញទិន្នន័យសិស្សដែលត្រូវលុបឡើយ'
        ], 404);
    }

    try {
        return DB::transaction(function () use ($student) {

            // ១. លុបឯកសាររូបថត (បើមាន)
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }

            // ២. រក្សាទុកព័ត៌មាន User ជាមុន មុននឹងលុប Student
            $user = $student->user;

            // ៣. លុបទិន្នន័យសិស្សពីតារាង students
            $student->delete();

            // ៤. លុបគណនី User ដែលភ្ជាប់ជាមួយសិស្សនេះ (បើមាន)
            if ($user) {
                $user->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'បានលុបទិន្នន័យសិស្សដោយជោគជ័យ'
            ], 200);
        });
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'ការលុបទិន្នន័យសិស្សបរាជ័យ៖ ' . $e->getMessage()
        ], 500);
    }
}


    // ក្នុង StudentController.php
    public function show($id)
    {
        $student = Student::with(['user', 'year'])->find($id);

        if (!$student) {
            return response()->json(['message' => 'រកមិនឃើញសិស្ស'], 404);
        }

        return response()->json($student, 200);
    }


    /**
 * ប្រវត្តិការសិក្សា + លទ្ធផលប្រឡង + វត្តមាន របស់សិស្សម្នាក់
 */
public function getStudentHistory($id)
{
    try {
        $student = Student::with(['user', 'year'])->find($id);

        if (!$student) {
            return response()->json([
                'message' => 'រកមិនឃើញសិស្ស'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | រកឈ្មោះ column ឆ្នាំសិក្សា
        |--------------------------------------------------------------------------
        */
        $yearColumns = Schema::getColumnListing('years');

        $yearNameColumn = collect([
            'name',
            'year_name',
            'title',
            'label',
            'year'
        ])->first(function ($column) use ($yearColumns) {
            return in_array($column, $yearColumns);
        }) ?? 'id';

        /*
        |--------------------------------------------------------------------------
        | 1. ប្រវត្តិចូលរៀន
        |--------------------------------------------------------------------------
        */
        $enrollments = DB::table('classroom_student')
            ->join('classes', 'classes.id', '=', 'classroom_student.class_id')
            ->leftJoin('years', 'years.id', '=', 'classroom_student.year_id')
            ->where('classroom_student.student_id', $id)
            ->select(
                'classroom_student.id',
                'classes.id as class_id',
                'classes.name as class_name',
                'classes.grade_level',
                "years.{$yearNameColumn} as year_name",
                'classroom_student.status',
                'classroom_student.created_at'
            )
            ->orderByDesc('classroom_student.created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 2. គណនាមេគុណសរុប
        |
        | ត្រូវដូច InputScore.vue:
        | ខ្មែរ និង គណិត = មេគុណ 2
        | មុខវិជ្ជាផ្សេង = មេគុណ 1
        |--------------------------------------------------------------------------
        */
        $subjects = Subject::select('id', 'name')->get();

        $totalCoefficient = $subjects->sum(function ($subject) {
            $subjectName = trim((string) $subject->name);

            if (
                str_contains($subjectName, 'គណិត') ||
                str_contains($subjectName, 'ខ្មែរ')
            ) {
                return 2;
            }

            return 1;
        });

        if ($totalCoefficient <= 0) {
            $totalCoefficient = 1;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. ទាញពិន្ទុតែ subject scores
        |
        | សំខាន់: កុំទាញ type = total មកបូកជាមួយ subject score
        |--------------------------------------------------------------------------
        */
        $scoreRows = DB::table('scores')
            ->join('exams', 'exams.id', '=', 'scores.exam_id')
            ->join('subjects', 'subjects.id', '=', 'scores.subject_id')
            ->leftJoin('classes', 'classes.id', '=', 'scores.class_id')
            ->leftJoin('years', 'years.id', '=', 'classes.year_id')
            ->where('scores.student_id', $id)
            ->where('scores.type', 'subject')
            ->select(
                'scores.exam_id',
                'scores.class_id',
                'scores.subject_id',
                'scores.score_value',
                'exams.name as exam_name',
                'subjects.name as subject_name',
                'classes.name as class_name',
                'classes.grade_level',
                "years.{$yearNameColumn} as year_name"
            )
            ->orderByDesc('scores.exam_id')
            ->orderBy('scores.subject_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4. ទាញ rank ពី row type = total
        |
        | Average មិនយកពី total row ទេ ព្រោះយើងគណនាថ្មីខាងក្រោម។
        |--------------------------------------------------------------------------
        */
        $savedTotals = DB::table('scores')
            ->where('student_id', $id)
            ->where('type', 'total')
            ->select(
                'exam_id',
                'class_id',
                'rank'
            )
            ->get()
            ->keyBy(function ($row) {
                return $row->exam_id . '-' . $row->class_id;
            });

        /*
        |--------------------------------------------------------------------------
        | 5. រាប់ចំនួនសិស្សក្នុងការប្រឡងនីមួយៗ
        |--------------------------------------------------------------------------
        */
        $studentCounts = DB::table('scores')
            ->where('type', 'total')
            ->select(
                'exam_id',
                'class_id',
                DB::raw('COUNT(DISTINCT student_id) as total_students')
            )
            ->groupBy('exam_id', 'class_id')
            ->get()
            ->keyBy(function ($row) {
                return $row->exam_id . '-' . $row->class_id;
            });

        /*
        |--------------------------------------------------------------------------
        | 6. រៀបចំ by_exam និង summary
        |--------------------------------------------------------------------------
        */
        $scoresByExam = [];
        $examSummary = [];

        $groupedScores = $scoreRows->groupBy(function ($row) {
            return $row->exam_id . '-' . $row->class_id;
        });

        foreach ($groupedScores as $examClassKey => $rows) {
            $firstRow = $rows->first();

            if (!$firstRow) {
                continue;
            }

            /*
             * ប្រើ exam name ជា key ដើម្បីត្រូវនឹង DetailStudent.vue
             */
            $examName = $firstRow->exam_name;

            /*
             * ការពារករណីឈ្មោះការប្រឡងដូចគ្នា
             * តែ class ខុសគ្នា
             */
            if (isset($scoresByExam[$examName])) {
                $examName = $firstRow->exam_name .
                    ' - ថ្នាក់ទី' .
                    ($firstRow->grade_level ?? '') .
                    ($firstRow->class_name ?? '');
            }

            /*
             * ពិន្ទុសរុប = បូកតែ subject scores
             */
            $totalScore = $rows->sum(function ($row) {
                return (float) $row->score_value;
            });

            /*
             * រូបមន្តដូច inputscore.vue:
             *
             * average = totalScore / totalCoefficient
             */
            $average = round($totalScore / $totalCoefficient, 2);

            /*
             * និទ្ទេស និងមូលវិចារ
             */
            if ($average >= 45) {
                $grade = 'A';
                $remark = 'ល្អប្រសើរ';
            } elseif ($average >= 40) {
                $grade = 'B';
                $remark = 'ល្អណាស់';
            } elseif ($average >= 35) {
                $grade = 'C';
                $remark = 'ល្អ';
            } elseif ($average >= 30) {
                $grade = 'D';
                $remark = 'ល្អបង្គួរ';
            } elseif ($average >= 25) {
                $grade = 'E';
                $remark = 'មធ្យម';
            } else {
                $grade = 'F';
                $remark = 'ធ្លាក់';
            }

            $savedTotal = $savedTotals->get($examClassKey);
            $studentCount = $studentCounts->get($examClassKey);

            /*
             * ពិន្ទុលម្អិតតាមមុខវិជ្ជា
             */
            $scoresByExam[$examName] = $rows->map(function ($row) {
                return [
                    'subject_id' => $row->subject_id,
                    'subject_name' => $row->subject_name,
                    'score_value' => (float) $row->score_value,
                ];
            })->values();

            /*
             * សង្ខេបលទ្ធផល
             */
            $examSummary[$examName] = [
                'exam_id' => $firstRow->exam_id,
                'class_id' => $firstRow->class_id,
                'class_name' => $firstRow->class_name,
                'grade_level' => $firstRow->grade_level,
                'year_name' => $firstRow->year_name,

                'total_score' => round($totalScore, 2),

                // តម្លៃនេះគណនាថ្មី ដូច InputScore.vue
                'average' => $average,

                'rank' => $savedTotal?->rank,
                'total_students' => $studentCount?->total_students ?? 0,

                'grade_kh' => $grade,
                'remark' => $remark,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | 7. វត្តមាន
        |--------------------------------------------------------------------------
        */
        $attendanceRecords = DB::table('attendance')
            ->where('student_id', $id)
            ->select('id', 'date', 'status', 'notes')
            ->orderByDesc('date')
            ->get()
            ->map(function ($record) {
                $record->notes = (
                    $record->notes &&
                    trim($record->notes) !== ''
                ) ? $record->notes : null;

                return $record;
            });

        $normalizeStatus = function ($status) {
            return match (strtolower((string) $status)) {
                'present', 'p' => 'present',
                'absent', 'a' => 'absent',
                'late', 'l' => 'late',
                'excused', 'permission', 'leave' => 'excused',
                default => strtolower((string) $status) ?: 'unknown',
            };
        };

        $groupedAttendance = $attendanceRecords->groupBy(function ($record) use ($normalizeStatus) {
            return $normalizeStatus($record->status);
        });

        $attendanceSummary = [
            'present' => $groupedAttendance->get('present', collect())->count(),
            'absent' => $groupedAttendance->get('absent', collect())->count(),
            'late' => $groupedAttendance->get('late', collect())->count(),
            'excused' => $groupedAttendance->get('excused', collect())->count(),
        ];

        $totalDays = $attendanceRecords->count();

        $attendedDays =
            $attendanceSummary['present'] +
            $attendanceSummary['late'] +
            $attendanceSummary['excused'];

        $attendanceSummary['percentage'] = $totalDays > 0
            ? round(($attendedDays / $totalDays) * 100, 2)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'student' => $student,
            'enrollments' => $enrollments,

            'scores' => [
                'by_exam' => $scoresByExam,
                'summary' => $examSummary,
            ],

            'attendance' => [
                'summary' => $attendanceSummary,
                'records' => $attendanceRecords,
            ],
        ], 200);

    } catch (\Throwable $e) {
        Log::error('Get Student History Error', [
            'student_id' => $id,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'message' => 'មានបញ្ហាក្នុងការទាញយកព័ត៌មានសិស្ស',
        ], 500);
    }
}
}