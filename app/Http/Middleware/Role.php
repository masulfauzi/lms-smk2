<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Batasi halaman berdasarkan role: ->middleware('role:admin,guru') */
class Role
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $u = $request->user();
        if (!$u || !$u->aktif) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun tidak aktif. Hubungi admin sekolah.');
        }
        if ($roles && !$u->punya(...$roles)) {
            abort(403, 'Halaman ini bukan untuk ' . \App\Modules\Pengguna\Models\User::ROLES[$u->role] . '.');
        }
        // Catat waktu terakhir aktif (paling sering 1x per 5 menit) untuk pantauan keaktifan PJJ
        if (!$u->terakhir_aktif_at || $u->terakhir_aktif_at->lt(now()->subMinutes(5))) {
            $u->forceFill(['terakhir_aktif_at' => now()])->saveQuietly();
        }
        return $next($request);
    }
}
