<?php

namespace App\Http\Middleware;

use App\Modules\Pengguna\Models\User;
use App\Modules\Sekolah\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Middleware;

/** Data yang tersedia di semua halaman Vue lewat usePage().props */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $u = $request->user();
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $u ? [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'role' => $u->role,
                    'role_label' => User::ROLES[$u->role] ?? $u->role,
                    'inisial' => $u->inisial(),
                    'kelas' => $u->kelas?->nama,
                ] : null,
            ],
            'sekolah' => fn () => [
                'nama' => setting('nama_sekolah'),
                'jenjang' => setting('jenjang'),
            ],
            'tahun_ajaran' => fn () => $u ? TahunAjaran::aktif()?->label() : null,
            'flash' => fn () => [
                'ok' => $request->session()->get('ok'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
