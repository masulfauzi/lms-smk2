<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\Role::class]);
        $middleware->web(append: [\App\Http\Middleware\HandleInertiaRequests::class]);
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Request Inertia mendapat halaman Error.vue; request biasa tetap memakai resources/views/errors/*.blade.php
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->header('X-Inertia')) {
                return $response;
            }
            if ($response->getStatusCode() === 419) {
                return back()->with('error', 'Sesi berakhir, silakan ulangi.');
            }
            if (in_array($response->getStatusCode(), [403, 404, 500, 503], true) && ! (config('app.debug') && $response->getStatusCode() === 500)) {
                $pesan = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface ? $e->getMessage() : '';
                return \Inertia\Inertia::render('Error', ['status' => $response->getStatusCode(), 'pesan' => $pesan])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }
            return $response;
        });
    })->create();
