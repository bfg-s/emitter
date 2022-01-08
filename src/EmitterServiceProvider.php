<?php

namespace Bfg\Emitter;

use Illuminate\Support\ServiceProvider;

class EmitterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        \Route::aliasMiddleware('emitter-message', MessageMiddleware::class);

        \Route::macro('emitter', function (string $guard = 'web') {
            \Route::post('/emitter/message/{name}', MessageController::class)
                ->middleware(['api', "emitter-message:{$guard}"])
                ->name('puller.message');
            \Route::get('/emitter/verify', [MessageController::class, 'verify'])
                ->middleware(['api', "emitter-message:{$guard}"])
                ->name('puller.keep-verify');
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \Blade::directive('emitterScripts', [BladeDirective::class, 'directiveScripts']);
        \Blade::directive('emitterInline', [BladeDirective::class, 'directiveInline']);

        $this->publishes([
            __DIR__ . '/../assets' => public_path('vendor/emitter')
        ], 'emitter-assets');

        $this->publishes([
            __DIR__ . '/../assets' => public_path('vendor/emitter')
        ], 'laravel-assets');
    }
}
