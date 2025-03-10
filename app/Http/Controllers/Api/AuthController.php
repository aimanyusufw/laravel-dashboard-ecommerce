<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * User Register
     */
    public function registerUser(UserRegisterRequest $request)
    {
        $data = $request->validated();

        if (User::where('email', $data['email'])->count() == 1) {
            throw new HttpResponseException(response([
                'errors' => [
                    'email' => [
                        'Email already registred'
                    ]
                ]
            ], 400));
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $role = Role::where('name', 'customer')->first();
        if ($role) {
            $user->assignRole($role->name); // Menggunakan nama role sebagai string
        } else {
            return response()->json([
                'error' => 'Role customer not found.'
            ], 400);
        }
        $user->save();
        $user['token'] = $user->createToken('auth_token')->plainTextToken;

        return response(['message' => 'Register user successfully', 'data' => new UserResource($user)], 201);
    }

    /**
     * User Login
     */
    public function loginUser(UserLoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            throw new HttpResponseException(response([
                'errors' => [
                    'email' => 'Invalid credentials',
                    'password' => 'Invalid credentials'
                ]
            ], 400));
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user['token'] = $user->createToken('auth_token')->plainTextToken;

        return response(['message' => 'Loggin successfully', 'data' => new UserResource($user)], 200);
    }

    /**
     * User Logout
     */
    public function logoutUser(Request $request)
    {
        $request->user()->tokens()->delete();
        return response(['message' => 'Logout successfully'], 200);
    }


    /**
     * User Delete
     */
    public function deleteUser(Request $request)
    {
        $user = $request->user();

        $user->delete();

        return response(['message' => 'User deleted successfully'], 200);
    }
}
