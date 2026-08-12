<?php // ParamsTest

use STDW\Http\Request;

it('parses and secures GET params', function () {
    $_GET = ['name' => ' Sidney ', 'age' => '30'];

    $request = new Request();
    $params = $request->getParams();

    expect($params['name'])->toBe('Sidney');
    expect($params['age'])->toBe('30');

    expect($request->param('name'))->toBe('Sidney');
    expect($request->param('age'))->toBe('30');
});

it('returns default when param is missing', function () {
    $_GET = [];

    $request = new Request();
    $params = $request->getParams();

    expect(isset($params['user']))->toBeFalse();
    expect($request->param('user', 'guest'))->toBe('guest');
});

