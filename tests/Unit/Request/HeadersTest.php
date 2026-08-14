<?php // HeadersTest

use STDW\Http\Request;

it('parses headers correctly', function () {
    $_SERVER['HTTP_X_TOKEN'] = 'abc123';
    $_SERVER['CONTENT_TYPE'] = 'application/json';

    $request = new Request();
    $headers = $request->getHeaders();

    expect($headers['x-token'])->toBe('abc123');
    expect($headers['content-type'])->toBe('application/json');

    expect($request->getHeader('X_TOKEN'))->toBe('abc123');
    expect($request->getHeader('CONTENT_TYPE'))->toBe('application/json');
});

it('returns null for missing header', function () {
    $_SERVER = [];

    $request = new Request();

    expect($request->getHeader('X_MISSING'))->toBeNull();
});

it('checks if header exists', function () {
    $_SERVER['HTTP_X_TOKEN'] = 'abc123';
    $_SERVER['CONTENT_TYPE'] = 'application/json';

    $request = new Request();

    expect($request->hasHeader('X_TOKEN'))->toBeTrue();
    expect($request->hasHeader('x-token'))->toBeTrue();
    expect($request->hasHeader('CONTENT_TYPE'))->toBeTrue();
});

it('returns false when header does not exist', function () {
    $_SERVER = [];

    $request = new Request();

    expect($request->hasHeader('X_MISSING'))->toBeFalse();
    expect($request->hasHeader('CONTENT_TYPE'))->toBeFalse();
});
