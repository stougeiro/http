<?php

use STDW\Http\Response;

describe('Response Cookies', function () {
    test('default cookies are empty', function () {
        $response = new Response();
        expect($response->getCookies())->toBe([]);
    });

    test('can add cookie with withCookie', function () {
        $response = new Response();
        $response->withCookie('session', 'abc123');
        
        expect($response->getCookies())->toHaveKey('session');
    });

    test('withCookie returns fluent interface', function () {
        $response = new Response();
        $result = $response->withCookie('name', 'value');
        expect($result)->toBe($response);
    });

    test('can get single cookie by name', function () {
        $response = new Response();
        $response->withCookie('user', 'john');
        
        $cookie = $response->getCookie('user');
        expect($cookie)->not->toBeNull();
        expect($cookie['name'])->toBe('user');
        expect($cookie['value'])->toBe('john');
    });

    test('get cookie returns null for non-existent cookie', function () {
        $response = new Response();
        expect($response->getCookie('non-existent'))->toBeNull();
    });

    test('can set cookie with options', function () {
        $response = new Response();
        $response->withCookie('auth', 'token123', [
            'expires' => 3600,
            'path' => '/admin',
            'domain' => 'example.com',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'strict',
        ]);
        
        $cookie = $response->getCookie('auth');
        expect($cookie['value'])->toBe('token123');
        expect($cookie['options']['expires'])->toBe(3600);
        expect($cookie['options']['path'])->toBe('/admin');
        expect($cookie['options']['domain'])->toBe('example.com');
        expect($cookie['options']['secure'])->toBeTrue();
        expect($cookie['options']['httponly'])->toBeTrue();
        expect($cookie['options']['samesite'])->toBe('strict');
    });

    test('can set cookie with partial options', function () {
        $response = new Response();
        $response->withCookie('test', 'value', ['path' => '/app']);
        
        $cookie = $response->getCookie('test');
        expect($cookie['options']['path'])->toBe('/app');
        // Check defaults for other options
        expect($cookie['options']['expires'])->toBe(0);
        expect($cookie['options']['domain'])->toBe('');
        expect($cookie['options']['secure'])->toBeFalse();
        expect($cookie['options']['httponly'])->toBeFalse();
    });

    test('cookie options have defaults', function () {
        $response = new Response();
        $response->withCookie('simple', 'value');
        
        $cookie = $response->getCookie('simple');
        expect($cookie['options']['expires'])->toBe(0);
        expect($cookie['options']['path'])->toBe('/');
        expect($cookie['options']['domain'])->toBe('');
        expect($cookie['options']['secure'])->toBeFalse();
        expect($cookie['options']['httponly'])->toBeFalse();
        expect($cookie['options']['samesite'])->toBe('strict');
    });

    test('can remove cookie with withoutCookie', function () {
        $response = new Response();
        $response->withCookie('temp', 'value');
        expect($response->getCookie('temp'))->not->toBeNull();
        
        $response->withoutCookie('temp');
        expect($response->getCookie('temp'))->toBeNull();
    });

    test('withoutCookie returns fluent interface', function () {
        $response = new Response();
        $response->withCookie('name', 'value');
        $result = $response->withoutCookie('name');
        expect($result)->toBe($response);
    });

    test('can override existing cookie', function () {
        $response = new Response();
        $response->withCookie('session', 'old_value');
        $response->withCookie('session', 'new_value');
        
        $cookie = $response->getCookie('session');
        expect($cookie['value'])->toBe('new_value');
    });

    test('can clear all cookies with clearCookies', function () {
        $response = new Response();
        $response->withCookie('cookie1', 'value1');
        $response->withCookie('cookie2', 'value2');
        $response->withCookie('cookie3', 'value3');
        
        expect($response->getCookies())->toHaveCount(3);
        
        $response->clearCookies();
        expect($response->getCookies())->toBe([]);
    });

    test('clearCookies returns fluent interface', function () {
        $response = new Response();
        $response->withCookie('test', 'value');
        $result = $response->clearCookies();
        expect($result)->toBe($response);
    });

    test('can chain cookie operations', function () {
        $response = new Response();
        $response
            ->withCookie('first', 'value1')
            ->withCookie('second', 'value2')
            ->withCookie('third', 'value3');
        
        expect($response->getCookies())->toHaveCount(3);
    });

    test('can add multiple cookies and retrieve all', function () {
        $response = new Response();
        $response->withCookie('user_id', '123');
        $response->withCookie('user_name', 'john');
        $response->withCookie('preferences', 'dark_mode');
        
        $cookies = $response->getCookies();
        expect($cookies)->toHaveCount(3);
        expect($cookies['user_id']['value'])->toBe('123');
        expect($cookies['user_name']['value'])->toBe('john');
        expect($cookies['preferences']['value'])->toBe('dark_mode');
    });

    test('samesite option accepts different cases', function () {
        $response = new Response();
        $response->withCookie('test', 'value', ['samesite' => 'Lax']);
        
        $cookie = $response->getCookie('test');
        expect($cookie['options']['samesite'])->toBe('Lax');
    });

    test('can set cookie with zero expires', function () {
        $response = new Response();
        $response->withCookie('session', 'token', ['expires' => 0]);
        
        $cookie = $response->getCookie('session');
        expect($cookie['options']['expires'])->toBe(0);
    });

    test('can set secure and httponly flags', function () {
        $response = new Response();
        $response->withCookie('secure_token', 'token123', [
            'secure' => true,
            'httponly' => true,
        ]);
        
        $cookie = $response->getCookie('secure_token');
        expect($cookie['options']['secure'])->toBeTrue();
        expect($cookie['options']['httponly'])->toBeTrue();
    });
});
