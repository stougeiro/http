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

it('skips non-string values in SERVER', function () {
    $_SERVER['HTTP_X_VALID'] = 'yes';
    $_SERVER['HTTP_X_INT'] = 123;
    $_SERVER['HTTP_X_ARRAY'] = ['a', 'b'];

    $request = new Request();
    $headers = $request->getHeaders();

    expect($headers)->toHaveKey('x-valid');
    expect($headers)->not->toHaveKey('x-int');
    expect($headers)->not->toHaveKey('x-array');
});

it('handles CONTENT_MD5 header', function () {
    $_SERVER['CONTENT_MD5'] = 'abc123hash';

    $request = new Request();
    $headers = $request->getHeaders();

    expect($headers)->toHaveKey('content-md5');
    expect($headers['content-md5'])->toBe('abc123hash');
});
