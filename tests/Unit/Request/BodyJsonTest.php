<?php // BodyJsonTest

use STDW\Http\Request;

class TestJsonRequest extends Request
{
    public function __construct(
        protected string $json = '')
    { $this->json = $json; }

    #[Override]
    protected function doGetPhpInputContent(): string
    {
        return $this->json;
    }
}


it('parses JSON body', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('{"name":"Sidney", "role":"admin"}');
    $body = $request->getBody();

    expect($body)->toHaveKeys(['name', 'role']);
    expect($body['name'])->toBe('Sidney');
    expect($request->input('name'))->toBe('Sidney');
    expect($body['role'])->toBe('admin');
    expect($request->input('role'))->toBe('admin');
});

it('handles invalid JSON', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('{invalid json');
    $body = $request->getBody();

    expect($body)->toBe([]);
});

it('returns empty array when JSON is not array', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('"just a string"');
    $body = $request->getBody();

    expect($body)->toBe([]);
});

it('returns empty array when JSON is a number', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('42');
    $body = $request->getBody();

    expect($body)->toBe([]);
});

it('returns empty array when JSON is a boolean', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('true');
    $body = $request->getBody();

    expect($body)->toBe([]);
});

it('returns empty array when JSON is null', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('null');
    $body = $request->getBody();

    expect($body)->toBe([]);
});

it('secures JSON body keys and values', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('{"<key>": "<value>"}');
    $body = $request->getBody();

    expect($body)->toHaveKey('&lt;key&gt;');
    expect($body['&lt;key&gt;'])->toBe('&lt;value&gt;');
});
