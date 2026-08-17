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
