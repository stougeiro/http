<?php

use STDW\Http\Response;
use STDW\Http\Response\JsonResponse;
use STDW\Http\Response\HtmlResponse;
use STDW\Http\Response\TextResponse;
use STDW\Http\Response\RedirectResponse;
use STDW\Http\Response\EmptyResponse;
use STDW\Http\Response\FileResponse;
use STDW\Http\Response\SseResponse;
use Tests\Traits\TestableResponse;

class TestableJsonResponse extends JsonResponse
{
    use TestableResponse;
}

class TestableHtmlResponse extends HtmlResponse
{
    use TestableResponse;
}

class TestableTextResponse extends TextResponse
{
    use TestableResponse;
}

class TestableRedirectResponse extends RedirectResponse
{
    use TestableResponse;

    public string $location;

    public function __construct(string $location, int $status = 302, array $headers = [], ?string $body = '')
    {
        $this->location = $location;
        parent::__construct($location, $status, $headers, $body);
    }
}

class TestableEmptyResponse extends EmptyResponse
{
    use TestableResponse;
}

class TestableFileResponse extends FileResponse
{
    use TestableResponse;
}

class TestableSseResponse extends SseResponse
{
    use TestableResponse;

    public function __construct(\Closure $eventGenerator, int $status = 200, array $headers = [], ?string $body = '')
    {
        parent::__construct($eventGenerator, $status, $headers, $body);
    }
}

describe('JsonResponse Send', function () {
    test('sets Content-Type to application/json', function () {
        $response = new TestableJsonResponse();
        $response->send();

        expect($response->sentHeaders['content-type'])->toBe('application/json; charset=utf-8');
    });

    test('sends body after setting headers', function () {
        $response = new TestableJsonResponse(200, [], '{"ok":true}');
        $response->send();

        expect($response->sentBody)->toBe(['{"ok":true}']);
    });
});

describe('HtmlResponse Send', function () {
    test('sets Content-Type to text/html', function () {
        $response = new TestableHtmlResponse();
        $response->send();

        expect($response->sentHeaders['content-type'])->toBe('text/html; charset=utf-8');
    });

    test('sends body after setting headers', function () {
        $response = new TestableHtmlResponse(200, [], '<p>hi</p>');
        $response->send();

        expect($response->sentBody)->toBe(['<p>hi</p>']);
    });
});

describe('TextResponse Send', function () {
    test('sets Content-Type to text/plain', function () {
        $response = new TestableTextResponse();
        $response->send();

        expect($response->sentHeaders['content-type'])->toBe('text/plain; charset=utf-8');
    });

    test('sends body after setting headers', function () {
        $response = new TestableTextResponse(200, [], 'plain text');
        $response->send();

        expect($response->sentBody)->toBe(['plain text']);
    });
});

describe('RedirectResponse Send', function () {
    test('sets Location header', function () {
        $response = new TestableRedirectResponse('/login');
        $response->send();

        expect($response->sentHeaders['location'])->toBe('/login');
    });

    test('sends 302 status by default', function () {
        $response = new TestableRedirectResponse('/login');
        $response->send();

        expect($response->sentStatus)->toBe([302]);
    });

    test('does not send body', function () {
        $response = new TestableRedirectResponse('/login');
        $response->send();

        expect($response->sentBody)->toBe([]);
    });
});

describe('EmptyResponse Send', function () {
    test('removes content-type header', function () {
        $response = new TestableEmptyResponse(204, ['Content-Type' => 'text/html']);
        $response->send();

        expect($response->sentHeaders)->not->toHaveKey('content-type');
    });

    test('removes content-length header', function () {
        $response = new TestableEmptyResponse(204, ['Content-Length' => '100']);
        $response->send();

        expect($response->sentHeaders)->not->toHaveKey('content-length');
    });

    test('removes transfer-encoding header', function () {
        $response = new TestableEmptyResponse(204, ['Transfer-Encoding' => 'chunked']);
        $response->send();

        expect($response->sentHeaders)->not->toHaveKey('transfer-encoding');
    });

    test('removes content-encoding header', function () {
        $response = new TestableEmptyResponse(204, ['Content-Encoding' => 'gzip']);
        $response->send();

        expect($response->sentHeaders)->not->toHaveKey('content-encoding');
    });

    test('does not send body', function () {
        $response = new TestableEmptyResponse(204);
        $response->send();

        expect($response->sentBody)->toBe([]);
    });
});

describe('FileResponse Send', function () {
    it('sets status 404 when file does not exist', function () {
        $response = new TestableFileResponse('/nonexistent/file.txt');
        $response->send();

        expect($response->sentStatus)->toBe([404]);
    });

    it('sets status 403 when file is not readable', function () {
        $file = tempnam(sys_get_temp_dir(), 'test_');
        chmod($file, 0000);

        $response = new TestableFileResponse($file);
        $response->send();

        expect($response->sentStatus)->toBe([403]);

        chmod($file, 0644);
        unlink($file);
    });

    it('sets download headers for valid readable file', function () {
        $file = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($file, 'content');

        $response = new TestableFileResponse($file);
        $response->send();

        expect($response->sentHeaders)->toHaveKey('content-description');
        expect($response->sentHeaders['content-description'])->toBe('File Transfer');
        expect($response->sentHeaders)->toHaveKey('content-type');
        expect($response->sentHeaders['content-type'])->toBe('application/octet-stream');
        expect($response->sentHeaders)->toHaveKey('content-disposition');
        expect($response->sentHeaders)->toHaveKey('content-transfer-encoding');
        expect($response->sentHeaders['content-transfer-encoding'])->toBe('binary');
        expect($response->sentHeaders)->toHaveKey('content-length');

        unlink($file);
    });

    it('does not send body for non-200 status', function () {
        $response = new TestableFileResponse('/nonexistent/file.txt');
        $response->send();

        expect($response->sentBody)->toBe([]);
    });
});

describe('SseResponse Send', function () {
    test('sets SSE headers on send', function () {
        $callCount = 0;
        $generator = function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return ['data' => 'hello'];
            }
            return null;
        };

        $response = new TestableSseResponse($generator);
        $response->send();

        expect($response->sentHeaders['content-type'])->toBe('text/event-stream');
        expect($response->sentHeaders['cache-control'])->toBe('no-cache');
        expect($response->sentHeaders['connection'])->toBe('keep-alive');
    });

    test('formats simple event with data field', function () {
        $callCount = 0;
        $generator = function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return ['data' => 'hello world'];
            }
            return null;
        };

        $response = new TestableSseResponse($generator);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        expect($output)->toContain('data: hello world');
        expect($output)->toContain("\n\n");
    });

    test('formats event with all optional fields', function () {
        $callCount = 0;
        $generator = function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return [
                    'event' => 'message',
                    'id' => '42',
                    'retry' => '3000',
                    'data' => 'payload',
                ];
            }
            return null;
        };

        $response = new TestableSseResponse($generator);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        expect($output)->toContain('event: message');
        expect($output)->toContain('id: 42');
        expect($output)->toContain('retry: 3000');
        expect($output)->toContain('data: payload');
    });

    test('formats array data as multiple data lines', function () {
        $callCount = 0;
        $generator = function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return [
                    'data' => ['line1', 'line2', 'line3'],
                ];
            }
            return null;
        };

        $response = new TestableSseResponse($generator);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        expect($output)->toContain('data: line1');
        expect($output)->toContain('data: line2');
        expect($output)->toContain('data: line3');
    });

    test('stops when generator returns null', function () {
        $generator = function () {
            return null;
        };

        $response = new TestableSseResponse($generator);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        expect($output)->toBe('');
    });

    test('ignores event without data field', function () {
        $callCount = 0;
        $generator = function () use (&$callCount) {
            $callCount++;
            if ($callCount === 1) {
                return ['event' => 'no-data'];
            }
            if ($callCount === 2) {
                return ['data' => 'valid'];
            }
            return null;
        };

        $response = new TestableSseResponse($generator);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        expect($output)->not->toContain('event: no-data');
        expect($output)->toContain('data: valid');
    });
});
