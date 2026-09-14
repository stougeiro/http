<?php // UploadedFilesTest

use STDW\Contract\Http\UploadedFileInterface;
use STDW\Http\Request;

it('parses uploaded files correctly', function () {
    $_FILES = [
        'avatar' => [
            'name' => 'me.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/php123',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ],
    ];

    $request = new Request();
    $files = $request->getUploadedFiles();

    expect(count($files))->toBe(1);
    expect($files)->toHaveKey('avatar');

    $file = $files['avatar'];

    expect($file->getName())->toBe('me.png');
    expect($file->gettype())->toBe('image/png');
    expect($file->getSize())->toBe(12345);

    expect($request->getUploadedFile('avatar'))->toBeInstanceOf(UploadedFileInterface::class);

    $file = $request->getUploadedFile('avatar');

    expect($file->getName())->toBe('me.png');
});

it('parses uploaded files correctly (multiple)', function () {
    $_FILES = [
        'avatar' => [
            'name' => [
                'me.png',
                'you.png',
            ],
            'type' => [
                'image/png',
                'image/png',
            ],
            'tmp_name' => [
                '/tmp/php123',
                '/tmp/php124',
            ],
            'error' => [
                UPLOAD_ERR_OK,
                UPLOAD_ERR_OK,
            ],
            'size' => [
                12345,
                54321,
            ],
        ],
    ];

    $request = new Request();
    $files = $request->getUploadedFiles();

    expect(count($files))->toBe(1);
    expect($files)->toHaveKey('avatar');

    $avatar = $files['avatar'];

    expect(count($avatar))->toBe(2);

    $file = $avatar[0];

    expect($file->getName())->toBe('me.png');
    expect($file->gettype())->toBe('image/png');
    expect($file->getSize())->toBe(12345);

    $file = $avatar[1];

    expect($file->getName())->toBe('you.png');
    expect($file->getTmpName())->toBe('/tmp/php124');
    expect($file->getSize())->toBe(54321);
});

it('returns null for missing file', function () {
    $_FILES = [];

    $request = new Request();
    $files = $request->getUploadedFiles();

    expect(count($files))->toBe(0);
    expect($files)->toBe([]);

    expect($request->getUploadedFile('missing'))->toBeNull();
});

it('skips non-array entries in FILES', function () {
    $_FILES = [
        'valid' => [
            'name' => 'ok.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php123',
            'error' => UPLOAD_ERR_OK,
            'size' => 100,
        ],
        'invalid' => 'not-an-array',
    ];

    $request = new Request();
    $files = $request->getUploadedFiles();

    expect($files)->toHaveKey('valid');
    expect($files)->not->toHaveKey('invalid');
});

it('skips file array with wrong types in multiple upload', function () {
    $_FILES = [
        'docs' => [
            'name' => [123, 'valid.pdf'],
            'type' => ['text/plain', 'application/pdf'],
            'tmp_name' => ['/tmp/a', '/tmp/b'],
            'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
            'size' => [100, 200],
        ],
    ];

    $request = new Request();
    $files = $request->getUploadedFiles();

    expect(count($files['docs']))->toBe(1);
    expect($files['docs'][0]->getName())->toBe('valid.pdf');
});

it('skips single file with wrong field types', function () {
    $_FILES = [
        'bad' => [
            'name' => 123,
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php123',
            'error' => UPLOAD_ERR_OK,
            'size' => 100,
        ],
    ];

    $request = new Request();
    $files = $request->getUploadedFiles();

    expect($files)->not->toHaveKey('bad');
});

it('returns empty array when FILES is empty', function () {
    $_FILES = [];

    $request = new Request();

    expect($request->getUploadedFiles())->toBe([]);
});
