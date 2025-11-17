<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $adminUser = AdminUser::where('username', $request->username)->first();

        if ($adminUser && Hash::check($request->password, $adminUser->password_hash)) {
            Auth::login($adminUser);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['error' => 'Invalid username or password']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
