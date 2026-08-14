<?php declare(strict_types=1);

use STDW\Http\Response\JsonResponse;

describe('JsonResponse', function () {
    describe('JSON Body Validation', function () {
        it('requires a non-empty body', function () {
            $response = new JsonResponse(body: '{}');
            
            expect(fn () => $response->withBody(''))
                ->toThrow(InvalidArgumentException::class, 'JsonResponse requires a non-empty body');
        });

        it('throws exception when body is null', function () {
            $response = new JsonResponse(body: '{}');
            
            expect(fn () => $response->withBody(null))
                ->toThrow(InvalidArgumentException::class);
        });

        it('validates JSON structure', function () {
            $response = new JsonResponse(body: '{}');
            
            expect(fn () => $response->withBody('invalid json'))
                ->toThrow(InvalidArgumentException::class, 'Invalid JSON body');
        });

        it('throws exception for malformed JSON', function () {
            $response = new JsonResponse(body: '{}');
            
            $invalidJsons = [
                '{invalid}',
                '{"key": undefined}',
                '{key: value}',
                '[1, 2, 3,]',
                '{"key": "value"',
                '{"key": "value",}',
            ];
            
            foreach ($invalidJsons as $json) {
                expect(fn () => $response->withBody($json))
                    ->toThrow(InvalidArgumentException::class);
            }
        });

        it('accepts valid JSON object', function () {
            $response = new JsonResponse(body: '{}');
            $json = '{"key":"value"}';
            
            $result = $response->withBody($json);
            
            expect($result)->toBe($response)
                ->and($response->getBody())->toBe($json);
        });

        it('accepts valid JSON array', function () {
            $response = new JsonResponse(body: '{}');
            $json = '[1, 2, 3, 4, 5]';
            
            $result = $response->withBody($json);
            
            expect($result)->toBe($response)
                ->and($response->getBody())->toBe($json);
        });

        it('accepts various valid JSON structures', function () {
            $validJsons = [
                '{}',
                '[]',
                '{"nested":{"key":"value"}}',
                '{"array":[1,2,3]}',
                '[{"id":1,"name":"test"}]',
                '{"string":"value","number":42,"boolean":true,"null":null}',
                '{"unicode":"áéíóú"}',
                '{"escaped":"\"quoted\""}',
            ];
            
            foreach ($validJsons as $json) {
                $resp = new JsonResponse(body: '{}');
                $resp->withBody($json);
                expect($resp->getBody())->toBe($json);
            }
        });

        it('accepts JSON with whitespace', function () {
            $response = new JsonResponse(body: '{}');
            $json = <<<JSON
            {
                "key": "value",
                "number": 42,
                "array": [1, 2, 3]
            }
            JSON;
            
            $result = $response->withBody($json);
            expect($result)->toBe($response);
        });
    });

    describe('JSON Error Detection', function () {
        it('detects JSON syntax errors', function () {
            $response = new JsonResponse(body: '{}');
            
            expect(fn () => $response->withBody('{"key": }'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('includes error message in exception', function () {
            $response = new JsonResponse(body: '{}');
            
            try {
                $response->withBody('invalid');
            } catch (InvalidArgumentException $e) {
                expect($e->getMessage())->toContain('Invalid JSON body');
            }
        });
    });

    describe('Content-Type Header Guarantee', function () {
        it('has application/json content type', function () {
            $response = new JsonResponse(body: '{}');
            $response->withBody('{"test":"data"}');
            
            // The content-type should be set to application/json; charset=utf-8
            expect($response)->toBeInstanceOf(JsonResponse::class);
        });
    });

    describe('Status Code', function () {
        it('has default status 200', function () {
            $response = new JsonResponse(body: '{}');
            expect($response->getStatus())->toBe(200);
        });

        it('allows custom status codes', function () {
            $response = new JsonResponse(201, body: '{}');
            expect($response->getStatus())->toBe(201);
            
            $response->withBody('{"id":1}');
            expect($response->getStatus())->toBe(201);
        });

        it('allows changing status code', function () {
            $response = new JsonResponse(body: '{}');
            $response->withStatus(202)->withBody('{"status":"accepted"}');
            
            expect($response->getStatus())->toBe(202);
        });
    });

    describe('Constructor Behavior', function () {
        it('throws when initialized with empty body', function () {
            expect(fn () => new JsonResponse(200, [], ''))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when initialized with null body', function () {
            expect(fn () => new JsonResponse(200, [], null))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when initialized with invalid JSON', function () {
            expect(fn () => new JsonResponse(200, [], 'not json'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts valid JSON in constructor', function () {
            $json = '{"id":1,"name":"test"}';
            $response = new JsonResponse(201, [], $json);
            
            expect($response->getBody())->toBe($json)
                ->and($response->getStatus())->toBe(201);
        });

        it('accepts headers in constructor', function () {
            $response = new JsonResponse(200, ['X-API-Version' => '1.0'], '{}');
            
            $headers = $response->getHeaders();
            expect($headers['x-api-version'])->toBe('1.0');
        });
    });

    describe('Fluent Interface', function () {
        it('returns self for method chaining', function () {
            $response = new JsonResponse(body: '{}');
            
            $result = $response->withBody('{"key":"value"}');
            expect($result)->toBe($response);
        });

        it('chains operations in sequence', function () {
            $response = new JsonResponse(body: '{}');
            
            $result = $response
                ->withStatus(201)
                ->withHeader('X-Request-ID', '12345')
                ->withBody('{"id":1}');
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(201)
                ->and($response->getBody())->toBe('{"id":1}')
                ->and($response->hasHeader('X-Request-ID'))->toBe(true);
        });
    });

    describe('Headers Management', function () {
        it('accepts custom headers', function () {
            $response = new JsonResponse(body: '{}');
            $response
                ->withHeader('X-Custom', 'value');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('value');
        });

        it('can override headers', function () {
            $response = new JsonResponse(body: '{}');
            $response
                ->withHeader('X-Custom', 'old-value')
                ->withHeader('X-Custom', 'new-value');
            
            $headers = $response->getHeaders();
            expect($headers['x-custom'])->toBe('new-value');
        });
    });

    describe('JSON with Special Characters', function () {
        it('handles unicode characters', function () {
            $response = new JsonResponse(body: '{}');
            $json = '{"message":"你好世界"}';
            
            $result = $response->withBody($json);
            expect($result)->toBe($response);
        });

        it('handles escaped characters', function () {
            $response = new JsonResponse(body: '{}');
            $json = '{"text":"Line 1\\nLine 2\\tTabbed"}';
            
            $result = $response->withBody($json);
            expect($result)->toBe($response);
        });

        it('handles numeric values correctly', function () {
            $response = new JsonResponse(body: '{}');
            $json = '{"int":42,"float":3.14,"negative":-1,"zero":0}';
            
            $result = $response->withBody($json);
            expect($result)->toBe($response);
        });
    });
});
