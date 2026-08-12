<?php // HeadersTest

use STDW\Http\Request;

it('parses headers correctly', function () {
    $_SERVER['HTTP_X_TOKEN'] = 'abc123';
    $_SERVER['CONTENT_TYPE'] = 'application/json';

    $request = new Request();
    $headers = $request->getHeaders();

    expect($headers['x_token'])->toBe('abc123');
    expect($headers['content_type'])->toBe('application/json');

    expect($request->getHeader('X_TOKEN'))->toBe('abc123');
    expect($request->getHeader('CONTENT_TYPE'))->toBe('application/json');
});

it('returns null for missing header', function () {
    $_SERVER = [];

    $request = new Request();

    expect($request->getHeader('X_MISSING'))->toBeNull();
});
