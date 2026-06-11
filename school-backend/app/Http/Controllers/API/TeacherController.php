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
        // ១. Validation គ្រប់ Fields ទាំងអស់ឱ្យបានត្រឹមត្រូវ
        $validated = $request->validate([
            'teacher_id_card' => 'required|string|max:50|unique:teachers,teacher_id_card',
            'name_kh'         => 'required|string|max:255',
            'name_en'         => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'gender'          => 'required|in:male,female',
            'specialty'       => 'nullable', 
            'dob'             => 'nullable|date',
            'phone'           => 'nullable|string',
            
            // ទីកន្លែងកំណើត (POB)
            'pob_province'    => 'nullable|string',
            'pob_district'    => 'nullable|string',
            'pob_commune'     => 'nullable|string',
            'pob_village'     => 'nullable|string',

            // អាសយដ្ឋានបច្ចុប្បន្ន (Current Address)
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

                // ២. បង្កើតគណនីអ្នកប្រើប្រាស់ (User) ភ្ជាប់ជាមួយសិទ្ធិ teacher
                $user = User::create([
                    'name'     => $validated['name_en'], 
                    'email'    => $validated['email'],
                    'password' => Hash::make('password123'), // គណនីបង្កើតដំបូងប្រើ Default Password
                    'role'     => 'teacher',
                    'status'   => $status,
                ]);

                // ៣. រៀបចំទុកដាក់ឯកសាររូបថតប្រវត្តិរូប
                $photoPath = null;
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('teachers', 'public');
                }

                // ៤. បំប្លែងទិន្នន័យ specialty ទៅជា JSON string (ប្រសិនបើផ្ញើមកជា Array)
                $specialty = $request->specialty;
                if (is_array($specialty)) {
                    $specialty = json_encode($specialty);
                }

                // ៥. បង្កើតប្រវត្តិរូបគ្រូបង្រៀន (Teacher) ចូលក្នុង Table
                $teacher = Teacher::create([
                    'teacher_id_card' => $validated['teacher_id_card'],
                    'user_id'         => $user->id,
                    'name_kh'         => $validated['name_kh'],
                    'name_en'         => $validated['name_en'],
                    'specialty'       => $specialty, 
                    'gender'          => $validated['gender'],
                    'dob'             => $validated['dob'],
                    'phone'           => $validated['phone'],
                    'email'           => $validated['email'],
                    
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
                    'message' => 'រក្សាទុកព័ត៌មានគ្រូបង្រៀនជោគជ័យ',
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
        
        // ១. Validation ឱ្យគ្រប់គ្រាន់ ដើម្បីការពារការបាត់បង់ទិន្នន័យពេលលោតចូល $validated
        $validated = $request->validate([
            'teacher_id_card' => 'required|string|unique:teachers,teacher_id_card,' . $id,
            'name_kh'         => 'required|string',
            'name_en'         => 'required|string',
            'email'           => 'required|email|unique:users,email,' . $teacher->user_id,
            'gender'          => 'required|in:male,female',
            'dob'             => 'nullable|date',
            'phone'           => 'nullable|string',
            'specialty'       => 'nullable', 
            'status'          => 'nullable', 
            
            // ទីកន្លែងកំណើត (POB)
            'pob_province'    => 'nullable|string',
            'pob_district'    => 'nullable|string',
            'pob_commune'     => 'nullable|string',
            'pob_village'     => 'nullable|string',
            
            // អាសយដ្ឋានបច្ចុប្បន្ន (Current Address)
            'province'        => 'nullable|string',
            'district'        => 'nullable|string',
            'commune'         => 'nullable|string',
            'village'         => 'nullable|string',
            
            'hire_date'       => 'nullable|date',
            'photo'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request, $teacher, $validated) {
                
                // បំប្លែងតម្លៃ status មកជា Integer (ទោះបីជាមកពី Frontend ជា String "0" ឬ "1" ក៏ដោយ)
                $status = $request->has('status') ? (int)$request->status : 1;

                // ២. ធ្វើបច្ចុប្បន្នភាព (Update) ទៅកាន់ Table Users មុន
                if ($teacher->user) {
                    $teacher->user->update([
                        'name'   => $validated['name_en'],
                        'email'  => $validated['email'],
                        'status' => $status,
                    ]);
                }

                // ៣. ចម្លងទិន្នន័យដែលបាន Validate រួចទៅក្នុង Array ថ្មីមួយសម្រាប់ចាត់ចែង
                $data = $validated;
                $data['status'] = $status;
                
                // គ្រប់គ្រងរឿង Specialty (ការពារការទម្លាក់ JSON ជាន់គ្នាពីរដង)
                if (isset($data['specialty'])) {
                    $decoded = json_decode($data['specialty'], true);
                    if (is_array($decoded)) {
                        // ប្រសិនបើវាជាទម្រង់ JSON String ស្រាប់ (មកពី JSON.stringify) គឺរក្សាទុកដដែល
                        $data['specialty'] = $data['specialty']; 
                    } else if (is_array($data['specialty'])) {
                        // បើមកជាទម្រង់ Array ធម្មតា ត្រូវបំប្លែងទៅជា JSON String
                        $data['specialty'] = json_encode($data['specialty']);
                    }
                }

                // ៤. គ្រប់គ្រងការផ្លាស់ប្តូររូបថត (លុបរូបចាស់ចេញពី Storage ប្រសិនបើមានការ Upload រូបថ្មី)
                if ($request->hasFile('photo')) {
                    if ($teacher->photo) {
                        Storage::disk('public')->delete($teacher->photo);
                    }
                    $data['photo'] = $request->file('photo')->store('teachers', 'public');
                } else {
                    // ប្រសិនបើមិនមានរូបថតថ្មីផ្ញើមកទេ ដក field នេះចេញដើម្បីរក្សារូបចាស់ឱ្យនៅដដែល
                    unset($data['photo']);
                }

                // ៥. ធ្វើបច្ចុប្បន្នភាព (Update) ទិន្នន័យគ្រូបង្រៀនទាំងអស់ចូលក្នុង Table Teachers
                $teacher->update($data);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'កែប្រែព័ត៌មានគ្រូបង្រៀនជោគជ័យ',
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