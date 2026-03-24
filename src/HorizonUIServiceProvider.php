<?php

declare(strict_types=1);

namespace APB\HorizonUI;

use APB\HorizonUI\Http\Middleware\SwapHorizonAssets;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HorizonUIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        $this->registerMiddleware();
        $this->overrideRoutes();
    }

    /**
     * Register the middleware that swaps Horizon's JS bundle with ours.
     *
     * Appends to the existing 'horizon' middleware group so that HTML
     * responses get our enhanced JS bundle instead of the original.
     * No Horizon views are overridden — only the inlined <script> block.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('horizon', SwapHorizonAssets::class);
    }

    /**
     * Register our enhanced controller routes, overriding Horizon's originals.
     *
     * Laravel uses last-registered-wins for named routes, so we register
     * our routes AFTER Horizon's boot — which is guaranteed because
     * Laravel auto-discovers this provider after laravel/horizon.
     */
    protected function overrideRoutes(): void
    {
        Route::group([
            'domain' => config('horizon.domain', null),
            'prefix' => config('horizon.path') . '/api',
            'middleware' => 'horizon',
        ], function () {
            Route::get('/jobs/pending', [Http\Controllers\PendingJobsController::class, 'index'])
                ->name('horizon.pending-jobs.index');

            Route::get('/jobs/completed', [Http\Controllers\CompletedJobsController::class, 'index'])
                ->name('horizon.completed-jobs.index');

            Route::get('/jobs/failed', [Http\Controllers\FailedJobsController::class, 'index'])
                ->name('horizon.failed-jobs.index');
        });
    }
}
