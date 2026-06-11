<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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
                'email'              => 'required|email|unique:students,email|unique:users,email',
                'phone'              => 'nullable|string',
                'status'             => 'nullable|integer',
                'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'date_of_birth'      => 'nullable|date',
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
                    'email'    => $validatedData['email'],
                    'password' => bcrypt('password'), // 🔒 លេខសម្ងាត់លំនាំដើមសម្រាប់សិស្ស៖ password
                    'role'     => 'student',          // កំណត់តួនាទីជាសិស្ស
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

            // ប្រើ Method ដែលមានក្នុង ClassRoom Model (សូមពិនិត្យមើលក្នុង ClassRoom.php ផង)
            // ប្រសិនបើក្នុង ClassRoom.php អ្នកដាក់ឈ្មោះ students() ត្រូវហៅ students()
            $students = $classRoom->student()
                ->with(['user', 'year'])
                ->get();

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
            'phone'              => 'sometimes|nullable|string',
            'status'             => 'sometimes|nullable|integer',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date_of_birth'      => 'sometimes|nullable|date',

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
                if ($student->user) {
                    $student->user->update([
                        'name'   => $request->name_en ?? $student->user->name,
                        'email'  => $request->email ?? $student->user->email,
                        'status' => $request->status ?? $student->user->status,
                    ]);
                }

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

        // ៤. ទាញយកបញ្ជីសិស្សតាមរយៈ Relationship (ត្រូវប្រើ () និងដាក់ឈ្មោះ Function ឱ្យត្រូវនឹង Model)
        // សន្មតថាក្នុង ClassRoom Model អ្នកបានកំណត់ relationship ឈ្មោះ students()
        $students = $classRoom->student()->with(['user', 'year'])->get();

        return response()->json(['data' => $students]);
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
}
