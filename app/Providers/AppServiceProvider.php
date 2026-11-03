<?php

namespace App\Providers;

use App\Models\Ruang;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.paginasi');
        Gate::define('akses', fn (User $u, Ruang $ruang) => $u->bolehAkses($ruang));
    }
}
