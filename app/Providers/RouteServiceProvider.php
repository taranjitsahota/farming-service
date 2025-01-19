<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

        protected function map()
    {
        // $this->mapApiRoutes();
        // $this->mapWebRoutes();
        $this->mapAppRoutes();
        $this->mapWebsiteRoutes();
    }

    protected function mapAppRoutes()
    {
        // Load all routes from the app directory
        $appRoutesPath = base_path('routes/app');

        foreach (scandir($appRoutesPath) as $file) {
            if (is_file($appRoutesPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                Route::prefix('api/app')
                    ->middleware('api')
                    ->group($appRoutesPath . '/' . $file);
            }
        }
    }

    protected function mapWebsiteRoutes()
    {
        $websiteRoutesPath = base_path('routes/website');

        foreach (scandir($websiteRoutesPath) as $file) {
            if (is_file($websiteRoutesPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                Route::prefix('api/website') // Prefix for website-specific routes
                    ->middleware('api')
                    ->group($websiteRoutesPath . '/' . $file);
            }
        }
    }


    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->map();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
