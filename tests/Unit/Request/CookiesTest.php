<?php // CookiesTest

use STDW\Http\Request;

it('parses cookies correctly', function () {
    $_SERVER['HTTP_COOKIE'] = 'session=abc123; theme=dark';

    $request = new Request();
    $cookies = $request->getCookies();

    expect($cookies)->toHaveKeys(['session', 'theme']);

    expect($cookies['session']['value'])->toBe('abc123');
    expect($cookies['theme']['value'])->toBe('dark');

    $cookie = $request->getCookie('session');
    expect($cookie['name'])->toBe('session');
    expect($cookie['value'])->toBe('abc123');

    $cookie = $request->getCookie('theme');
    expect($cookie['name'])->toBe('theme');
    expect($cookie['value'])->toBe('dark');
});

it('secures cookie values', function () {
    $_SERVER['HTTP_COOKIE'] = 'xss=<script>alert(1)</script>';

    $request = new Request();
    $cookies = $request->getCookies();

    expect($cookies)->toHaveKey('xss');
    expect($cookies['xss']['value'])->toBe('&lt;script&gt;alert(1)&lt;/script&gt;');

    $cookie = $request->getCookie('xss');
    expect($cookie['name'])->toBe('xss');
    expect($cookie['value'])->toBe('&lt;script&gt;alert(1)&lt;/script&gt;');
});

it('skips cookie parts without = sign', function () {
    $_SERVER['HTTP_COOKIE'] = 'valid=yes; noequals; other=ok';

    $request = new Request();
    $cookies = $request->getCookies();

    expect($cookies)->toHaveKeys(['valid', 'other']);
    expect($cookies)->not->toHaveKey('noequals');
    expect($cookies['valid']['value'])->toBe('yes');
    expect($cookies['other']['value'])->toBe('ok');
});

it('returns empty array when no cookies', function () {
    unset($_SERVER['HTTP_COOKIE']);

    $request = new Request();

    expect($request->getCookies())->toBe([]);
});

it('returns null for non-existent cookie', function () {
    $_SERVER['HTTP_COOKIE'] = 'exists=1';

    $request = new Request();

    expect($request->getCookie('missing'))->toBeNull();
});
