<?php // CookiesTest

use STDW\Http\Request;

it('parses cookies correctly', function () {
    $_SERVER['HTTP_COOKIE'] = 'session=abc123; theme=dark';

    $request = new Request();
    $cookies = $request->getCookies();

    expect($cookies['session']['value'])->toBe('abc123');
    expect($cookies['theme']['value'])->toBe('dark');

    $cookie = $request->getCookie('session');
    expect($cookie['value'])->toBe('abc123');

    $cookie = $request->getCookie('theme');
    expect($cookie['value'])->toBe('dark');
});

it('secures cookie values', function () {
    $_SERVER['HTTP_COOKIE'] = 'xss=<script>alert(1)</script>';

    $request = new Request();
    $cookies = $request->getCookies();

    expect($cookies['xss']['value'])->toBe('&lt;script&gt;alert(1)&lt;/script&gt;');

    $cookie = $request->getCookie('xss');
    expect($cookie['value'])->toBe('&lt;script&gt;alert(1)&lt;/script&gt;');
});
