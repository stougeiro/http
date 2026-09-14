<?php

use STDW\Http\Request;
use STDW\Http\Response;
use STDW\Http\Handler\RequestHandler;
use STDW\Http\Middleware\MiddlewareManager;
use STDW\Contract\Http\MiddlewareInterface;

class AddHeaderMiddleware implements MiddlewareInterface
{
    public function __construct(private string $name, private string $value) {}

    public function handle(\STDW\Contract\Http\RequestInterface $request, \STDW\Contract\Http\ResponseInterface $response, callable $next): \STDW\Contract\Http\ResponseInterface
    {
        $response->withHeader($this->name, $this->value);
        return $next($request, $response);
    }
}

class StopMiddleware implements MiddlewareInterface
{
    public function handle(\STDW\Contract\Http\RequestInterface $request, \STDW\Contract\Http\ResponseInterface $response, callable $next): \STDW\Contract\Http\ResponseInterface
    {
        $response->withBody('stopped');
        $response->withStatus(403);
        return $response;
    }
}

class AppendBodyMiddleware implements MiddlewareInterface
{
    public function __construct(private string $text) {}

    public function handle(\STDW\Contract\Http\RequestInterface $request, \STDW\Contract\Http\ResponseInterface $response, callable $next): \STDW\Contract\Http\ResponseInterface
    {
        $body = $response->getBody() ?? '';
        $response->withBody($body . $this->text);
        return $next($request, $response);
    }
}

describe('Feature: Middleware Pipeline Integration', function () {
    it('processes request through full middleware pipeline', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = [];

        $manager = new MiddlewareManager();
        $manager->add(new AddHeaderMiddleware('X-Processed', 'true'));
        $manager->add(new AppendBodyMiddleware('Hello'));

        $handler = new RequestHandler($manager);

        $request = new Request();
        $response = new Response(200, [], '');

        $result = $handler->handle($request, $response);

        expect($result->hasHeader('X-Processed'))->toBeTrue();
        expect($result->getHeader('X-Processed'))->toBe('true');
    });

    it('middleware can short-circuit the pipeline', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = [];

        $manager = new MiddlewareManager();
        $manager->add(new StopMiddleware());
        $manager->add(new AddHeaderMiddleware('X-Should-Not-Exist', 'true'));

        $handler = new RequestHandler($manager);

        $request = new Request();
        $response = new Response(200, [], '');

        $result = $handler->handle($request, $response);

        expect($result->getStatus())->toBe(403);
        expect($result->getBody())->toBe('stopped');
        expect($result->hasHeader('X-Should-Not-Exist'))->toBeFalse();
    });

    it('multiple middlewares execute in order', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = [];

        $manager = new MiddlewareManager();
        $manager->add(new AppendBodyMiddleware('A'));
        $manager->add(new AppendBodyMiddleware('B'));
        $manager->add(new AppendBodyMiddleware('C'));

        $handler = new RequestHandler($manager);

        $request = new Request();
        $response = new Response(200, [], '');

        $result = $handler->handle($request, $response);

        expect($result->getBody())->toBe('ABC');
    });

    it('final handler is called when pipeline is exhausted', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = [];

        $finalHandler = new AppendBodyMiddleware('-FINAL');

        $manager = new MiddlewareManager();
        $manager->add(new AppendBodyMiddleware('A'));
        $manager->setFinalHandler($finalHandler);

        $handler = new RequestHandler($manager);

        $request = new Request();
        $response = new Response(200, [], '');

        $result = $handler->handle($request, $response);

        expect($result->getBody())->toBe('A-FINAL');
    });

    it('request attributes persist across middleware', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = [];

        $manager = new MiddlewareManager();
        $manager->add(new class implements MiddlewareInterface {
            public function handle(\STDW\Contract\Http\RequestInterface $request, \STDW\Contract\Http\ResponseInterface $response, callable $next): \STDW\Contract\Http\ResponseInterface
            {
                $request->withAttribute('user', 'john');
                return $next($request, $response);
            }
        });
        $manager->add(new class implements MiddlewareInterface {
            public function handle(\STDW\Contract\Http\RequestInterface $request, \STDW\Contract\Http\ResponseInterface $response, callable $next): \STDW\Contract\Http\ResponseInterface
            {
                $user = $request->getAttribute('user');
                $response->withBody("User: {$user}");
                return $next($request, $response);
            }
        });

        $handler = new RequestHandler($manager);

        $request = new Request();
        $response = new Response(200, [], '');

        $result = $handler->handle($request, $response);

        expect($result->getBody())->toBe('User: john');
    });

    it('response headers accumulate through pipeline', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = [];

        $manager = new MiddlewareManager();
        $manager->add(new AddHeaderMiddleware('X-First', '1'));
        $manager->add(new AddHeaderMiddleware('X-Second', '2'));
        $manager->add(new AddHeaderMiddleware('X-Third', '3'));

        $handler = new RequestHandler($manager);

        $request = new Request();
        $response = new Response(200);

        $result = $handler->handle($request, $response);

        expect($result->getHeader('X-First'))->toBe('1');
        expect($result->getHeader('X-Second'))->toBe('2');
        expect($result->getHeader('X-Third'))->toBe('3');
    });

    it('empty pipeline returns original response', function () {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = [];

        $manager = new MiddlewareManager();

        $handler = new RequestHandler($manager);

        $request = new Request();
        $response = new Response(200, [], 'original');

        $result = $handler->handle($request, $response);

        expect($result->getBody())->toBe('original');
        expect($result->getStatus())->toBe(200);
    });
});
