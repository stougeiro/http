<?php

use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Http\Middleware\MiddlewareManager;
use PHPUnit\Framework\TestCase;

class MiddlewareTestCase extends TestCase
{
    protected MiddlewareManager $manager;

    protected function setUp(): void
    {
        $this->manager = new MiddlewareManager();
    }
}


uses(MiddlewareTestCase::class);

test('handle without middlewares returns response as is', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($response);
});

test('handle with single middleware calls middleware process', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $returnResponse = $this->createMock(ResponseInterface::class);

    $middleware = $this->createMock(MiddlewareInterface::class);
    $middleware->expects($this->once())
        ->method('process')
        ->with($request, $response)
        ->willReturnCallback(function ($req, $res, $next) {
            return $next($req, $res);
        })
        ->willReturn($returnResponse);

    $this->manager->add($middleware);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($returnResponse);
});

test('multiple middlewares execute in order', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $executionOrder = [];

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware1_before';
            $result = $next($req, $res);
            $executionOrder[] = 'middleware1_after';
            return $result;
        });

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware2_before';
            $result = $next($req, $res);
            $executionOrder[] = 'middleware2_after';
            return $result;
        });

    $middleware3 = $this->createMock(MiddlewareInterface::class);
    $middleware3->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$executionOrder) {
            $executionOrder[] = 'middleware3_before';
            $result = $next($req, $res);
            $executionOrder[] = 'middleware3_after';
            return $result;
        });

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);
    $this->manager->add($middleware3);

    $this->manager->handle($request, $response);

    expect($executionOrder)->toBe([
        'middleware1_before',
        'middleware2_before',
        'middleware3_before',
        'middleware3_after',
        'middleware2_after',
        'middleware1_after',
    ]);
});

test('middleware can modify response', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $modifiedResponse = $this->createMock(ResponseInterface::class);

    $middleware = $this->createMock(MiddlewareInterface::class);
    $middleware->expects($this->once())
        ->method('process')
        ->willReturnCallback(function () use ($modifiedResponse) {
            return $modifiedResponse;
        });

    $this->manager->add($middleware);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($modifiedResponse);
});

test('middleware can short circuit chain', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $shortCircuitResponse = $this->createMock(ResponseInterface::class);

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturn($shortCircuitResponse);

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->never())
        ->method('process');

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($shortCircuitResponse);
});

test('final handler is called when middlewares finished', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $finalResponse = $this->createMock(ResponseInterface::class);

    $middleware = $this->createMock(MiddlewareInterface::class);
    $middleware->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) {
            return $next($req, $res);
        });

    $finalHandler = $this->createMock(MiddlewareInterface::class);
    $finalHandler->expects($this->once())
        ->method('process')
        ->with($request, $response)
        ->willReturn($finalResponse);

    $this->manager->add($middleware);
    $this->manager->setFinalHandler($finalHandler);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($finalResponse);
});

test('final handler not called if middleware short circuits', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $shortCircuitResponse = $this->createMock(ResponseInterface::class);

    $middleware = $this->createMock(MiddlewareInterface::class);
    $middleware->expects($this->once())
        ->method('process')
        ->willReturn($shortCircuitResponse);

    $finalHandler = $this->createMock(MiddlewareInterface::class);
    $finalHandler->expects($this->never())
        ->method('process');

    $this->manager->add($middleware);
    $this->manager->setFinalHandler($finalHandler);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($shortCircuitResponse);
});

test('final handler with empty middleware stack', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $finalResponse = $this->createMock(ResponseInterface::class);

    $finalHandler = $this->createMock(MiddlewareInterface::class);
    $finalHandler->expects($this->once())
        ->method('process')
        ->with($request, $response)
        ->willReturn($finalResponse);

    $this->manager->setFinalHandler($finalHandler);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($finalResponse);
});

test('final handler receives correct next callback', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $callbackInvoked = false;

    $finalHandler = $this->createMock(MiddlewareInterface::class);
    $finalHandler->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$callbackInvoked) {
            $callbackInvoked = true;
            return $next($req, $res);
        });

    $this->manager->setFinalHandler($finalHandler);

    $result = $this->manager->handle($request, $response);

    expect($callbackInvoked)->toBeTrue();
    expect($result)->toBe($response);
});

test('many middlewares form correct chain', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $callCount = 0;

    $createMiddleware = function () use (&$callCount) {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($req, $res, $next) use (&$callCount) {
                $callCount++;
                return $next($req, $res);
            });
        return $middleware;
    };

    for ($i = 0; $i < 5; $i++) {
        $this->manager->add($createMiddleware());
    }

    $this->manager->handle($request, $response);

    expect($callCount)->toBe(5);
});

test('middleware receives same request through chain', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $requestsReceived = [];

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$requestsReceived) {
            $requestsReceived[] = $req;
            return $next($req, $res);
        });

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$requestsReceived) {
            $requestsReceived[] = $req;
            return $next($req, $res);
        });

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);

    $this->manager->handle($request, $response);

    expect($requestsReceived[0])->toBe($request);
    expect($requestsReceived[1])->toBe($request);
});

test('middleware can modify request for next middleware', function () {
    $request = $this->createMock(RequestInterface::class);
    $modifiedRequest = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $receivedRequests = [];

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use ($modifiedRequest) {
            return $next($modifiedRequest, $res);
        });

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$receivedRequests) {
            $receivedRequests[] = $req;
            return $next($req, $res);
        });

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);

    $this->manager->handle($request, $response);

    expect($receivedRequests[0])->toBe($modifiedRequest);
    expect($receivedRequests[0])->not->toBe($request);
});

test('response is passed through correctly', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $responsesReceived = [];

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$responsesReceived) {
            $responsesReceived[] = $res;
            return $next($req, $res);
        });

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$responsesReceived) {
            $responsesReceived[] = $res;
            return $next($req, $res);
        });

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);

    $this->manager->handle($request, $response);

    expect($responsesReceived[0])->toBe($response);
    expect($responsesReceived[1])->toBe($response);
});

test('middleware can modify response in callback', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $modifiedResponse = $this->createMock(ResponseInterface::class);

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use ($modifiedResponse) {
            $next($req, $res);
            return $modifiedResponse;
        });

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) {
            return $next($req, $res);
        });

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($modifiedResponse);
});

test('final handler can ignore next callback', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $finalResponse = $this->createMock(ResponseInterface::class);

    $finalHandler = $this->createMock(MiddlewareInterface::class);
    $finalHandler->expects($this->once())
        ->method('process')
        ->willReturnCallback(function () use ($finalResponse) {
            return $finalResponse;
        });

    $this->manager->setFinalHandler($finalHandler);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($finalResponse);
});

test('multiple calls with same manager', function () {
    $request1 = $this->createMock(RequestInterface::class);
    $response1 = $this->createMock(ResponseInterface::class);

    $request2 = $this->createMock(RequestInterface::class);
    $response2 = $this->createMock(ResponseInterface::class);

    $middleware = $this->createMock(MiddlewareInterface::class);
    $middleware->expects($this->exactly(2))
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) {
            return $next($req, $res);
        });

    $this->manager->add($middleware);

    $result1 = $this->manager->handle($request1, $response1);
    $result2 = $this->manager->handle($request2, $response2);

    expect($result1)->toBe($response1);
    expect($result2)->toBe($response2);
});

test('next callback terminates early', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $earlyResponse = $this->createMock(ResponseInterface::class);

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturn($earlyResponse);

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->never())
        ->method('process');

    $middleware3 = $this->createMock(MiddlewareInterface::class);
    $middleware3->expects($this->never())
        ->method('process');

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);
    $this->manager->add($middleware3);

    $result = $this->manager->handle($request, $response);

    expect($result)->toBe($earlyResponse);
});

test('complex scenario with middleware stack and final handler', function () {
    $request = $this->createMock(RequestInterface::class);
    $response = $this->createMock(ResponseInterface::class);
    $finalResponse = $this->createMock(ResponseInterface::class);
    $executionLog = [];

    $middleware1 = $this->createMock(MiddlewareInterface::class);
    $middleware1->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$executionLog) {
            $executionLog[] = 'M1-before';
            $result = $next($req, $res);
            $executionLog[] = 'M1-after';
            return $result;
        });

    $middleware2 = $this->createMock(MiddlewareInterface::class);
    $middleware2->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$executionLog) {
            $executionLog[] = 'M2-before';
            $result = $next($req, $res);
            $executionLog[] = 'M2-after';
            return $result;
        });

    $finalHandler = $this->createMock(MiddlewareInterface::class);
    $finalHandler->expects($this->once())
        ->method('process')
        ->willReturnCallback(function ($req, $res, $next) use (&$executionLog, $finalResponse) {
            $executionLog[] = 'FH-process';
            return $finalResponse;
        });

    $this->manager->add($middleware1);
    $this->manager->add($middleware2);
    $this->manager->setFinalHandler($finalHandler);

    $result = $this->manager->handle($request, $response);

    expect($executionLog)->toBe(['M1-before', 'M2-before', 'FH-process', 'M2-after', 'M1-after']);
    expect($result)->toBe($finalResponse);
});
