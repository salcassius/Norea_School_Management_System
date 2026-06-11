<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class UserController extends Controller
{
    /**
     * ទាញយកបញ្ជីអ្នកប្រើប្រាស់ទាំងអស់ (GET /api/users)
     */
    public function index(Request $request)
    {
        try {
            // Eager Load 'teacher' និង 'student' relationship
            $query = User::with(['teacher', 'student']); 

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            // ទាញយកទិន្នន័យចុងក្រោយគេ
            $users = $query->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'មានបញ្ហាបច្ចេកទេស៖ ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * បង្កើតអ្នកប្រើប្រាស់ថ្មី និងបង្កើត Profile (Teacher/Student) ស្វ័យប្រវត្តិ
     */
    public function store(Request $request)
    {
        // ប្រើ DB Transaction ដើម្បីធានាថា បើបង្កើត Profile បរាជ័យ User ក៏មិនត្រូវបង្កើតដែរ
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'role'     => 'required|in:admin,teacher,student',
                'status'   => 'required',
            ]);

            // ១. បង្កើត User Account
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => $validated['password'], // សង្ឃឹមថាអ្នកមាន Mutator hash ក្នុង Model រួចហើយ
                'role'     => $validated['role'],
                'status'   => $validated['status'],
            ]);

            // ២. បង្កើត Record ក្នុង Table តាម Role (ដើម្បីឱ្យ Relationship Work)
            if ($user->role === 'teacher') {
                Teacher::create([
                    'user_id' => $user->id,
                    'name_en' => $user->name,
                    'email'   => $user->email,
                    // ថែម field ចាំបាច់ផ្សេងៗទៀតនៅទីនេះ...
                ]);
            } elseif ($user->role === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'name_en' => $user->name,
                    'email'   => $user->email,
                    // ថែម field ចាំបាច់ផ្សេងៗទៀតនៅទីនេះ...
                ]);
            }

            DB::commit();

            // Load relationship មុននឹង return ទៅ frontend
            return response()->json([
                'success' => true,
                'data'    => $user->load(['teacher', 'student'])
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'ការបង្កើតបានបរាជ័យ៖ ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * កែប្រែព័ត៌មាន (PUT /api/users/{id})
     */
    public function update(Request $request, User $user)
{
    try {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'     => 'required|in:admin,teacher,student',
            'status'   => 'required',
            'password' => 'nullable|string|min:8', 
        ]);

        $updateData = collect($validated)->except('password')->toArray();

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data'    => $user->load(['teacher', 'student'])
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            // បង្ហាញសារកំហុសឱ្យបានច្បាស់លាស់
            'message' => 'ការកែប្រែបានបរាជ័យ៖ ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * លុបអ្នកប្រើប្រាស់ (DELETE /api/users/{id})
     */
    public function destroy(User $user)
    {
        try {
            // ដោយសារយើងប្រើ onDelete('cascade') ក្នុង Migration វានឹងលុប Profile គ្រូ/សិស្សដោយស្វ័យប្រវត្តិ
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ការលុបបានបរាជ័យ'
            ], 500);
        }
    }

    /**
     * ទាញយកស្ថិតិសម្រាប់ Dashboard
     */
    public function getStats()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total'    => User::count(),
                    'teachers' => User::where('role', 'teacher')->count(),
                    'students' => User::where('role', 'student')->count(),
                    'active'   => User::where('status', 1)->orWhere('status', true)->count(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}