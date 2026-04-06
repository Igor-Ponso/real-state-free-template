<?php

use App\Services\GeocodingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('services.nominatim.enabled', true);
    Config::set('services.nominatim.base_url', 'https://nominatim.openstreetmap.org');
    Config::set('services.nominatim.user_agent', 'SovereignEstates/1.0 (test@example.com)');
    Config::set('services.nominatim.cache_ttl', 60);
    Config::set('services.nominatim.timeout', 5);

    Cache::flush();
});

test('geocode returns lat/lng for a successful response', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            [
                'lat' => '49.2827291',
                'lon' => '-123.1207375',
                'display_name' => '123 Main St, Vancouver, BC, Canada',
            ],
        ], 200),
    ]);

    $service = new GeocodingService;

    $result = $service->geocode('123 Main St', 'Vancouver', 'BC');

    expect($result)->toBe([
        'lat' => 49.2827291,
        'lng' => -123.1207375,
    ]);
});

test('geocode returns null when API returns empty array', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([], 200),
    ]);

    $service = new GeocodingService;

    expect($service->geocode('Nonexistent Street 999'))->toBeNull();
});

test('geocode returns null when API returns error status', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response(null, 500),
    ]);

    $service = new GeocodingService;

    expect($service->geocode('123 Main St'))->toBeNull();
});

test('geocode returns null when geocoding is disabled', function () {
    Config::set('services.nominatim.enabled', false);
    Http::fake();

    $service = new GeocodingService;

    expect($service->geocode('123 Main St'))->toBeNull();
    Http::assertNothingSent();
});

test('geocode returns null for empty address', function () {
    Http::fake();

    $service = new GeocodingService;

    expect($service->geocode(''))->toBeNull();
    Http::assertNothingSent();
});

test('geocode caches successful results', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '49.28', 'lon' => '-123.12'],
        ], 200),
    ]);

    $service = new GeocodingService;

    $service->geocode('123 Main St', 'Vancouver', 'BC');
    $service->geocode('123 Main St', 'Vancouver', 'BC');
    $service->geocode('123 Main St', 'Vancouver', 'BC');

    Http::assertSentCount(1);
});

test('geocode sends a meaningful User-Agent header', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '49.28', 'lon' => '-123.12'],
        ], 200),
    ]);

    $service = new GeocodingService;
    $service->geocode('123 Main St');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('User-Agent', 'SovereignEstates/1.0 (test@example.com)');
    });
});

test('geocode includes full address with city and state in query', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '49.28', 'lon' => '-123.12'],
        ], 200),
    ]);

    $service = new GeocodingService;
    $service->geocode('123 Main St', 'Vancouver', 'BC');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'q=')
            && str_contains(urldecode($request->url()), '123 Main St, Vancouver, BC, Canada');
    });
});

test('geocode handles malformed json response gracefully', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response('not json', 200),
    ]);

    $service = new GeocodingService;

    expect($service->geocode('123 Main St'))->toBeNull();
});

test('geocode handles connection failure', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::failedConnection(),
    ]);

    $service = new GeocodingService;

    expect($service->geocode('123 Main St'))->toBeNull();
});
