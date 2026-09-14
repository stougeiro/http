<?php

use STDW\Http\Request;

it('gets uri correctly', function () {
    $_SERVER['REQUEST_URI'] = '/products/list?category=books';

    $req = new Request;
    $uri = $req->getUri();

    expect($uri->getPath())->toBe('/products/list');
    expect($uri->getQuery())->toBe(['category' => 'books']);
    expect((string) $uri)->toBe('/products/list?category=books');
});

it('defaults to / when REQUEST_URI is absent', function () {
    unset($_SERVER['REQUEST_URI']);

    $request = new Request();
    $uri = $request->getUri();

    expect($uri->getPath())->toBe('/');
    expect((string) $uri)->toBe('/');
});

it('defaults to / when REQUEST_URI is empty', function () {
    $_SERVER['REQUEST_URI'] = '';

    $request = new Request();
    $uri = $request->getUri();

    expect($uri->getPath())->toBe('/');
});
