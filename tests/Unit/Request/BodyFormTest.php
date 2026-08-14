<?php // BodyFormTest

use STDW\Http\Request;

it('parses and secures form-data', function () {
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    $_POST = ['name' => ' Sidney ', 'role' => '<admin>'];

    $request = new Request();
    $body = $request->getBody();

    expect($body)->toHaveKeys(['name', 'role']);
    expect($body['name'])->toBe('Sidney');
    expect($body['role'])->toBe('&lt;admin&gt;');
    expect($request->input('name'))->toBe('Sidney');
    expect($request->input('role'))->toBe('&lt;admin&gt;');
});
