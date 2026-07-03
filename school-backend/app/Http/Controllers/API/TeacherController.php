<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Teacher;
use App\Models\ClassRoom; // 💡 ប្រសិនបើ Model ថ្នាក់រៀនរបស់ប្អូនឈ្មោះ Class សូមប្តូរទៅជា Class វិញ
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    /**
     * 💡 មុខងារបន្ថែមថ្មី៖ ទាញយកបញ្ជីថ្នាក់រៀនដែលគ្រូកំពុង Login មានសិទ្ធិគ្រប់គ្រង
     * URL: GET /api/teacher/classes
     */
    public function getMyClasses(Request $request)
    {
        try {
            $user = $request->user();

            // ករណីទី ១៖ ប្រសិនបើអ្នកដែលកំពុងប្រើប្រាស់ជា ADMIN ឱ្យបង្ហាញថ្នាក់រៀនទាំងអស់តែម្តង
            if ($user->role === 'admin') {
                $classes = ClassRoom::latest()->get();
                return response()->json([
                    'success' => true,
                    'data' => $classes
                ]);
            }

            // ករណីទី ២៖ ប្រសិនបើជាគ្រូបង្រៀន (TEACHER)
            // ស្វែងរកទិន្នន័យគ្រូនៅក្នុង Table teachers ផ្អែកលើ user_id នៃគណនីដែលបាន Login
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return response()->json([
                    'success' => false,
                    'message' => 'រកមិនឃើញប្រវត្តិរូបគ្រូបង្រៀនសម្រាប់គណនីនេះឡើយ!'
                ], 404);
            }

            // ទាញយកថ្នាក់រៀនណាដែលជារបស់គ្រូម្នាក់នេះ (ផ្អែកលើ column teacher_id នៅក្នុង table classes)
            $classes = ClassRoom::where('teacher_id', $teacher->id)->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $classes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Laravel Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ទាញយកបញ្ជីឈ្មោះគ្រូបង្រៀនទាំងអស់ រួមជាមួយគណនី User
     */
    public function index() {
        return response()->json(Teacher::with('user')->latest()->get());
    }

    /**
     * បង្កើតព័ត៌មានគ្រូបង្រៀនថ្មី (Store)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id_card' => 'required|string|max:50|unique:teachers,teacher_id_card',
            'name_kh'         => 'required|string|max:255',
            'name_en'         => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email|unique:teachers,email', // ពិនិត្យកុំឱ្យជាន់គ្នាក្នុងតារាងទាំងពីរ
            'password'        => 'required|string|min:6',
            'gender'          => 'required|in:male,female',
            'specialty'       => 'nullable', 
            'dob'             => 'nullable|date',
            'phone'           => 'nullable|string',
            
            // ទីកន្លែងកំណើត (POB)
            'pob_province'    => 'nullable|string',
            'pob_district'    => 'nullable|string',
            'pob_commune'     => 'nullable|string',
            'pob_village'     => 'nullable|string',

            // អាសយដ្ឋានបច្ចុប្បន្ន
            'province'        => 'nullable|string',
            'district'        => 'nullable|string',
            'commune'         => 'nullable|string',
            'village'         => 'nullable|string',

            'hire_date'       => 'nullable|date',
            'status'          => 'nullable',
            'photo'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                
                $status = $request->has('status') ? (int)$request->status : 1;
                $hashedPassword = Hash::make($validated['password']); // Hash តែម្តងសម្រាប់ប្រើប្រាស់ទាំងពីរតារាង

                // ១. បង្កើតគណនីនៅក្នុងតារាង users
                $user = User::create([
                    'name'     => $validated['name_en'], 
                    'email'    => $validated['email'],
                    'password' => $hashedPassword,
                    'role'     => 'teacher',
                    'status'   => $status,
                ]);

                // ២. រៀបចំទុកដាក់ឯកសាររូបថត
                $photoPath = null;
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('teachers', 'public');
                }

                // ៣. គ្រប់គ្រង Specialty
                $specialty = $request->input('specialty', []);

                // ៤. បង្កើតប្រវត្តិរូបគ្រូបង្រៀននៅក្នុងតារាង teachers (រួមទាំង email និង password)
                $teacher = Teacher::create([
                    'user_id'         => $user->id,
                    'teacher_id_card' => $validated['teacher_id_card'],
                    'name_kh'         => $validated['name_kh'],
                    'name_en'         => $validated['name_en'],
                    'email'           => $validated['email'],     // ✅ រក្សាទុកក្នុងតារាង teachers
                    'password'        => $hashedPassword,         // ✅ រក្សាទុកក្នុងតារាង teachers (Hashed)
                    'specialty'       => $specialty, 
                    'gender'          => $validated['gender'],
                    'dob'             => $validated['dob'],
                    'phone'           => $validated['phone'],
                    
                    // ទីកន្លែងកំណើត
                    'pob_province'    => $validated['pob_province'],
                    'pob_district'    => $validated['pob_district'],
                    'pob_commune'     => $validated['pob_commune'],
                    'pob_village'     => $validated['pob_village'],

                    // អាសយដ្ឋានបច្ចុប្បន្ន
                    'province'        => $validated['province'],
                    'district'        => $validated['district'],
                    'commune'         => $validated['commune'],
                    'village'         => $validated['village'],

                    'hire_date'       => $validated['hire_date'],
                    'status'          => $status,
                    'photo'           => $photoPath,
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'រក្សាទុកព័ត៌មានគ្រូ និងបង្កើតគណនីស្វ័យប្រវត្តិកើតឡើងដោយជោគជ័យ',
                    'data'    => $teacher->load('user')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'បរាជ័យក្នុងការរក្សាទុក៖ ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * កែសម្រួលព័ត៌មានគ្រូបង្រៀន (Update)
     */
    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        
        $validated = $request->validate([
            'teacher_id_card' => 'required|string|unique:teachers,teacher_id_card,' . $id,
            'name_kh'         => 'required|string',
            'name_en'         => 'required|string',
            'email'           => 'required|email|unique:users,email,' . $teacher->user_id . '|unique:teachers,email,' . $id,
            'password'        => 'nullable|string|min:6', 
            'gender'          => 'required|in:male,female',
            'dob'             => 'nullable|date',
            'phone'           => 'nullable|string',
            'specialty'       => 'nullable', 
            'status'          => 'nullable', 
            
            // ទីកន្លែងកំណើត
            'pob_province'    => 'nullable|string',
            'pob_district'    => 'nullable|string',
            'pob_commune'     => 'nullable|string',
            'pob_village'     => 'nullable|string',
            
            // អាសយដ្ឋានបច្ចុប្បន្ន
            'province'        => 'nullable|string',
            'district'        => 'nullable|string',
            'commune'         => 'nullable|string',
            'village'         => 'nullable|string',
            
            'hire_date'       => 'nullable|date',
            'photo'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request, $teacher, $validated) {
                
                $status = $request->has('status') ? (int)$request->status : 1;
                $data = $validated;
                $data['status'] = $status;

                // ១. ពិនិត្យមើលការផ្លាស់ប្តូរ Password
                if (!empty($validated['password'])) {
                    $hashedPassword = Hash::make($validated['password']);
                    $data['password'] = $hashedPassword; // សម្រាប់ Update ក្នុងតារាង teachers
                } else {
                    // ប្រសិនបើមិនបានវាយលេខសម្ងាត់ថ្មីទេ ដកវាចេញដើម្បីរក្សាលេខសម្ងាត់ចាស់ដដែល
                    unset($data['password']); 
                }

                // ២. ធ្វើបច្ចុប្បន្នភាពទៅកាន់តារាង users មុន
                if ($teacher->user) {
                    $userData = [
                        'name'   => $validated['name_en'],
                        'email'  => $validated['email'],
                        'status' => $status,
                    ];
                    if (isset($data['password'])) {
                        $userData['password'] = $data['password'];
                    }
                    $teacher->user->update($userData);
                }

                // ៣. គ្រប់គ្រងរឿង Specialty
                $data['specialty'] = $request->input('specialty', []);

                // ៤. គ្រប់គ្រងការផ្លាស់ប្តូររូបថត
                if ($request->hasFile('photo')) {
                    if ($teacher->photo) {
                        Storage::disk('public')->delete($teacher->photo);
                    }
                    $data['photo'] = $request->file('photo')->store('teachers', 'public');
                } else {
                    unset($data['photo']);
                }

                // ៥. ធ្វើបច្ចុប្បន្នភាពទិន្នន័យគ្រូបង្រៀនទៅកាន់តារាង teachers
                $teacher->update($data);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'កែប្រែព័ត៌មានគ្រូបង្រៀន និងគណនីជោគជ័យ',
                    'data'    => $teacher->load('user')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'កែប្រែព័ត៌មានបរាជ័យ៖ ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * លុបទិន្នន័យគ្រូបង្រៀន និងគណនី User (Destroy)
     */
    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        try {
            return DB::transaction(function () use ($teacher) {
                // ១. លុបរូបថតចេញពី Storage បើមាន
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }

                $user = User::find($teacher->user_id);
                
                // ២. លុបទិន្នន័យចេញពី Table Teachers
                $teacher->delete();
                
                // ៣. លុបគណនីចេញពី Table Users ជាមួយគ្នា
                if ($user) {
                    $user->delete();
                }

                return response()->json([
                    'status'  => 'success',
                    'message' => 'លុបទិន្នន័យគ្រូបង្រៀនជោគជ័យ'
                ]);
        });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'មិនអាចលុបទិន្នន័យបានឡើយ៖ ' . $e->getMessage()
            ], 500);
        }
    }
}