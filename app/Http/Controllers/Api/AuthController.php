<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'firstName' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'confirmPassword' => 'required|same:password',
            'isTeacher' => 'required|boolean',
            'phone' => 'required|min:10|max:20'
        ]);

        if ($validator->fails()) {
            $success['status'] = false;
            $response = [
                'data' => $success,
                'message' => $validator->errors()
            ];
            return response()->json($response);
        }

        $encrypted_password = Hash::make($request->password);
        
        $user = User::create([
            'name' => $request->name,
            'first_name' => $request->firstName,
            'is_teacher' => $request->isTeacher,
            'phone_number' => $request->phone,
            'email' => $request->email,
            'password' => $encrypted_password
        ]);
        
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['name'] = $user->name;
        $success['status'] = true;

        $response = [
            'message' => "successfuly",
            'data' => $success
        ];

        return response()->json($response);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $success['status'] = false;
            $response = [
                'data' => $success,
                'message' => $validator->errors()
            ];
        
            return response()->json($response);
        }

        $credential = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (Auth::attempt($credential)) {
            $user = $request->user();
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->name;
            $success['status'] = true;
            $response = [
                'data' => $success,
                'message' => "successfuly",
            ];
            return response()->json($response);
        } else {
            $success['status'] = false;
            $response = [
                'data' => $success,
                'message' => "The provided credentials do not match our records."
            ];
            return response()->json($response);
        };
    }
}
