<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\AuthResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * User Register
     */
    public function registerUser(UserRegisterRequest $request)
    {
        $data = $request->validated();

        if (User::where('email', $data['email'])->count() == 1) {
            return responseModel(400, "Invalid credentials", ["errors" => ['email' => ['Email already registred']]]);
        }

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $role = Role::where('name', 'customer')->first();
        if ($role) {
            $user->assignRole($role->name); // Menggunakan nama role sebagai string
        } else {
            return responseModel(400, 'Invalid credentials', ['error' => 'Role customer not found.']);
        }
        $user->save();
        $user['token'] = $user->createToken('auth_token')->plainTextToken;

        return responseModel(201, 'Invalid credentials', new AuthResource($user));
    }

    /**
     * User Login
     */
    public function loginUser(UserLoginRequest $request)
    {
        $data = $request->validated();

        if (!Auth::attempt($data)) {
            return responseModel(400, "Invalid credentials", ['errors' => [
                'email' => ['Invalid credentials'],
                'password' => ['Invalid credentials']
            ]]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user['token'] = $user->createToken('auth_token')->plainTextToken;

        return responseModel(200, 'Loggin successfully', new AuthResource($user));
    }

    /**
     * User Logout
     */
    public function logoutUser(Request $request)
    {
        $request->user()->tokens()->delete();
        return responseModel(200, 'Logout successfully', new AuthResource($request->user()));
    }


    /**
     * User Delete
     */
    public function deleteUser(Request $request)
    {
        $user = $request->user();
        $user->delete();

        return responseModel(200, 'User deleted successfully', new AuthResource($user));
    }
}
