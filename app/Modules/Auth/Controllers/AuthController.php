<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function form()
    {
        return Inertia::render('Auth/Login', [
            'sekolah' => [
                'nama' => setting('nama_sekolah'),
                'npsn' => setting('npsn'),
                'jenjang' => setting('jenjang'),
                'alamat' => setting('alamat'),
            ],
            'demo' => config('app.env') !== 'production',
        ]);
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

    public function profil(Request $r)
    {
        $u = $r->user();
        return Inertia::render('Auth/Profil', [
            'akun' => [
                'name' => $u->name,
                'username' => $u->username,
                'role' => $u->role,
                'role_label' => \App\Modules\Pengguna\Models\User::ROLES[$u->role],
                'kelas' => $u->kelas?->nama,
                'email' => $u->email,
            ],
        ]);
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
