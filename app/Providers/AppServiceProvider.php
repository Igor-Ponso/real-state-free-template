<?php

namespace App\Providers;

use App\Auth\CipherSweetEloquentUserProvider;
use App\Models\City;
use App\Models\ListingType;
use App\Models\Property;
use App\Models\PropertyType;
use App\Observers\FilterOptionObserver;
use App\Observers\PropertyObserver;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();

        Auth::provider('ciphersweet', function ($app, array $config) {
            return new CipherSweetEloquentUserProvider($app['hash'], $config['model']);
        });

        Event::listen(SocialiteWasCalled::class, AppleExtendSocialite::class.'@handle');

        Property::observe(PropertyObserver::class);
        City::observe(FilterOptionObserver::class);
        PropertyType::observe(FilterOptionObserver::class);
        ListingType::observe(FilterOptionObserver::class);

        Inertia::handleExceptionsUsing(function (ExceptionResponse $response) {
            if (in_array($response->statusCode(), [401, 403, 404, 429, 500, 503])) {
                return $response->render('Error', [
                    'status' => $response->statusCode(),
                ])->withSharedData();
            }
        });
    }

    /**
     * Define named rate limiters for different user roles and endpoints.
     *
     * Named limiters replace inline `throttle:X,Y` for semantic clarity
     * and role-aware limits. Admins bypass rate limits entirely.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('public', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));

        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('inquiry', fn (Request $request) => $request->user()?->hasRole('admin')
            ? Limit::none()
            : Limit::perHour(10)->by($request->ip()),
        );

        RateLimiter::for('admin', fn (Request $request) => $request->user()?->hasRole('admin')
            ? Limit::perMinute(300)->by($request->user()->id)
            : Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()),
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Strict mode: prevents lazy loading, silently discarding attributes, and accessing missing attributes in dev
        Model::shouldBeStrict(! app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // Log queries exceeding 500ms in non-production environments.
        // Catches N+1 regressions and missing indexes during development.
        if (! app()->isProduction()) {
            DB::whenQueryingForLongerThan(500, function ($connection, $event) {
                logger()->warning('Slow query detected', [
                    'sql' => $event->toRawSql(),
                    'time' => $event->time.'ms',
                    'connection' => $connection->getName(),
                ]);
            });
        }

        Password::defaults(fn (): Password => Password::min(12)
            ->mixedCase()
            ->letters()
            ->numbers()
            ->symbols()
            ->uncompromised(),
        );
    }
}
