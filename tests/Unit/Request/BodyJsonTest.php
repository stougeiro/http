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

    expect($body['name'])->toBe('Sidney');
    expect($request->input('name'))->toBe('Sidney');
    expect($body['role'])->toBe('admin');
    expect($request->input('role'))->toBe('admin');
});

it('handles invalid JSON', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $request = new TestJsonRequest('{invalid json');
    $body = $request->getBody();

    expect($body)->toHaveKey('__ERROR__');
});
