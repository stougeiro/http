<?php declare(strict_types=1);

use STDW\Http\Response\FileResponse;
use InvalidArgumentException;

describe('FileResponse', function () {

    describe('File Path Requirement', function () {
        it('requires a file path in constructor', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            expect($response)->toBeInstanceOf(FileResponse::class);
        });

        it('accepts file path with various formats', function () {
            $paths = [
                '/absolute/path/file.txt',
                'relative/path/file.pdf',
                './current/file.doc',
                '../parent/file.zip',
                'single_file.txt',
            ];
            
            foreach ($paths as $path) {
                $response = new FileResponse($path);
                expect($response)->toBeInstanceOf(FileResponse::class);
            }
        });
    });

    describe('Status Code Guarantee', function () {
        it('only allows status codes 200, 403, or 404', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            expect(fn () => $response->withStatus(200))->not->toThrow(Throwable::class);
            expect(fn () => $response->withStatus(403))->not->toThrow(Throwable::class);
            expect(fn () => $response->withStatus(404))->not->toThrow(Throwable::class);
        });

        it('throws exception for invalid status codes', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            $invalidCodes = [100, 201, 202, 204, 301, 302, 400, 401, 500];
            
            foreach ($invalidCodes as $code) {
                expect(fn () => $response->withStatus($code))
                    ->toThrow(InvalidArgumentException::class, "Invalid HTTP status code for FileResponse: {$code}");
            }
        });

        it('throws for any status outside 200, 403, 404', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            expect(fn () => $response->withStatus(201))
                ->toThrow(InvalidArgumentException::class);
            
            expect(fn () => $response->withStatus(199))
                ->toThrow(InvalidArgumentException::class);
            
            expect(fn () => $response->withStatus(205))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('Body Guarantee', function () {
        it('must not have a body', function () {
            $response = new FileResponse('/path/to/file.txt');
            expect($response->getBody())->toBeEmpty();
        });

        it('throws exception when trying to set non-empty body', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            expect(fn () => $response->withBody('file content'))
                ->toThrow(InvalidArgumentException::class, 'Invalid body on FileResponse');
        });

        it('allows empty or null body', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            $result1 = $response->withBody('');
            expect($result1)->toBe($response);

            $result2 = $response->withBody(null);
            expect($result2)->toBe($response)
                ->and($response->getBody())->toBeEmpty();
        });

        it('throws for any non-empty body', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            $invalidBodies = ['text', ' ', '0', 'null', 'false', "\n"];
            
            foreach ($invalidBodies as $body) {
                expect(fn () => $response->withBody($body))
                    ->toThrow(InvalidArgumentException::class);
            }
        });
    });

    describe('Constructor Behavior', function () {
        it('requires file path parameter', function () {
            $response = new FileResponse('/path/to/file.txt');
            expect($response)->toBeInstanceOf(FileResponse::class);
        });

        it('accepts optional status parameter', function () {
            $response = new FileResponse('/path/to/file.txt', 200);
            expect($response->getStatus())->toBe(200);
        });

        it('throws when initialized with invalid status', function () {
            expect(fn () => new FileResponse('/path/to/file.txt', 201))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when initialized with non-empty body', function () {
            expect(fn () => new FileResponse('/path/to/file.txt', 200, [], 'content'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts empty body in constructor', function () {
            $response = new FileResponse('/path/to/file.txt', 200, [], '');
            expect($response->getBody())->toBeEmpty();
        });

        it('accepts headers in constructor', function () {
            $response = new FileResponse(
                '/path/to/file.txt',
                200,
                ['X-Custom' => 'value']
            );
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });
    });

    describe('Header Management', function () {
        it('accepts custom headers', function () {
            $response = new FileResponse('/path/to/file.txt');
            $response->withHeader('X-Custom', 'value');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });

        it('can set multiple headers', function () {
            $response = new FileResponse('/path/to/file.txt');
            $response
                ->withHeader('X-Header-1', 'value1')
                ->withHeader('X-Header-2', 'value2')
                ->withHeader('X-Header-3', 'value3');
            
            $headers = $response->getHeaders();
            expect($headers)->toHaveKeys(['x-header-1', 'x-header-2', 'x-header-3']);
        });
    });

    describe('Fluent Interface', function () {
        it('returns self for method chaining', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            $result = $response->withStatus(200);
            expect($result)->toBe($response);
        });

        it('chains multiple valid operations', function () {
            $response = new FileResponse('/path/to/file.txt');
            
            $result = $response
                ->withStatus(200)
                ->withBody(null)
                ->withHeader('X-Custom-Header', 'value');
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(200);
        });
    });

    describe('Default Status Behavior', function () {
        it('initializes with default status 200', function () {
            $response = new FileResponse('/path/to/file.txt');
            expect($response->getStatus())->toBe(200);
        });
    });

    describe('Status Code Semantics', function () {
        it('status 200 indicates successful file response', function () {
            $response = new FileResponse('/valid/file.txt', 200);
            expect($response->getStatus())->toBe(200);
        });

        it('status 403 indicates file is not readable', function () {
            $response = new FileResponse('/restricted/file.txt');
            $response->withStatus(403);
            expect($response->getStatus())->toBe(403);
        });

        it('status 404 indicates file not found', function () {
            $response = new FileResponse('/nonexistent/file.txt');
            $response->withStatus(404);
            expect($response->getStatus())->toBe(404);
        });
    });

    describe('Multiple File Paths', function () {
        it('handles different file types', function () {
            $paths = [
                '/path/to/document.pdf',
                '/path/to/image.png',
                '/path/to/archive.zip',
                '/path/to/script.js',
                '/path/to/data.csv',
            ];
            
            foreach ($paths as $path) {
                $response = new FileResponse($path);
                expect($response)->toBeInstanceOf(FileResponse::class);
            }
        });

        it('handles paths with special characters', function () {
            $paths = [
                '/path/to/file with spaces.txt',
                '/path/to/file-with-dashes.pdf',
                '/path/to/file_with_underscores.zip',
                '/path/to/file (1).doc',
            ];
            
            foreach ($paths as $path) {
                $response = new FileResponse($path);
                expect($response)->toBeInstanceOf(FileResponse::class);
            }
        });
    });
});
