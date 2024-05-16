<?php

use Illuminate\Support\Facades\Route;
use Tests\TestClasses\TestModel;
use Spatie\RouteTesting\RouteTesting;
use Tests\TestClasses\TestUser;
use function Spatie\RouteTesting\routeTesting;

it('only checks for GET endpoints', function () {
    Route::get('/get-endpoint', fn () => '');
    Route::post('/post-endpoint', fn () => '');

    $class = routeTesting()->toReturnSuccessfulResponse();

    expect($class)->toBeInstanceOf(RouteTesting::class);

    expect($class->assertedRoutes)
        ->toHaveCount(1)
        ->toHaveKey('get-endpoint')
        ->not->toHaveKey('post-endpoint');
});

it('can bind a model to a route', function () {
    Route::get('{user}', fn () => '');

    $model = new TestModel();

    $class = routeTesting()
        ->bind('user', $model)
        ->toReturnSuccessfulResponse();

    expect($class)->toBeInstanceOf(RouteTesting::class);

    expect($class->assertedRoutes)
        ->toHaveCount(1);

    /** @var \Illuminate\Routing\Route $firstRoute */
    $firstRoute = $class->assertedRoutes['{user}'];

    // @todo is there a way to verify if the binding is set?
});

it('can exclude routes with unknown bindings', function () {
    Route::get('{user}', fn () => '');

    $class = routeTesting()->toReturnSuccessfulResponse();

    expect($class->assertedRoutes)
        ->toHaveCount(0);

    expect($class->ignoredRoutes)
        ->toHaveCount(1)
        ->toContain('{user}');
});

it('can exclude routes', function () {
    Route::get('/get-endpoint', fn () => '');
    Route::get('/excluded-endpoint', fn () => '');

    $class = routeTesting()
        ->excluding(['excluded-endpoint'])
        ->toReturnSuccessfulResponse();

    expect($class->assertedRoutes)
        ->toHaveCount(1)
        ->toHaveKey('get-endpoint');
});

it('can exclude multiple routes', function () {
    Route::get('/get-endpoint', fn () => '');
    Route::get('/excluded-endpoint', fn () => '');
    Route::get('/2-excluded-endpoint', fn () => '');

    $class = routeTesting()
        ->excluding(['excluded-endpoint', '2-excluded-endpoint'])
        ->toReturnSuccessfulResponse();

    expect($class->assertedRoutes)
        ->toHaveCount(1)
        ->toHaveKey('get-endpoint');
});

it('can exclude routes based on a wildcard', function () {
    Route::get('/get-endpoint', fn () => '');
    Route::get('/excluded-endpoint', fn () => '');
    Route::get('/excluded-endpoint-2', fn () => '');

    $class = routeTesting()
        ->excluding(['excluded-*'])
        ->toReturnSuccessfulResponse();

    expect($class->assertedRoutes)
        ->toHaveCount(1)
        ->toHaveKey('get-endpoint');
});

it('can act as a user for authenticated routes', function () {
    Route::middleware('auth')->get('/authenticated-endpoint', fn () => '');

    expect(fn () => routeTesting()->toReturnSuccessfulResponse())
        ->toThrow(\Illuminate\Http\Exceptions\HttpResponseException::class);

    test()->actingAs(new TestUser());
    routeTesting()->toReturnSuccessfulResponse();
});
