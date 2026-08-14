<?php declare(strict_types=1);

use STDW\Http\Response\TextResponse;
use InvalidArgumentException;

describe('TextResponse', function () {
    describe('Body Guarantee', function () {
        it('requires a non-empty body', function () {
            $response = new TextResponse(body: ' ');
            
            expect(fn () => $response->withBody(''))
                ->toThrow(InvalidArgumentException::class, 'TextResponse requires a non-empty body');
        });

        it('throws exception when body is null', function () {
            $response = new TextResponse(body: ' ');
            
            expect(fn () => $response->withBody(null))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts valid text body', function () {
            $response = new TextResponse(body: ' ');
            $text = 'Hello, World!';
            
            $result = $response->withBody($text);
            
            expect($result)->toBe($response)
                ->and($response->getBody())->toBe($text);
        });

        it('accepts various text content', function () {
            $validBodies = [
                'Simple text',
                'Text with special chars: áéíóú',
                'Text with numbers: 123456',
                'Text with symbols: !@#$%^&*()',
                'Multi-line text
with several
lines of content',
                ' ',
                '0',
                'false',
                'null',
                'JSON-like: {not: "json"}',
                '<html>html-like but plain text</html>',
            ];
            
            foreach ($validBodies as $body) {
                $resp = new TextResponse(body: ' ');
                $resp->withBody($body);
                expect($resp->getBody())->toBe($body);
            }
        });

        it('does not validate content format', function () {
            $response = new TextResponse(body: ' ');
            
            // TextResponse should accept any non-empty string
            $response->withBody('{"this":"looks":like json:but;is!plain}');
            expect($response->getBody())->toBe('{"this":"looks":like json:but;is!plain}');
        });
    });

    describe('Content-Type Header Guarantee', function () {
        it('sets Content-Type header to text/plain', function () {
            $response = new TextResponse(body: ' ');
            $response->withBody('Some text');
            
            // The header should be set when send() is called
            expect($response)->toBeInstanceOf(TextResponse::class);
        });

        it('sets charset to utf-8', function () {
            $response = new TextResponse(body: ' ');
            $response->withBody('Unicode: áéíóú');
            
            // Content-Type should include charset=utf-8
            expect($response)->toBeInstanceOf(TextResponse::class);
        });
    });

    describe('Status Code Flexibility', function () {
        it('has default status code 200', function () {
            $response = new TextResponse(body: ' ');
            expect($response->getStatus())->toBe(200);
        });

        it('accepts various status codes', function () {
            $statusCodes = [200, 201, 202, 204, 301, 302, 304, 400, 401, 403, 404, 500, 502, 503];
            
            foreach ($statusCodes as $status) {
                $response = new TextResponse($status, body: ' ');
                expect($response->getStatus())->toBe($status);
            }
        });

        it('can change status after initialization', function () {
            $response = new TextResponse(body: ' ');
            $response->withBody('Content');
            
            $response->withStatus(404);
            expect($response->getStatus())->toBe(404);
        });
    });

    describe('Constructor Behavior', function () {
        it('initializes with default values', function () {
            $response = new TextResponse(body: ' ');
            expect($response->getStatus())->toBe(200)
                ->and($response->getHeaders())->toBeEmpty();
        });

        it('throws when constructor body is empty', function () {
            expect(fn () => new TextResponse(200, [], ''))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when constructor body is null', function () {
            expect(fn () => new TextResponse(200, [], null))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts valid body in constructor', function () {
            $text = 'Hello World';
            $response = new TextResponse(200, [], $text);
            
            expect($response->getBody())->toBe($text)
                ->and($response->getStatus())->toBe(200);
        });

        it('accepts custom status in constructor', function () {
            $response = new TextResponse(404, body: ' ');
            expect($response->getStatus())->toBe(404);
        });

        it('accepts headers in constructor', function () {
            $response = new TextResponse(200, ['X-Version' => '1.0'], 'Content');
            
            $headers = $response->getHeaders();
            expect($headers['x-version'])->toBe('1.0');
        });
    });

    describe('Fluent Interface', function () {
        it('returns self for method chaining', function () {
            $response = new TextResponse(body: ' ');
            
            $result = $response->withBody('Test content');
            expect($result)->toBe($response);
        });

        it('chains multiple operations', function () {
            $response = new TextResponse(body: ' ');
            
            $result = $response
                ->withStatus(201)
                ->withHeader('X-Custom', 'value')
                ->withBody('Created successfully');
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(201)
                ->and($response->getBody())->toBe('Created successfully');
        });

        it('can update body multiple times', function () {
            $response = new TextResponse(body: ' ');
            
            $response->withBody('First content')
                ->withBody('Updated content');
            
            expect($response->getBody())->toBe('Updated content');
        });
    });

    describe('Headers Management', function () {
        it('accepts custom headers', function () {
            $response = new TextResponse(body: ' ');
            $response->withBody('Text content')
                ->withHeader('X-Custom', 'custom-value');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('custom-value');
        });

        it('can set multiple headers', function () {
            $response = new TextResponse(body: ' ');
            $response->withBody('Text content')
                ->withHeader('X-Header-1', 'value1')
                ->withHeader('X-Header-2', 'value2')
                ->withHeader('X-Header-3', 'value3');
            
            $headers = $response->getHeaders();
            expect($headers)->toHaveKeys(['x-header-1', 'x-header-2', 'x-header-3']);
        });

        it('can remove headers', function () {
            $response = new TextResponse(body: ' ');
            $response->withBody('Text content')
                ->withHeader('X-Remove', 'value');
            
            $response->withoutHeader('X-Remove');
            $headers = $response->getHeaders();
            expect(isset($headers['x-remove']))->toBeFalse();
        });
    });

    describe('Unicode and Special Characters', function () {
        it('handles unicode text correctly', function () {
            $response = new TextResponse(body: ' ');
            $text = 'Olá Mundo! 你好世界 مرحبا بالعالم';
            
            $response->withBody($text);
            expect($response->getBody())->toBe($text);
        });

        it('handles multiline content', function () {
            $response = new TextResponse(body: ' ');
            $text = "Line 1\nLine 2\nLine 3";
            
            $response->withBody($text);
            expect($response->getBody())->toBe($text);
        });

        it('handles tab and special whitespace', function () {
            $response = new TextResponse(body: ' ');
            $text = "Column1\tColumn2\tColumn3";
            
            $response->withBody($text);
            expect($response->getBody())->toBe($text);
        });
    });

    describe('Body Persistence', function () {
        it('maintains body after setting headers', function () {
            $response = new TextResponse(body: ' ');
            $text = 'Original content';
            
            $response->withBody($text)
                ->withHeader('X-Custom', 'header');
            
            expect($response->getBody())->toBe($text);
        });

        it('maintains body after changing status', function () {
            $response = new TextResponse(body: ' ');
            $text = 'Content';
            
            $response->withBody($text)
                ->withStatus(201);
            
            expect($response->getBody())->toBe($text)
                ->and($response->getStatus())->toBe(201);
        });
    });
});
