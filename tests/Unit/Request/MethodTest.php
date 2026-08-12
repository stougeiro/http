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
