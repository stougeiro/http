<?php

use STDW\Http\Response;

describe('Response Headers', function () {
    test('default headers are empty', function () {
        $response = new Response();
        expect($response->getHeaders())->toBe([]);
    });

    test('can set headers in constructor', function () {
        $headers = ['Content-Type' => 'application/json', 'X-Custom' => 'value'];
        $response = new Response(200, $headers);
        expect($response->getHeaders())->toHaveKeys(['content-type', 'x-custom']);
    });

    test('can get single header by name', function () {
        $response = new Response(200, ['Content-Type' => 'text/html']);
        expect($response->getHeader('Content-Type'))->toBe('text/html');
    });

    test('get header returns null for non-existent header', function () {
        $response = new Response();
        expect($response->getHeader('Non-Existent'))->toBeNull();
    });

    test('can add header with withHeader', function () {
        $response = new Response();
        $response->withHeader('X-Custom', 'custom-value');
        expect($response->getHeader('X-Custom'))->toBe('custom-value');
    });

    test('withHeader returns fluent interface', function () {
        $response = new Response();
        $result = $response->withHeader('X-Test', 'value');
        expect($result)->toBe($response);
    });

    test('can check if header exists', function () {
        $response = new Response();
        expect($response->hasHeader('Content-Type'))->toBeFalse();
        
        $response->withHeader('Content-Type', 'text/html');
        expect($response->hasHeader('Content-Type'))->toBeTrue();
    });

    test('can remove header with withoutHeader', function () {
        $response = new Response(200, ['X-Custom' => 'value']);
        expect($response->hasHeader('X-Custom'))->toBeTrue();
        
        $response->withoutHeader('X-Custom');
        expect($response->hasHeader('X-Custom'))->toBeFalse();
    });

    test('withoutHeader returns fluent interface', function () {
        $response = new Response(200, ['X-Test' => 'value']);
        $result = $response->withoutHeader('X-Test');
        expect($result)->toBe($response);
    });

    test('can override existing header', function () {
        $response = new Response(200, ['Content-Type' => 'text/html']);
        expect($response->getHeader('Content-Type'))->toBe('text/html');
        
        $response->withHeader('Content-Type', 'application/json');
        expect($response->getHeader('Content-Type'))->toBe('application/json');
    });

    test('can chain multiple header operations', function () {
        $response = new Response();
        $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-Custom', 'value')
            ->withHeader('X-Another', 'another');
        
        expect($response->getHeaders())->toHaveCount(3);
    });

    test('header names are case-insensitive (normalized)', function () {
        $response = new Response();
        $response->withHeader('Content-Type', 'text/html');
        
        // Different case variations should access the same header
        expect($response->hasHeader('content-type'))->toBeTrue();
        expect($response->hasHeader('CONTENT-TYPE'))->toBeTrue();
    });

    test('removing header with different case works', function () {
        $response = new Response(200, ['Content-Type' => 'text/html']);
        $response->withoutHeader('content-type');
        
        expect($response->hasHeader('Content-Type'))->toBeFalse();
    });

    test('can add multiple headers and retrieve all', function () {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Custom-1' => 'value1',
            'X-Custom-2' => 'value2',
        ];
        
        $response = new Response();
        foreach ($headers as $name => $value) {
            $response->withHeader($name, $value);
        }
        
        expect($response->getHeaders())->toHaveCount(count($headers));
    });
});
