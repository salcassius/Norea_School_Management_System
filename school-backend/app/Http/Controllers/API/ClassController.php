<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * ទាញយកបញ្ជីថ្នាក់រៀនទាំងអស់ រួមជាមួយព័ត៌មានឆ្នាំសិក្សា គ្រូ និងចំនួនសិស្ស
     */
    public function index(Request $request)
    {
        try {
            // ប្រាកដថា Relationship ក្នុង Model ClassRoom ឈ្មោះ year និង teacher (ឯកវចនៈ)
            $query = ClassRoom::with(['year', 'teacher'])->withCount('student');

            if ($request->filled('academic_year_id')) {
                $query->where('year_id', $request->academic_year_id);
            }

            return response()->json($query->latest()->get());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * បង្កើតថ្នាក់រៀនថ្មី
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'year_id'      => 'required|exists:years,id',
            'grade_level'  => 'required|integer|in:7,8,9',
            'is_active'    => 'boolean',
            'teacher_id'   => 'nullable|exists:teachers,id'
        ]);

        try {
            $class = ClassRoom::create([
                'name'         => $data['name'],
                'year_id'      => $data['year_id'],
                'grade_level'  => $data['grade_level'],
                'is_active'    => $request->is_active ?? true,
                'teacher_id'   => $data['teacher_id'] ?? null,
            ]);

            return response()->json($class->load(['year', 'teacher']), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * កែប្រែព័ត៌មានថ្នាក់រៀន
     */
    public function update(Request $request, $id)
    {
        try {
            $class = ClassRoom::findOrFail($id);

            $data = $request->validate([
                'name'         => 'required|string|max:255',
                'year_id'      => 'required|exists:years,id',
                'grade_level'  => 'required|integer|in:7,8,9',
                'is_active'    => 'boolean',
                'teacher_id'   => 'nullable|exists:teachers,id'
            ]);

            $class->update($data);
            
            // កែពី load(['years', 'teachers']) មកជា ['year', 'teacher'] ឱ្យត្រូវតាម index
            return response()->json($class->load(['year', 'teacher']));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * លុបថ្នាក់រៀន (លុបបានលុះត្រាតែគ្មានសិស្សនៅក្នុងថ្នាក់)
     */
    public function destroy($id)
    {
        try {
            $class = ClassRoom::findOrFail($id);

            // ប្រាកដថាមាន function student() ក្នុង Model ClassRoom ដូចដែលប្អូនបានកំណត់ក្នុង withCount
            if ($class->student()->count() > 0) {
                return response()->json(['message' => 'មិនអាចលុបបានទេ ព្រោះមានសិស្សក្នុងថ្នាក់នេះ!'], 400);
            }

            $class->delete();
            return response()->json(['message' => 'លុបថ្នាក់រៀនជោគជ័យ']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * ស្វែងរកសិស្ស (តាម ID Card ឬឈ្មោះ) ដើម្បីត្រៀមបញ្ចូលទៅក្នុងថ្នាក់
     */
    public function findStudent(Request $request)
    {
        $search = $request->query('search'); // ទទួលយកតម្លៃពី ?search=...

        if (!$search) {
            return response()->json(['message' => 'សូមបញ្ចូលព័ត៌មានដើម្បីស្វែងរក'], 400);
        }

        $student = Student::with(['user', 'year'])
            ->where(function($query) use ($search) {
                $query->where('student_id_card', $search) // ស្វែងរកតាម ID Card
                      ->orWhere('name_kh', 'LIKE', "%{$search}%") // ស្វែងរកតាមឈ្មោះខ្មែរ
                      ->orWhere('name_en', 'LIKE', "%{$search}%"); // ស្វែងរកតាមឈ្មោះអង់គ្លេស
            })
            ->first(); // យកតែម្នាក់ដែលត្រូវមុនគេ

        if (!$student) {
            return response()->json(['message' => 'រកមិនឃើញសិស្សដែលមានព័ត៌មាននេះទេ'], 404);
        }

        return response()->json($student, 200);
    }


    public function addStudentToClass(Request $request, $class_id)
{
    // 1. ពិនិត្យទិន្នន័យ
    $request->validate([
        'student_id' => 'required|exists:students,id',
    ]);

    try {
        // 2. រកថ្នាក់រៀន
        $classRoom = \App\Models\ClassRoom::findOrFail($class_id);
        
        // 3. ប្រើ syncWithoutDetaching ដើម្បីបញ្ចូលសិស្ស
        // ពិនិត្យឈ្មោះតារាង Pivot និង Column ក្នុង Database របស់អ្នក
        $classRoom->student()->syncWithoutDetaching([
            $request->student_id => [
                'status' => 1,
                'year_id' => $classRoom->year_id ?? 1, // បញ្ជាក់៖ បើ year_id ក្នុង Database មិនអនុញ្ញាត NULL ត្រូវតែមានតម្លៃ
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        return response()->json(['message' => 'បញ្ចូលសិស្សជោគជ័យ!'], 200);

    } catch (\Exception $e) {
        // បើ Error វានឹងបង្ហាញសារនេះមកកាន់ Vue ជំនួសឱ្យ Error 500 ទូទៅ
        return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
    }
}

   public function getStudentsByClass($class_id)
{
    try {
        // កូដសាកល្បង៖ កាត់បន្ថយអ្វីៗទាំងអស់ឱ្យសល់តែប៉ុណ្ណេះ
        $students = \App\Models\Student::whereHas('classroom', function($q) use ($class_id) {
            $q->where('class_rooms.id', $class_id);
        })->get();

        return response()->json($students);

    } catch (\Exception $e) {
        // នេះជាកន្លែងដែលនឹងប្រាប់អ្នកថាវាខុសត្រង់ណា
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString() // បង្ហាញទីតាំងដែលខុស
        ], 500);
    }
}
}