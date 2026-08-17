<?php

declare(strict_types=1);

use STDW\Contract\Http\MiddlewareInterface;
use STDW\Contract\Http\RequestInterface;
use STDW\Contract\Http\ResponseInterface;
use STDW\Http\Handler\RequestHandler;
use STDW\Http\Middleware\MiddlewareManager;
use STDW\Http\Request;
use STDW\Http\Response;

it('delegates to the middleware manager and returns the final response', function () {
    $request = new Request();
    $response = new Response();

    $manager = new MiddlewareManager();
    $handler = new RequestHandler($manager);

    $manager->add(new class implements MiddlewareInterface {
        public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
        {
            return $next($request, $response->withHeader('X-Flow', 'middleware'));
        }
    });

    $manager->setFinalHandler(new class implements MiddlewareInterface {
        public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
        {
            return $response
                ->withStatus(201)
                ->withHeader('X-Handler', 'final')
                ->withBody('final-response');
        }
    });

    $result = $handler->handle($request, $response);

    expect($result)->toBeInstanceOf(Response::class)
        ->and($result->getStatus())->toBe(201)
        ->and($result->getHeader('X-Flow'))->toBe('middleware')
        ->and($result->getHeader('X-Handler'))->toBe('final')
        ->and($result->getBody())->toBe('final-response');
});

it('executes the full request-response flow like an application kernel', function () {
    $request = (new Request())
        ->withAttribute('step', 'begin');
    $response = new Response();

    $pipeline = [];
    $manager = new MiddlewareManager();
    $handler = new RequestHandler($manager);

    $manager->add(new class($pipeline) implements MiddlewareInterface {
        public function __construct(private array &$pipeline) {}

        public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
        {
            $this->pipeline[] = 'middleware-1-before';
            $request = $request->withAttribute('step', 'middleware-1');
            $response = $response->withHeader('X-Step', '1');

            $result = $next($request, $response);

            $this->pipeline[] = 'middleware-1-after';

            return $result->withHeader('X-Chain', 'completed');
        }
    });

    $manager->add(new class($pipeline) implements MiddlewareInterface {
        public function __construct(private array &$pipeline) {}

        public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
        {
            $this->pipeline[] = 'middleware-2-before';
            $response = $response->withHeader('X-Step', $request->getAttribute('step'));

            $result = $next($request, $response);

            $this->pipeline[] = 'middleware-2-after';

            return $result;
        }
    });

    $manager->setFinalHandler(new class($pipeline) implements MiddlewareInterface {
        public function __construct(private array &$pipeline) {}

        public function process(RequestInterface $request, ResponseInterface $response, Closure $next): ResponseInterface
        {
            $this->pipeline[] = 'final-handler';

            return $response
                ->withStatus(202)
                ->withHeader('X-Final', $request->getAttribute('step'))
                ->withBody('kernel-result');
        }
    });

    $result = $handler->handle($request, $response);

    expect($result->getStatus())->toBe(202)
        ->and($result->getHeader('X-Step'))->toBe('middleware-1')
        ->and($result->getHeader('X-Final'))->toBe('middleware-1')
        ->and($result->getBody())->toBe('kernel-result')
        ->and($pipeline)->toBe([
            'middleware-1-before',
            'middleware-2-before',
            'final-handler',
            'middleware-2-after',
            'middleware-1-after',
        ]);
});
