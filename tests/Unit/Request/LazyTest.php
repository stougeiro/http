<?php // LazyTest

use STDW\Http\Request;

it('caches params lazily', function () {
    $_GET = ['name' => 'Sidney'];

    $request = new Request();

    $first = $request->getParams();
    $_GET['name'] = 'Changed';

    $second = $request->getParams();

    expect($first)->toBe($second);
});
