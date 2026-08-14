<?php declare(strict_types=1);

use STDW\Http\Response\RedirectResponse;
use InvalidArgumentException;

describe('RedirectResponse', function () {
    describe('Location Requirement', function () {
        it('requires location URL in constructor', function () {
            $response = new RedirectResponse('/new-location');
            
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('accepts various URL formats', function () {
            $urls = [
                '/path/to/page',
                'http://example.com',
                'https://example.com/path',
                'https://example.com?query=value',
                'https://example.com/path#hash',
                '../relative/path',
                './same/directory',
                '/absolute/path',
                'page.html',
            ];
            
            foreach ($urls as $url) {
                $response = new RedirectResponse($url);
                expect($response)->toBeInstanceOf(RedirectResponse::class);
            }
        });
    });

    describe('Status Code Guarantee', function () {
        it('only allows 3xx status codes (300-399)', function () {
            $validStatuses = [300, 301, 302, 303, 304, 305, 306, 307, 308];
            
            foreach ($validStatuses as $status) {
                $response = new RedirectResponse('/location', $status);
                expect($response->getStatus())->toBe($status);
            }
        });

        it('has default status 302 (Found)', function () {
            $response = new RedirectResponse('/new-location');
            expect($response->getStatus())->toBe(302);
        });

        it('throws exception for non-3xx status codes', function () {
            $response = new RedirectResponse('/location');
            
            $invalidCodes = [100, 200, 201, 299, 400, 404, 500];
            
            foreach ($invalidCodes as $code) {
                expect(fn () => $response->withStatus($code))
                    ->toThrow(InvalidArgumentException::class, "Invalid HTTP status code: {$code}");
            }
        });

        it('throws for status codes below 300', function () {
            $response = new RedirectResponse('/location');
            
            expect(fn () => $response->withStatus(200))
                ->toThrow(InvalidArgumentException::class);
            
            expect(fn () => $response->withStatus(299))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws for status codes above 399', function () {
            $response = new RedirectResponse('/location');
            
            expect(fn () => $response->withStatus(400))
                ->toThrow(InvalidArgumentException::class);
            
            expect(fn () => $response->withStatus(399 + 1))
                ->toThrow(InvalidArgumentException::class);
        });

        it('allows edge case status codes 300 and 399', function () {
            $response1 = new RedirectResponse('/location', 300);
            expect($response1->getStatus())->toBe(300);
            
            $response2 = new RedirectResponse('/location', 399);
            expect($response2->getStatus())->toBe(399);
        });
    });

    describe('Body Guarantee', function () {
        it('must not have a body', function () {
            $response = new RedirectResponse('/location');
            expect($response->getBody())->toBeEmpty();
        });

        it('throws exception when trying to set non-empty body', function () {
            $response = new RedirectResponse('/location');
            
            expect(fn () => $response->withBody('redirect content'))
                ->toThrow(InvalidArgumentException::class, 'Invalid body on RedirectResponse');
        });

        it('allows empty or null body', function () {
            $response = new RedirectResponse('/location');
            
            $result1 = $response->withBody('');
            expect($result1)->toBe($response);

            $result2 = $response->withBody(null);
            expect($result2)->toBe($response)
                ->and($response->getBody())->toBeEmpty();
        });

        it('throws for any non-empty body', function () {
            $response = new RedirectResponse('/location');
            
            $invalidBodies = ['text', ' ', '0', 'null', 'false', "\n"];
            
            foreach ($invalidBodies as $body) {
                expect(fn () => $response->withBody($body))
                    ->toThrow(InvalidArgumentException::class);
            }
        });
    });

    describe('Constructor Behavior', function () {
        it('requires location parameter', function () {
            $response = new RedirectResponse('/target');
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('accepts optional status parameter', function () {
            $response = new RedirectResponse('/target', 301);
            expect($response->getStatus())->toBe(301);
        });

        it('throws when initialized with invalid status', function () {
            expect(fn () => new RedirectResponse('/target', 200))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when initialized with non-empty body', function () {
            expect(fn () => new RedirectResponse('/target', 302, [], 'content'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts empty body in constructor', function () {
            $response = new RedirectResponse('/target', 302, [], '');
            expect($response->getBody())->toBeEmpty();
        });

        it('accepts headers in constructor', function () {
            $response = new RedirectResponse(
                '/target',
                301,
                ['X-Custom' => 'value']
            );
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });

        it('initializes with all parameters', function () {
            $response = new RedirectResponse(
                'https://example.com/new-path',
                301,
                ['X-Header' => 'value'],
                ''
            );
            
            expect($response->getStatus())->toBe(301)
                ->and($response->getHeaders()['x-header'])->toBe('value');
        });
    });

    describe('Status Code Semantics', function () {
        it('300 Multiple Choices is valid', function () {
            $response = new RedirectResponse('/choices', 300);
            expect($response->getStatus())->toBe(300);
        });

        it('301 Moved Permanently is valid', function () {
            $response = new RedirectResponse('/permanent', 301);
            expect($response->getStatus())->toBe(301);
        });

        it('302 Found (default) is valid', function () {
            $response = new RedirectResponse('/found');
            expect($response->getStatus())->toBe(302);
        });

        it('303 See Other is valid', function () {
            $response = new RedirectResponse('/other', 303);
            expect($response->getStatus())->toBe(303);
        });

        it('304 Not Modified is valid', function () {
            $response = new RedirectResponse('/cached', 304);
            expect($response->getStatus())->toBe(304);
        });

        it('307 Temporary Redirect is valid', function () {
            $response = new RedirectResponse('/temp', 307);
            expect($response->getStatus())->toBe(307);
        });

        it('308 Permanent Redirect is valid', function () {
            $response = new RedirectResponse('/perm', 308);
            expect($response->getStatus())->toBe(308);
        });
    });

    describe('Fluent Interface', function () {
        it('returns self for method chaining', function () {
            $response = new RedirectResponse('/location');
            
            $result = $response->withStatus(301);
            expect($result)->toBe($response);
        });

        it('chains multiple valid operations', function () {
            $response = new RedirectResponse('/target');
            
            $result = $response
                ->withStatus(301)
                ->withBody(null)
                ->withHeader('X-Redirect-Reason', 'permanent');
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(301);
        });
    });

    describe('Header Management', function () {
        it('accepts custom headers', function () {
            $response = new RedirectResponse('/location');
            $response->withHeader('X-Custom', 'value');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });

        it('can set multiple headers', function () {
            $response = new RedirectResponse('/location');
            $response
                ->withHeader('X-Header-1', 'value1')
                ->withHeader('X-Header-2', 'value2')
                ->withHeader('X-Header-3', 'value3');
            
            $headers = $response->getHeaders();
            expect($headers)->toHaveKeys(['x-header-1', 'x-header-2', 'x-header-3']);
        });
    });

    describe('URL Variations', function () {
        it('handles absolute URLs', function () {
            $response = new RedirectResponse('https://example.com/path');
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('handles relative URLs', function () {
            $response = new RedirectResponse('/relative/path');
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('handles URLs with query strings', function () {
            $response = new RedirectResponse('/path?param=value&other=123');
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('handles URLs with fragments', function () {
            $response = new RedirectResponse('/path#section');
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('handles URLs with port numbers', function () {
            $response = new RedirectResponse('https://example.com:8080/path');
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });

        it('handles URLs with authentication', function () {
            $response = new RedirectResponse('https://user:pass@example.com/path');
            expect($response)->toBeInstanceOf(RedirectResponse::class);
        });
    });

    describe('Changing Status Code', function () {
        it('can change to different 3xx status', function () {
            $response = new RedirectResponse('/location', 302);
            
            $response->withStatus(301);
            expect($response->getStatus())->toBe(301);
            
            $response->withStatus(307);
            expect($response->getStatus())->toBe(307);
        });

        it('cannot change to non-3xx status', function () {
            $response = new RedirectResponse('/location');
            
            expect(fn () => $response->withStatus(200))
                ->toThrow(InvalidArgumentException::class);
        });
    });
});
