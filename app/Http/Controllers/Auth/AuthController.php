<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Bunday telefon raqam bilan foydalanuvchi topilmadi.']);
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Parol noto‘g‘ri.']);
        }

        Auth::login($user);

        return redirect('/')->with('success', 'Siz muvaffaqiyatli tizimga kirdingiz.');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Siz muvaffaqiyatli tizimdan chiqdingiz.');
    }
}
