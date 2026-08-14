<?php

use STDW\Http\Response;
use InvalidArgumentException;

describe('Response Status', function () {
    test('default status is 200', function () {
        $response = new Response();
        expect($response->getStatus())->toBe(200);
    });

    test('can set status in constructor', function () {
        $response = new Response(404);
        expect($response->getStatus())->toBe(404);
    });

    test('can get status', function () {
        $response = new Response(201);
        expect($response->getStatus())->toBe(201);
    });

    test('can set status with withStatus', function () {
        $response = new Response();
        $response->withStatus(301);
        expect($response->getStatus())->toBe(301);
    });

    test('withStatus returns fluent interface', function () {
        $response = new Response();
        $result = $response->withStatus(204);
        expect($result)->toBe($response);
    });

    test('throws exception for status code below 100', function () {
        $response = new Response();
        expect(fn () => $response->withStatus(99))
            ->toThrow(InvalidArgumentException::class);
    });

    test('throws exception for status code above 599', function () {
        $response = new Response();
        expect(fn () => $response->withStatus(600))
            ->toThrow(InvalidArgumentException::class);
    });

    test('accepts status code 100', function () {
        $response = new Response();
        $response->withStatus(100);
        expect($response->getStatus())->toBe(100);
    });

    test('accepts status code 599', function () {
        $response = new Response();
        $response->withStatus(599);
        expect($response->getStatus())->toBe(599);
    });

    test('can chain multiple status changes', function () {
        $response = new Response();
        $response->withStatus(201)->withStatus(202)->withStatus(203);
        expect($response->getStatus())->toBe(203);
    });

    test('common HTTP status codes work correctly', function () {
        $codes = [200, 201, 204, 301, 302, 304, 400, 401, 403, 404, 500, 503];
        
        foreach ($codes as $code) {
            $response = new Response();
            $response->withStatus($code);
            expect($response->getStatus())->toBe($code);
        }
    });
});
