<?php // MethodTest

use STDW\Http\Request;

it('detects HTTP method correctly', function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $request = new Request();

    expect($request->getMethod())->toBe('post');
});

it('normalizes method to uppercase', function () {
    $_SERVER['REQUEST_METHOD'] = 'get';

    $request = new Request();

    expect($request->getMethod())->toBe('get');
});

it('defaults to get when REQUEST_METHOD is absent', function () {
    unset($_SERVER['REQUEST_METHOD']);

    $request = new Request();

    expect($request->getMethod())->toBe('get');
});

it('defaults to get when REQUEST_METHOD is empty', function () {
    $_SERVER['REQUEST_METHOD'] = '';

    $request = new Request();

    expect($request->getMethod())->toBe('get');
});

it('defaults to get when REQUEST_METHOD is non-string', function () {
    $_SERVER['REQUEST_METHOD'] = 123;

    $request = new Request();

    expect($request->getMethod())->toBe('get');
});
