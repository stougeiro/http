<?php

use STDW\Http\Response;
use Tests\Traits\TestableResponse;

class TestableResponseImpl extends Response
{
    use TestableResponse;
}

describe('Response Send', function () {
    test('send invokes sendStatus with correct status', function () {
        $response = new TestableResponseImpl(201);
        $response->send();

        expect($response->sentStatus)->toBe([201]);
    });

    test('send invokes sendHeaders', function () {
        $response = new TestableResponseImpl(200, ['X-Foo' => 'bar']);
        $response->send();

        expect($response->sentHeaders)->toHaveKey('x-foo');
        expect($response->sentHeaders['x-foo'])->toBe('bar');
    });

    test('send invokes sendCookies', function () {
        $response = new TestableResponseImpl();
        $response->withCookie('session', 'abc');
        $response->send();

        expect($response->sentCookies)->toHaveKey('session');
    });

    test('send invokes sendBody with body content', function () {
        $response = new TestableResponseImpl(200, [], 'Hello');
        $response->send();

        expect($response->sentBody)->toBe(['Hello']);
    });

    test('send suppresses body for status 204', function () {
        $response = new TestableResponseImpl(204, [], 'should not appear');
        $response->send();

        expect($response->sentBody)->toBe([]);
    });

    test('send suppresses body for status 304', function () {
        $response = new TestableResponseImpl(304, [], 'should not appear');
        $response->send();

        expect($response->sentBody)->toBe([]);
    });

    test('send suppresses body for status 100', function () {
        $response = new TestableResponseImpl(100, [], 'should not appear');
        $response->send();

        expect($response->sentBody)->toBe([]);
    });

    test('send suppresses body for status 199', function () {
        $response = new TestableResponseImpl(199, [], 'should not appear');
        $response->send();

        expect($response->sentBody)->toBe([]);
    });

    test('send suppresses null body', function () {
        $response = new TestableResponseImpl();
        $response->withBody(null);
        $response->send();

        expect($response->sentBody)->toBe([]);
    });

    test('send suppresses empty string body', function () {
        $response = new TestableResponseImpl();
        $response->withBody('');
        $response->send();

        expect($response->sentBody)->toBe([]);
    });

    test('send outputs body for status 200 with content', function () {
        $response = new TestableResponseImpl(200, [], 'test body');
        $response->send();

        expect($response->sentBody)->toBe(['test body']);
    });

    test('send outputs body for status 500', function () {
        $response = new TestableResponseImpl(500, [], 'error');
        $response->send();

        expect($response->sentBody)->toBe(['error']);
    });

    test('send calls all helpers in order', function () {
        $response = new TestableResponseImpl(200, ['X-Foo' => 'bar'], 'body');
        $response->withCookie('c', 'v');
        $response->send();

        expect($response->sentStatus)->toHaveCount(1);
        expect($response->sentHeaders)->toHaveKey('x-foo');
        expect($response->sentCookies)->toHaveKey('c');
        expect($response->sentBody)->toBe(['body']);
    });
});
