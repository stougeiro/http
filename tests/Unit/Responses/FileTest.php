<?php declare(strict_types=1);

use STDW\Http\Response\FileResponse;

describe('FileResponse', function () {

    describe('File Path Requirement', function () {
        it('requires a file path in constructor', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            expect($response)->toBeInstanceOf(FileResponse::class);
            unlink($path);
        });

        it('accepts file path with various formats', function () {
            $extensions = ['txt', 'pdf', 'png', 'zip'];
            foreach ($extensions as $ext) {
                $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . ".{$ext}";
                file_put_contents($path, 'content');
                $response = new FileResponse($path);
                expect($response)->toBeInstanceOf(FileResponse::class);
                unlink($path);
            }
        });

        it('throws exception for path traversal with ..', function () {
            expect(fn () => new FileResponse('../etc/passwd'))
                ->toThrow(InvalidArgumentException::class, 'path traversal detected');
        });

        it('throws exception for absolute path traversal', function () {
            expect(fn () => new FileResponse('/var/www/../../etc/passwd'))
                ->toThrow(InvalidArgumentException::class, 'path traversal detected');
        });

        it('throws exception for relative traversal', function () {
            expect(fn () => new FileResponse('docs/../../secret'))
                ->toThrow(InvalidArgumentException::class, 'path traversal detected');
        });

        it('throws exception for nonexistent file', function () {
            expect(fn () => new FileResponse('/nonexistent/file.txt'))
                ->toThrow(InvalidArgumentException::class, 'file does not exist');
        });
    });

    describe('Status Code Guarantee', function () {
        it('only allows status codes 200, 403, or 404', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            expect(fn () => $response->withStatus(200))->not->toThrow(Throwable::class);
            expect(fn () => $response->withStatus(403))->not->toThrow(Throwable::class);
            expect(fn () => $response->withStatus(404))->not->toThrow(Throwable::class);
            unlink($path);
        });

        it('throws exception for invalid status codes', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            $invalidCodes = [100, 201, 202, 204, 301, 302, 400, 401, 500];

            foreach ($invalidCodes as $code) {
                expect(fn () => $response->withStatus($code))
                    ->toThrow(InvalidArgumentException::class, "Invalid HTTP status code for FileResponse: {$code}");
            }
            unlink($path);
        });

        it('throws for any status outside 200, 403, 404', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            expect(fn () => $response->withStatus(201))
                ->toThrow(InvalidArgumentException::class);

            expect(fn () => $response->withStatus(199))
                ->toThrow(InvalidArgumentException::class);

            expect(fn () => $response->withStatus(205))
                ->toThrow(InvalidArgumentException::class);
            unlink($path);
        });
    });

    describe('Body Guarantee', function () {
        it('must not have a body', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            expect($response->getBody())->toBeEmpty();
            unlink($path);
        });

        it('throws exception when trying to set non-empty body', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            expect(fn () => $response->withBody('file content'))
                ->toThrow(InvalidArgumentException::class, 'Invalid body on FileResponse');
            unlink($path);
        });

        it('allows empty or null body', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            $result1 = $response->withBody('');
            expect($result1)->toBe($response);

            $result2 = $response->withBody(null);
            expect($result2)->toBe($response)
                ->and($response->getBody())->toBeEmpty();
            unlink($path);
        });

        it('throws for any non-empty body', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            $invalidBodies = ['text', ' ', '0', 'null', 'false', "\n"];

            foreach ($invalidBodies as $body) {
                expect(fn () => $response->withBody($body))
                    ->toThrow(InvalidArgumentException::class);
            }
            unlink($path);
        });
    });

    describe('Constructor Behavior', function () {
        it('requires file path parameter', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            expect($response)->toBeInstanceOf(FileResponse::class);
            unlink($path);
        });

        it('accepts optional status parameter', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path, 200);
            expect($response->getStatus())->toBe(200);
            unlink($path);
        });

        it('throws when initialized with invalid status', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            expect(fn () => new FileResponse($path, 201))
                ->toThrow(InvalidArgumentException::class);
            unlink($path);
        });

        it('throws when initialized with non-empty body', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            expect(fn () => new FileResponse($path, 200, [], 'content'))
                ->toThrow(InvalidArgumentException::class);
            unlink($path);
        });

        it('accepts empty body in constructor', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path, 200, [], '');
            expect($response->getBody())->toBeEmpty();
            unlink($path);
        });

        it('accepts headers in constructor', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse(
                $path,
                200,
                ['X-Custom' => 'value']
            );

            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
            unlink($path);
        });
    });

    describe('Header Management', function () {
        it('accepts custom headers', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            $response->withHeader('X-Custom', 'value');

            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
            unlink($path);
        });

        it('can set multiple headers', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            $response
                ->withHeader('X-Header-1', 'value1')
                ->withHeader('X-Header-2', 'value2')
                ->withHeader('X-Header-3', 'value3');

            $headers = $response->getHeaders();
            expect($headers)->toHaveKeys(['x-header-1', 'x-header-2', 'x-header-3']);
            unlink($path);
        });
    });

    describe('Fluent Interface', function () {
        it('returns self for method chaining', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            $result = $response->withStatus(200);
            expect($result)->toBe($response);
            unlink($path);
        });

        it('chains multiple valid operations', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);

            $result = $response
                ->withStatus(200)
                ->withBody(null)
                ->withHeader('X-Custom-Header', 'value');

            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(200);
            unlink($path);
        });
    });

    describe('Default Status Behavior', function () {
        it('initializes with default status 200', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            expect($response->getStatus())->toBe(200);
            unlink($path);
        });
    });

    describe('Status Code Semantics', function () {
        it('status 200 indicates successful file response', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path, 200);
            expect($response->getStatus())->toBe(200);
            unlink($path);
        });

        it('status 403 indicates file is not readable', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            $response->withStatus(403);
            expect($response->getStatus())->toBe(403);
            unlink($path);
        });

        it('status 404 indicates file not found', function () {
            $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . '.txt';
            file_put_contents($path, 'content');
            $response = new FileResponse($path);
            $response->withStatus(404);
            expect($response->getStatus())->toBe(404);
            unlink($path);
        });
    });

    describe('Multiple File Paths', function () {
        it('handles different file types', function () {
            $extensions = ['txt', 'pdf', 'png', 'zip', 'js', 'csv'];
            foreach ($extensions as $ext) {
                $path = sys_get_temp_dir() . '/file_response_test_' . uniqid() . ".{$ext}";
                file_put_contents($path, 'content');
                $response = new FileResponse($path);
                expect($response)->toBeInstanceOf(FileResponse::class);
                unlink($path);
            }
        });
    });
});
