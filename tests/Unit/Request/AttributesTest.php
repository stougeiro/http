<?php // AttributesTest

use STDW\Http\Request;

it('stores and retrieves attributes', function () {
    $request = new Request();

    $request->withAttribute('user', 'Sidney');
    $request->withAttribute('role', 'admin');

    expect($request->getAttribute('user'))->toBe('Sidney');
    expect($request->getAttribute('role'))->toBe('admin');

    $attributes = $request->getAttributes();

    expect($attributes)->toHaveKeys(['user', 'role']);

    expect($attributes['user'])->toBe('Sidney');
    expect($attributes['role'])->toBe('admin');

    expect($request->getAttribute('missing'))->toBeNull();
});

it('removes attributes', function () {
    $request = new Request();

    $request->withAttribute('user', 'Sidney');
    $request->withAttribute('role', 'admin');
    $request->withAttribute('email', 'sidney@example.com');

    expect($request->getAttributes())->toHaveKeys(['user', 'role', 'email']);
    expect($request->getAttribute('user'))->toBe('Sidney');

    $request->withoutAttribute('user');

    expect($request->getAttributes())->toHaveKeys(['role', 'email']);
    expect($request->getAttribute('user'))->toBeNull();
    expect($request->getAttribute('role'))->toBe('admin');
    expect($request->getAttribute('email'))->toBe('sidney@example.com');
});

it('returns request instance from withoutAttribute for method chaining', function () {
    $request = new Request();

    $request->withAttribute('temp', 'value');

    $result = $request->withoutAttribute('temp');

    expect($result)->toBe($request);
    expect($result->getAttribute('temp'))->toBe(null);
});

