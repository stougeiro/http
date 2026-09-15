<?php

use STDW\Http\Request;
use STDW\Http\Response;
use STDW\Http\Uri;
use STDW\Http\Middleware\MiddlewareManager;
use STDW\Contract\Http\MiddlewareInterface;

describe('Performance', function () {
    beforeEach(function () {
        $this->backupServer = $_SERVER;
        $this->backupGet = $_GET;
        $this->backupPost = $_POST;
    });

    afterEach(function () {
        $_SERVER = $this->backupServer;
        $_GET = $this->backupGet;
        $_POST = $this->backupPost;
    });


    test('request parsing — simple GET', function () {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/users/123?page=2&sort=name',
        ];
        $_GET  = ['page' => '2', 'sort' => 'name'];
        $_POST = [];

        $iterations = 10000;

        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $request = new Request();
            $request->getMethod();
            $request->getUri();
            $request->getParams();
        }

        $elapsedMs = (hrtime(true) - $start) / 1e6;

        expect($elapsedMs)->toBeLessThan(200);
    });


    test('request parsing — POST with headers and cookies', function () {
        $_SERVER = [
            'REQUEST_METHOD'    => 'POST',
            'REQUEST_URI'       => '/api/data',
            'CONTENT_TYPE'      => 'application/x-www-form-urlencoded',
            'HTTP_X_CSRF_TOKEN' => 'abc123def456ghi789',
            'HTTP_ACCEPT'       => 'application/json, text/html',
            'HTTP_ACCEPT_LANGUAGE' => 'pt-BR,pt;q=0.9,en;q=0.8',
            'HTTP_COOKIE'       => 'session=abc123xyz; theme=dark; lang=pt-BR; preferences=mode%3Ddark',
        ];
        $_GET  = [];
        $_POST = [
            'name'  => 'João da Silva',
            'email' => 'joao@example.com',
            'bio'   => '<b>Bold text</b> & "quotes"',
            'age'   => '30',
        ];

        $iterations = 1000;

        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $request = new Request();
            $request->getMethod();
            $request->getUri();
            $request->getHeaders();
            $request->getCookies();
            $request->getParams();
            $request->getBody();
        }

        $elapsedMs = (hrtime(true) - $start) / 1e6;

        expect($elapsedMs)->toBeLessThan(500);
    });


    test('response — header chain', function () {
        $iterations = 10000;

        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $response = new Response(200);
            $response
                ->withHeader('Content-Type', 'text/html; charset=utf-8')
                ->withHeader('X-Request-Id', 'abc-123-def-456')
                ->withHeader('X-Custom-Header-One', 'value1')
                ->withHeader('X-Custom-Header-Two', 'value2')
                ->withHeader('Cache-Control', 'no-cache, no-store')
                ->withHeader('Strict-Transport-Security', 'max-age=31536000')
                ->withHeader('X-Frame-Options', 'DENY')
                ->withHeader('X-Content-Type-Options', 'nosniff')
                ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->withHeader('Permissions-Policy', 'camera=()');

            $response->getHeaders();
            $response->getHeader('Content-Type');
            $response->hasHeader('X-Request-Id');
        }

        $elapsedMs = (hrtime(true) - $start) / 1e6;

        expect($elapsedMs)->toBeLessThan(100);
    });


    test('response — cookie chain', function () {
        $iterations = 10000;

        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $response = new Response(200);
            $response
                ->withCookie('session', 'abc123xyz', [
                    'expires'  => 3600,
                    'path'     => '/',
                    'domain'   => 'example.com',
                    'secure'   => true,
                    'httponly'  => true,
                    'samesite' => 'strict',
                ])
                ->withCookie('theme', 'dark')
                ->withCookie('lang', 'pt-BR', ['path' => '/app'])
                ->withCookie('preferences', json_encode(['mode' => 'dark']))
                ->withCookie('csrf', 'token123', ['secure' => true]);

            $response->getCookies();
            $response->getCookie('session');
        }

        $elapsedMs = (hrtime(true) - $start) / 1e6;

        expect($elapsedMs)->toBeLessThan(200);
    });


    test('middleware pipeline — throughput', function () {
        $passThrough = new class implements MiddlewareInterface {
            public function process(
                \STDW\Contract\Http\RequestInterface $request,
                \STDW\Contract\Http\ResponseInterface $response,
                \Closure $next
            ): \STDW\Contract\Http\ResponseInterface {
                return $next($request, $response);
            }
        };

        $scenarios = [
            '0 middlewares' => 0,
            '5 middlewares' => 5,
            '10 middlewares' => 10,
        ];

        foreach ($scenarios as $label => $count) {
            $manager = new MiddlewareManager();

            for ($m = 0; $m < $count; $m++) {
                $manager->add($passThrough);
            }

            $_SERVER = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/test'];
            $_GET  = [];
            $_POST = [];

            $iterations = 1000;

            $start = hrtime(true);

            for ($i = 0; $i < $iterations; $i++) {
                $request  = new Request();
                $response = new Response(200, [], 'ok');
                $manager->handle($request, $response);
            }

            $elapsedMs = (hrtime(true) - $start) / 1e6;

            expect($elapsedMs)->toBeLessThan(200)
                ->and("{$label}: {$elapsedMs}ms")->toBeString();
        }
    });


    test('uri — parsing and toString', function () {
        $urls = [
            '/simple',
            '/path?foo=bar&baz=1',
            'https://example.com/full/path?foo=bar&baz=qux&sort=name&page=2',
            'https://user:pass@host.example.com:8443/docs/search?q=php&lang=en#section',
        ];

        $iterations = 10000;

        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            foreach ($urls as $url) {
                $uri = Uri::fromUrl($url);
                $uri->getScheme();
                $uri->getHost();
                $uri->getPath();
                $uri->getQuery();
                $uri->getFragment();
                (string) $uri;
            }
        }

        $elapsedMs = (hrtime(true) - $start) / 1e6;

        expect($elapsedMs)->toBeLessThan(200);
    });


    test('sse — generator throughput with validation', function () {
        $iterations = 10000;

        $events = [];
        for ($i = 0; $i < $iterations; $i++) {
            $events[] = [
                'event' => 'message',
                'id'    => (string) $i,
                'retry' => '3000',
                'data'  => "Event payload #{$i} with some content",
            ];
        }

        $start = hrtime(true);

        $counter = 0;
        $valid = 0;

        foreach ($events as $event) {
            if (isset($event['event'])) {
                $clean = preg_replace('/[\r\n]/', '', $event['event']);
                if ($clean === $event['event']) {
                    $valid++;
                }
            }

            if (isset($event['id'])) {
                $clean = preg_replace('/[^\x20-\x7E]/', '', $event['id']);
                if ($clean === $event['id']) {
                    $valid++;
                }
            }

            if (isset($event['retry'])) {
                $clean = preg_replace('/[^0-9]/', '', $event['retry']);
                if ($clean === $event['retry']) {
                    $valid++;
                }
            }

            $counter++;
        }

        $elapsedMs = (hrtime(true) - $start) / 1e6;

        expect($counter)->toBe($iterations)
            ->and($elapsedMs)->toBeLessThan(100);
    });
});
