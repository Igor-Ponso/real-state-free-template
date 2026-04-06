<?php

/*
|--------------------------------------------------------------------------
| Architecture Tests
|--------------------------------------------------------------------------
|
| These tests enforce architectural rules across the codebase, catching
| violations (debug statements, wrong namespaces, missing base classes)
| at test time rather than in code review or production.
|
*/

arch('no debugging statements')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'die'])
    ->each->not->toBeUsed();

arch('models extend Eloquent Model')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\User');

arch('user model extends Authenticatable')
    ->expect('App\Models\User')
    ->toExtend('Illuminate\Foundation\Auth\User');

arch('controllers have Controller suffix')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('non-base controllers extend the base Controller')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring('App\Http\Controllers\Controller');

arch('form requests have Request suffix and extend FormRequest')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request')
    ->toExtend('Illuminate\Foundation\Http\FormRequest');

arch('actions have Action suffix')
    ->expect('App\Actions')
    ->toHaveSuffix('Action')
    ->ignoring([
        'App\Actions\Fortify\CreateNewUser',
        'App\Actions\Fortify\ResetUserPassword',
    ]);

arch('services have Service suffix and are classes')
    ->expect('App\Services')
    ->toHaveSuffix('Service')
    ->toBeClasses();

arch('resources have Resource suffix and extend JsonResource')
    ->expect('App\Http\Resources')
    ->toHaveSuffix('Resource')
    ->toExtend('Illuminate\Http\Resources\Json\JsonResource');

arch('policies have Policy suffix')
    ->expect('App\Policies')
    ->toHaveSuffix('Policy');

arch('middleware are classes')
    ->expect('App\Http\Middleware')
    ->toBeClasses();

arch('observers have Observer suffix')
    ->expect('App\Observers')
    ->toHaveSuffix('Observer');

arch('rules implement ValidationRule contract')
    ->expect('App\Rules')
    ->toImplement('Illuminate\Contracts\Validation\ValidationRule');

arch('actions should not depend on request/auth/session globals')
    ->expect('App\Actions')
    ->not->toUse(['request', 'auth', 'session']);

arch('services should not depend on HTTP layer')
    ->expect('App\Services')
    ->not->toUse([
        'Illuminate\Http\Request',
        'Inertia\Inertia',
        'Inertia\Response',
    ]);

arch('preset: php')
    ->preset()
    ->php();
