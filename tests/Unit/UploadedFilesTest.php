<?php // UploadedFilesTest

use STDW\Http\UploadedFile;
use STDW\Contract\Http\UploadedFileInterface;

class FakeUploadedFile extends UploadedFile
{
    public bool $isDir;
    public bool $shouldMove;
    public bool $shouldRename;

    protected function doIsdir(string $filename): bool
    { return $this->isDir; }

    protected function doMoveUploadedFile(string $from, string $to): bool
    { return $this->shouldMove; }

    protected function doRename(string $from, string $to, $context = null): bool
    { return $this->shouldRename; }
}


it('constructs correctly', function () {
    $file = new UploadedFile(
        'me.png',
        'image/png',
        '/tmp/php123',
        12345,
        UPLOAD_ERR_OK
    );

    expect($file)->toBeInstanceOf(UploadedFileInterface::class);
    expect($file->getName())->toBe('me.png');
    expect($file->getType())->toBe('image/png');
    expect($file->getTmpName())->toBe('/tmp/php123');
    expect($file->getSize())->toBe(12345);
    expect($file->getError())->toBe(UPLOAD_ERR_OK);
});

it('creates from array', function () {
    $data = [
        'name' => 'me.png',
        'type' => 'image/png',
        'tmp_name' => '/tmp/php123',
        'size' => 12345,
        'error' => UPLOAD_ERR_OK,
    ];

    $file = UploadedFile::fromArray($data);

    expect($file->getName())->toBe('me.png');
    expect($file->getType())->toBe('image/png');
    expect($file->getTmpName())->toBe('/tmp/php123');
    expect($file->getSize())->toBe(12345);
    expect($file->getError())->toBe(UPLOAD_ERR_OK);
});

it('returns correct error messages', function () {
    $cases = [
        UPLOAD_ERR_OK => null,
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds MAX_FILE_SIZE.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        999 => 'Unknown upload error.',
    ];

    foreach ($cases as $code => $message) {
        $file = new UploadedFile('x', 'y', '/tmp/a', 1, $code);
        expect($file->getErrorMessage())->toBe($message);
    }
});

it('allows renaming via withName', function () {
    $file = new UploadedFile('me.png', 'image/png', '/tmp/php123', 12345, UPLOAD_ERR_OK);

    $file->withName('new.png');

    expect($file->getName())->toBe('new.png');
});

it('throws when moving file with error', function () {
    $file = new UploadedFile('me.png', 'image/png', '/tmp/php123', 12345, UPLOAD_ERR_NO_FILE);

    expect(fn() => $file->moveTo('/tmp/target'))
        ->toThrow(RuntimeException::class, 'No file was uploaded.');
});

it('moves file using move_uploaded_file', function () {
    $file = new FakeUploadedFile('me.png', 'image/png', '/tmp/php123', 12345, UPLOAD_ERR_OK);
    $file->isDir = true;
    $file->shouldMove = true;
    $file->shouldRename = true;

    $file->moveTo('/tmp/target');

    expect($file->getTmpName())->toBeNull();
    expect($file->getPath())->toBe('/tmp/target/me.png');
});

it('moves file using rename', function () {
    $file = new FakeUploadedFile('me.png', 'image/png', '/tmp/rf876', 12345, UPLOAD_ERR_OK);
    $file->isDir = true;
    $file->shouldMove = true;
    $file->shouldRename = true;

    $file->withName('new.png');
    $file->moveTo('/new/local');

    expect($file->getTmpName())->toBeNull();
    expect($file->getName())->toBe('new.png');
    expect($file->getPath())->toBe('/new/local/new.png');

    $file->withName('other.png');
    $file->moveTo('/other/local');

    expect($file->getTmpName())->toBeNull();
    expect($file->getName())->toBe('other.png');
    expect($file->getPath())->toBe('/other/local/other.png');
});

it('throws when move_uploaded_file fails', function () {
    $file = new FakeUploadedFile('me.png', 'image/png', '/tmp/php123', 12345, UPLOAD_ERR_OK);
    $file->isDir = true;
    $file->shouldMove = false;
    $file->shouldRename = false;

    expect(fn() => $file->moveTo('/tmp/target'))
        ->toThrow(RuntimeException::class, 'Failed to move uploaded file.');
});

it('rename file', function () {
    $file = new FakeUploadedFile('me.png', 'image/png', '/tmp/rf876', 12345, UPLOAD_ERR_OK);
    $file->shouldMove = true;
    $file->shouldRename = true;

    $file->isDir = true;
    $file
        ->withName('new.png')
        ->moveTo('/new/local');

    expect($file->getName())->toBe('new.png');
    expect($file->getPath())->toBe('/new/local/new.png');

    $file->isDir = false;
    $file
        ->withName('other.png')
        ->moveTo('/other/local/wonderful.png');

    expect($file->getName())->toBe('wonderful.png');
    expect($file->getPath())->toBe('/other/local/wonderful.png');
});