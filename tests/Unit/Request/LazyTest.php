<?php // LazyTest

use STDW\Http\Request;

it('caches params lazily', function () {
    $_GET = ['name' => 'Sidney'];

    $request = new Request();

    $first = $request->getParams();
    $_GET['name'] = 'Changed';

    $second = $request->getParams();

    expect($first)->toBe($second);
});

it('caches method lazily', function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $request = new Request();

    $first = $request->getMethod();
    $_SERVER['REQUEST_METHOD'] = 'GET';

    $second = $request->getMethod();

    expect($first)->toBe($second);
    expect($first)->toBe('post');
});

it('caches uri lazily', function () {
    $_SERVER['REQUEST_URI'] = '/first';

    $request = new Request();

    $first = $request->getUri();
    $_SERVER['REQUEST_URI'] = '/second';

    $second = $request->getUri();

    expect($first)->toBe($second);
    expect((string) $first)->toBe('/first');
});

it('caches headers lazily', function () {
    $_SERVER['HTTP_X_TOKEN'] = 'original';

    $request = new Request();

    $first = $request->getHeaders();
    $_SERVER['HTTP_X_TOKEN'] = 'changed';

    $second = $request->getHeaders();

    expect($first)->toBe($second);
    expect($first['x-token'])->toBe('original');
});

it('caches cookies lazily', function () {
    $_SERVER['HTTP_COOKIE'] = 'session=abc';

    $request = new Request();

    $first = $request->getCookies();
    $_SERVER['HTTP_COOKIE'] = 'session=xyz';

    $second = $request->getCookies();

    expect($first)->toBe($second);
    expect($first['session']['value'])->toBe('abc');
});

it('caches body lazily', function () {
    $_POST = ['field' => 'original'];

    $request = new Request();

    $first = $request->getBody();
    $_POST['field'] = 'changed';

    $second = $request->getBody();

    expect($first)->toBe($second);
    expect($first['field'])->toBe('original');
});
