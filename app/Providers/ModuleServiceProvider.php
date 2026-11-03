<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Memuat modul di app/Modules/<Nama>/.
 * View tiap modul didaftarkan dengan namespace huruf kecil: view('kuis::form').
 * Route modul (routes.php) dimuat dari routes/web.php agar tetap di grup middleware "web".
 */
class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (glob(app_path('Modules/*'), GLOB_ONLYDIR) as $dir) {
            if (is_dir("$dir/Views")) {
                $this->loadViewsFrom("$dir/Views", strtolower(basename($dir)));
            }
        }
    }
}
