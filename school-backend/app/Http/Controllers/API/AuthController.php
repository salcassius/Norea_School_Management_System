<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * មុខងារ Login សម្រាប់ចេញ Token ឱ្យទៅ Frontend
     */
    public function login(Request $request)
    {
        // Validate input ជាមុនសិន ការពារ Error 500 នៅ Backend
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'អ៊ីមែល ឬលេខសម្ងាត់មិនត្រឹមត្រូវឡើយ'
            ], 401);
        }

        // លុប Token ចាស់ៗចោលមុននឹងចេញ Token ថ្មី (Optional - for single device login)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user_token' => $token,
            'user_data'  => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * មុខងារ Logout ដើម្បីលុប Token
     */
    public function logout(Request $request)
    {
        // ប្រើអក្សរតូច user()
        $user = $request->user();

        if ($user) {
            // លុប Token បច្ចុប្បន្នដែលកំពុងប្រើ
            $user->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 401);
    }
}
