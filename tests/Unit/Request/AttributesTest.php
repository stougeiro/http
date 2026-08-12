<?php // AttributesTest

use STDW\Http\Request;

it('stores and retrieves attributes', function () {
    $request = new Request();

    $request->withAttribute('user', 'Sidney');
    $request->withAttribute('role', 'admin');

    expect($request->getAttribute('user'))->toBe('Sidney');
    expect($request->getAttribute('role'))->toBe('admin');

    $attributes = $request->getAttributes();

    expect($attributes['user'])->toBe('Sidney');
    expect($attributes['role'])->toBe('admin');

    expect($request->getAttribute('missing'))->toBeNull();
});

