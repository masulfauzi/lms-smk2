<?php

/*
 * Semua route aplikasi ada di masing-masing modul: app/Modules/<Nama>/routes.php.
 * File ini hanya memuatnya (di dalam grup middleware "web" bawaan Laravel).
 */
foreach (glob(app_path('Modules/*/routes.php')) as $routes) {
    require $routes;
}
