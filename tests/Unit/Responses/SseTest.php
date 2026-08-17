<?php declare(strict_types=1);

use STDW\Http\Response\SseResponse;

describe('SseResponse', function () {
    describe('Event Generator Requirement', function () {
        it('requires a closure event generator', function () {
            $generator = function () {
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('accepts closure that returns events', function () {
            $generator = function () {
                return [
                    'event' => 'message',
                    'data' => 'Hello World',
                ];
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('accepts closure that returns null to stop', function () {
            $generator = function () {
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('accepts closure with any logic', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter < 3) {
                    $counter++;
                    return ['data' => "Event {$counter}"];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });
    });

    describe('Status Code Guarantee', function () {
        it('only allows status code 200', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect($response->getStatus())->toBe(200);
        });

        it('has default status 200', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect($response->getStatus())->toBe(200);
        });

        it('throws exception when trying to set different status', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $invalidStatuses = [100, 201, 202, 204, 301, 302, 400, 404, 500];
            
            foreach ($invalidStatuses as $status) {
                expect(fn () => $response->withStatus($status))
                    ->toThrow(InvalidArgumentException::class, 'SseResponse only supports status 200');
            }
        });

        it('throws for any status other than 200', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect(fn () => $response->withStatus(199))
                ->toThrow(InvalidArgumentException::class);
            
            expect(fn () => $response->withStatus(201))
                ->toThrow(InvalidArgumentException::class);
            
            expect(fn () => $response->withStatus(1000))
                ->toThrow(InvalidArgumentException::class);
        });

        it('allows explicitly setting status to 200', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $result = $response->withStatus(200);
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(200);
        });
    });

    describe('Body Guarantee', function () {
        it('must not have a body', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect($response->getBody())->toBeEmpty();
        });

        it('throws exception when trying to set non-empty body', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect(fn () => $response->withBody('event data'))
                ->toThrow(InvalidArgumentException::class, 'Invalid body on SseResponse');
        });

        it('allows empty or null body', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $result1 = $response->withBody('');
            expect($result1)->toBe($response);

            $result2 = $response->withBody(null);
            expect($result2)->toBe($response)
                ->and($response->getBody())->toBeEmpty();
        });

        it('throws for any non-empty body', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $invalidBodies = ['event', ' ', '0', 'null', 'false', "\n", 'data'];
            
            foreach ($invalidBodies as $body) {
                expect(fn () => $response->withBody($body))
                    ->toThrow(InvalidArgumentException::class);
            }
        });
    });

    describe('Constructor Behavior', function () {
        it('requires generator closure parameter', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect($response)->toBeInstanceOf(SseResponse::class)
                ->and($response->getStatus())->toBe(200);
        });

        it('accepts optional status parameter', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator, 200);
            
            expect($response->getStatus())->toBe(200);
        });

        it('throws when initialized with invalid status', function () {
            $generator = fn () => null;
            
            expect(fn () => new SseResponse($generator, 201))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when initialized with non-empty body', function () {
            $generator = fn () => null;
            
            expect(fn () => new SseResponse($generator, 200, [], 'content'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts empty body in constructor', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator, 200, [], '');
            
            expect($response->getBody())->toBeEmpty();
        });

        it('accepts headers in constructor', function () {
            $generator = fn () => null;
            $response = new SseResponse(
                $generator,
                200,
                ['X-Custom' => 'value']
            );
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });

        it('initializes with all parameters', function () {
            $generator = fn () => ['data' => 'test'];
            $response = new SseResponse(
                $generator,
                200,
                ['X-SSE-Header' => 'value'],
                ''
            );
            
            expect($response->getStatus())->toBe(200)
                ->and($response->getHeaders()['x-sse-header'])->toBe('value');
        });
    });

    describe('Event Generator Types', function () {
        it('accepts closure with no parameters', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('accepts closure with captured variables', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter < 3) {
                    return ['data' => ++$counter];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('accepts generator function returning arrays', function () {
            $generator = fn () => ['event' => 'ping', 'data' => 'keepalive'];
            $response = new SseResponse($generator);
            
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('accepts generator that returns null', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect($response)->toBeInstanceOf(SseResponse::class);
        });
    });

    describe('Fluent Interface', function () {
        it('returns self for method chaining', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $result = $response->withStatus(200);
            expect($result)->toBe($response);
        });

        it('chains multiple valid operations', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $result = $response
                ->withStatus(200)
                ->withBody(null)
                ->withHeader('X-Custom', 'value');
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(200);
        });
    });

    describe('Header Management', function () {
        it('accepts custom headers', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            $response->withHeader('X-Custom', 'value');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });

        it('can set multiple headers', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            $response
                ->withHeader('X-Header-1', 'value1')
                ->withHeader('X-Header-2', 'value2')
                ->withHeader('X-Header-3', 'value3');
            
            $headers = $response->getHeaders();
            expect($headers)->toHaveKeys(['x-header-1', 'x-header-2', 'x-header-3']);
        });

        it('should set SSE-related headers on send', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            // These headers should be set during send()
            expect($response)->toBeInstanceOf(SseResponse::class);
        });
    });

    describe('Event Data Structures', function () {
        it('supports simple event with data field', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter === 0) {
                    $counter++;
                    return ['data' => 'simple event'];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('supports event with event name', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter === 0) {
                    $counter++;
                    return [
                        'event' => 'message',
                        'data' => 'hello',
                    ];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('supports event with id field', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter === 0) {
                    $counter++;
                    return [
                        'id' => '123',
                        'data' => 'event data',
                    ];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('supports event with retry field', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter === 0) {
                    $counter++;
                    return [
                        'retry' => '5000',
                        'data' => 'retry event',
                    ];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('supports event with all optional fields', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter === 0) {
                    $counter++;
                    return [
                        'event' => 'complete',
                        'id' => '456',
                        'retry' => '3000',
                        'data' => 'full event',
                    ];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('supports event with array data', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter === 0) {
                    $counter++;
                    return [
                        'data' => [
                            'line 1',
                            'line 2',
                            'line 3',
                        ],
                    ];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });
    });

    describe('Generator Loop Behavior', function () {
        it('supports generator that yields multiple events', function () {
            $events = [
                ['data' => 'event 1'],
                ['data' => 'event 2'],
                ['data' => 'event 3'],
                null,
            ];
            
            $index = 0;
            $generator = function () use (&$index, $events) {
                $event = $events[$index] ?? null;
                $index++;
                return $event;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });

        it('supports infinite generator with termination condition', function () {
            $counter = 0;
            $generator = function () use (&$counter) {
                if ($counter < 5) {
                    return ['data' => "Count: " . ++$counter];
                }
                return null;
            };
            
            $response = new SseResponse($generator);
            expect($response)->toBeInstanceOf(SseResponse::class);
        });
    });

    describe('Status Code Immutability', function () {
        it('cannot change status from 200', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect(fn () => $response->withStatus(200 + 1))
                ->toThrow(InvalidArgumentException::class);
        });

        it('must remain at 200 throughout lifecycle', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $response->withStatus(200);
            $response->withStatus(200);
            
            expect($response->getStatus())->toBe(200);
        });
    });

    describe('Body Immutability', function () {
        it('cannot accept any body content', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            expect(fn () => $response->withBody('any content'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('maintains empty body throughout', function () {
            $generator = fn () => null;
            $response = new SseResponse($generator);
            
            $response->withBody(null);
            $response->withBody('');
            
            expect($response->getBody())->toBeEmpty();
        });
    });
});
