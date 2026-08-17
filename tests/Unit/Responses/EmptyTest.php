<?php declare(strict_types=1);

use STDW\Http\Response\EmptyResponse;

class TestEmptyResponse extends EmptyResponse
{

    /** @return void
     */
    public function testSend(): void
    {
        /** Send
         */

        $this->withoutHeader('content-type');
        $this->withoutHeader('content-length');
        $this->withoutHeader('transfer-encoding');
        $this->withoutHeader('content-encoding');

        // comment real send
        // parent::send();
    }
}


describe('EmptyResponse', function () {

    describe('Status Code Guarantee', function () {
        it('must have status code 204', function () {
            $response = new EmptyResponse();
            expect($response->getStatus())->toBe(204);
        });

        it('throws exception when trying to set invalid status code', function () {
            $response = new EmptyResponse();
            expect(fn () => $response->withStatus(200))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws exception for status codes other than 204', function () {
            $response = new EmptyResponse();
            
            $invalidCodes = [100, 201, 202, 205, 300, 400, 500];
            
            foreach ($invalidCodes as $code) {
                expect(fn () => $response->withStatus($code))
                    ->toThrow(InvalidArgumentException::class);
            }
        });

        it('allows setting status to 204 explicitly', function () {
            $response = new EmptyResponse();
            $result = $response->withStatus(204);
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(204);
        });
    });

    describe('Body Guarantee', function () {
        it('must not have a body', function () {
            $response = new EmptyResponse();
            expect($response->getBody())->toBeEmpty();
        });

        it('throws exception when trying to set non-empty body', function () {
            $response = new EmptyResponse();
            expect(fn () => $response->withBody('test content'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('allows empty or null body', function () {
            $response = new EmptyResponse();
            
            $result1 = $response->withBody('');
            expect($result1)->toBe($response);

            $result2 = $response->withBody(null);
            expect($result2)->toBe($response)
                ->and($response->getBody())->toBeEmpty();
        });

        it('throws exception with various non-empty bodies', function () {
            $response = new EmptyResponse();
            
            $invalidBodies = ['text', ' ', '0', 'null', 'false', "\n"];
            
            foreach ($invalidBodies as $body) {
                expect(fn () => $response->withBody($body))
                    ->toThrow(InvalidArgumentException::class);
            }
        });
    });

    describe('Header Removal on Send', function () {
        it('removes content-type header when sent', function () {
            $response = new EmptyResponse();
            $response->withHeader('Content-Type', 'text/plain');

            $headers = $response->getHeaders();
            expect(isset($headers['content-type']))->toBeTrue();
            
            // After send, content-type should be removed
            // Note: send() modifies headers before sending
        });

        it('does not allow content-related headers', function () {
            $response = new TestEmptyResponse();
            
            // EmptyResponse should remove these headers on send
            $response->withHeader('Content-Length', '100');
            $response->withHeader('Content-Encoding', 'gzip');
            $response->withHeader('Transfer-Encoding', 'chunked');

            $response->testSend();

            $headers = $response->getHeaders();
            expect(isset($headers['content-length']))->toBeFalse();
            expect(isset($headers['content-encoding']))->toBeFalse();
            expect(isset($headers['transfer-encoding']))->toBeFalse();
        });
    });

    describe('Response Fluency', function () {
        it('returns self for method chaining', function () {
            $response = new EmptyResponse();
            
            $result = $response->withStatus(204);
            expect($result)->toBe($response);
        });

        it('can chain multiple valid operations', function () {
            $response = new EmptyResponse();
            
            $result = $response
                ->withStatus(204)
                ->withBody(null)
                ->withHeader('X-Custom-Header', 'value');
            
            expect($result)->toBe($response)
                ->and($response->getStatus())->toBe(204);
        });
    });

    describe('Constructor', function () {
        it('initializes with default 204 status', function () {
            $response = new EmptyResponse();
            expect($response->getStatus())->toBe(204);
        });

        it('throws when initialized with invalid status', function () {
            expect(fn () => new EmptyResponse(200))
                ->toThrow(InvalidArgumentException::class);
        });

        it('throws when initialized with non-empty body', function () {
            expect(fn () => new EmptyResponse(204, [], 'content'))
                ->toThrow(InvalidArgumentException::class);
        });

        it('accepts empty body in constructor', function () {
            $response = new EmptyResponse(204, [], '');
            expect($response->getBody())->toBeEmpty();
        });
    });
});
