<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function form()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $data = $r->validate(['username' => 'required|string', 'password' => 'required|string']);
        if (!Auth::attempt(['username' => trim($data['username']), 'password' => $data['password'], 'aktif' => true], $r->boolean('ingat'))) {
            return back()->withInput($r->only('username'))->with('error', 'NIP/NISN atau password salah.');
        }
        $r->session()->regenerate();
        return redirect()->intended(route('home'));
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function profil()
    {
        return view('auth.profil');
    }

    public function gantiPassword(Request $r)
    {
        $r->validate([
            'password_lama' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'password_lama.current_password' => 'Password lama tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);
        $r->user()->update(['password' => $r->password]);
        return back()->with('ok', 'Password berhasil diganti.');
    }
}
