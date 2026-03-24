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

        // Defer route registration until after ALL service providers have
        // booted, ensuring our named routes overwrite Horizon's originals
        // (Laravel uses last-registered-wins for named routes).
        $this->app->booted(function () {
            $this->overrideRoutes();
        });
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
     * Laravel uses last-registered-wins for named routes. This method is
     * called from the app->booted() callback to guarantee execution AFTER
     * all service providers (including Horizon) have finished booting.
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
