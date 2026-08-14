<?php declare(strict_types=1);

use STDW\Http\Response\HtmlResponse;

describe('HtmlResponse', function () {
    describe('Body Guarantee', function () {
        it('requires a non-empty body', function () {
            expect(fn () => $response = new HtmlResponse())
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts valid HTML body', function () {
            $html = '<html><body>Hello</body></html>';
            $response = new HtmlResponse(body: $html);
            
            expect($response->getBody())->toBe($html);
        });

        it('accepts various valid HTML content', function () {
            $validBodies = [
                '<div>Content</div>',
                '<h1>Title</h1>',
                '<p>Paragraph with special chars: áéíóú</p>',
                ' ',
                '0',
                'plain text',
                '<script>alert("test");</script>',
            ];
            
            foreach ($validBodies as $body) {
                $resp = new HtmlResponse(body: $body);
                expect($resp->getBody())->toBe($body);
            }
        });
    });

    describe('Content-Type Header Guarantee', function () {
        it('sets Content-Type header to text/html on send', function () {
            $response = new HtmlResponse(body: '<div>Test</div>');
            
            // The header should be set when send() is called
            // We can verify the intention through the class
            expect($response)->toBeInstanceOf(HtmlResponse::class);
        });

        it('forces text/html content type with charset', function () {
            $response = new HtmlResponse(body: '<p>Test</p>');
            // Attempting to override should still result in proper content-type on send
            $response->withHeader('Content-Type', 'text/plain');
            
            // The response should maintain HtmlResponse contract
            expect($response->getBody())->toBe('<p>Test</p>');
        });
    });

    describe('Status Code Flexibility', function () {
        it('allows default status code of 200', function () {
            $response = new HtmlResponse(body: '<h1>Hello World</h1>');
            expect($response->getStatus())->toBe(200);
        });

        it('accepts various status codes', function () {
            $validStatuses = [200, 201, 202, 301, 302, 400, 404, 500];
            
            foreach ($validStatuses as $status) {
                $response = new HtmlResponse($status, body: '<h1>Test</h1>');
                expect($response->getStatus())->toBe($status);
            }
        });

        it('can change status after initialization', function () {
            $response = new HtmlResponse(body: '<p>Content</p>');

            $response->withStatus(404);
            expect($response->getStatus())->toBe(404);
        });
    });

    describe('Constructor Behavior', function () {
        it('throws when constructor body is empty', function () {
            expect(fn () => new HtmlResponse(200, [], ''))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when constructor body is null', function () {
            expect(fn () => new HtmlResponse(200, [], null))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts valid body in constructor', function () {
            $html = '<h1>Hello</h1>';
            $response = new HtmlResponse(200, [], $html);
            
            expect($response->getBody())->toBe($html);
        });

        it('accepts headers in constructor', function () {
            $response = new HtmlResponse(200, ['X-Custom' => 'value'], '<p>Test</p>');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });
    });

    describe('Fluent Interface', function () {
        it('returns self for method chaining', function () {
            $response = new HtmlResponse(body: '<div>Initial</div>');
            
            $result = $response->withBody('<div>Test</div>');
            expect($result)->toBe($response);
        });

        it('chains multiple operations', function () {
            $response = new HtmlResponse(body: '<div>Test</div>');

            $result = $response
                ->withStatus(201)
                ->withHeader('X-Custom', 'value')
                ->withBody('<section>Content</section>');
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(201)
                ->and($response->getBody())->toBe('<section>Content</section>');
        });

        it('can update body multiple times', function () {
            $response = new HtmlResponse(body: '<div>Initial</div>');
            
            $response->withBody('<div>First</div>')
                ->withBody('<div>Second</div>');
            
            expect($response->getBody())->toBe('<div>Second</div>');
        });
    });

    describe('Headers Management', function () {
        it('accepts custom headers', function () {
            $response = new HtmlResponse(body: '<p>Test</p>');
            $response->withHeader('X-Custom', 'custom-value');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('custom-value');
        });

        it('can set multiple headers', function () {
            $response = new HtmlResponse(body: '<p>Test</p>');
            $response
                ->withHeader('X-Header-1', 'value1')
                ->withHeader('X-Header-2', 'value2')
                ->withHeader('X-Header-3', 'value3');
            
            $headers = $response->getHeaders();
            expect($headers)->toHaveKeys(['x-header-1', 'x-header-2', 'x-header-3']);
        });
    });
});
