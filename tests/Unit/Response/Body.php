<?php

use STDW\Http\Response;

describe('Response Body', function () {
    test('default body is empty string', function () {
        $response = new Response();
        expect($response->getBody())->toBe('');
    });

    test('can set body in constructor', function () {
        $response = new Response(200, [], 'Hello World');
        expect($response->getBody())->toBe('Hello World');
    });

    test('can get body', function () {
        $response = new Response(200, [], 'Test Body');
        expect($response->getBody())->toBe('Test Body');
    });

    test('can set body with withBody', function () {
        $response = new Response();
        $response->withBody('New Body');
        expect($response->getBody())->toBe('New Body');
    });

    test('withBody returns fluent interface', function () {
        $response = new Response();
        $result = $response->withBody('content');
        expect($result)->toBe($response);
    });

    test('can set body to null', function () {
        $response = new Response(200, [], 'Initial Body');
        $response->withBody(null);
        expect($response->getBody())->toBeNull();
    });

    test('can replace body content', function () {
        $response = new Response(200, [], 'Original');
        $response->withBody('Replaced');
        expect($response->getBody())->toBe('Replaced');
    });

    test('can clear body by setting empty string', function () {
        $response = new Response(200, [], 'Content');
        $response->withBody('');
        expect($response->getBody())->toBe('');
    });

    test('body can contain special characters', function () {
        $body = 'Special chars: !@#$%^&*() "quotes" \'apostrophes\'';
        $response = new Response(200, [], $body);
        expect($response->getBody())->toBe($body);
    });

    test('body can contain JSON', function () {
        $json = '{"key": "value", "number": 123}';
        $response = new Response(200, [], $json);
        expect($response->getBody())->toBe($json);
    });

    test('body can contain HTML', function () {
        $html = '<html><body><h1>Title</h1></body></html>';
        $response = new Response(200, [], $html);
        expect($response->getBody())->toBe($html);
    });

    test('body can contain large content', function () {
        $largeContent = str_repeat('A', 10000);
        $response = new Response(200, [], $largeContent);
        expect($response->getBody())->toBe($largeContent);
    });

    test('can chain body operations', function () {
        $response = new Response();
        $response->withBody('First')->withBody('Second');
        expect($response->getBody())->toBe('Second');
    });

    test('body preserves whitespace', function () {
        $body = "Line 1\n\tLine 2\r\nLine 3";
        $response = new Response(200, [], $body);
        expect($response->getBody())->toBe($body);
    });
});
