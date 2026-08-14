<?php // SecurityTest

use STDW\Http\Request;

class TestSecurityRequest extends Request
{
    public function testSecureArray(array $data): array
    {
        return $this->secureArray($data);
    }

    public function testSecureString(string $value): string
    {
        return $this->secureString($value);
    }

    public function testSecureFilename(string $name): string
    {
        return $this->secureFilename($name);
    }
}

$request = new TestSecurityRequest;


it('removes control characters', function () use ($request) {
    $input = "Hello\x00World\x1F!";
    $expected = "HelloWorld!";

    expect($request->testSecureString($input))
        ->toBe(htmlspecialchars($expected, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
});

it('removes zero-width characters', function () use ($request) {
    $input = "A\xE2\x80\x8B B"; // ZERO WIDTH SPACE
    $expected = "A B";

    expect($request->testSecureString($input))
        ->toBe(htmlspecialchars($expected, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
});

it('removes directional formatting characters', function () use ($request) {
    $input = "ABC\xE2\x80\xAADEF"; // LRE
    $expected = "ABCDEF";

    expect($request->testSecureString($input))
        ->toBe(htmlspecialchars($expected, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
});

it('escapes HTML correctly', function () use ($request) {
    $input = "<script>alert('x')</script>";

    expect($request->testSecureString($input))
        ->toBe("&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;");
});

it('applies trim to the input', function () use ($request) {
    expect($request->testSecureString("  test  "))
        ->toBe("test");
});

it('sanitizes string values', function () use ($request) {
    $input = ['name' => '<b>John</b>'];
    $output = $request->testSecureArray($input);

    expect($output)->toBe([
        'name' => '&lt;b&gt;John&lt;/b&gt;',
    ]);
});

it('sanitizes string keys', function () use ($request) {
    $input = ["<key>" => "value"];
    $output = $request->testSecureArray($input);

    expect($output)->toHaveKey("&lt;key&gt;");
    expect($output["&lt;key&gt;"])->toBe("value");
});

it('sanitizes nested arrays recursively', function () use ($request) {
    $input = [
        "user" => [
            "name" => "<John>",
            "meta" => ["role" => "admin<script>"]
        ]
    ];

    $output = $request->testSecureArray($input);

    expect($output)->toBe([
        "user" => [
            "name" => "&lt;John&gt;",
            "meta" => ["role" => "admin&lt;script&gt;"]
        ]
    ]);
});

it('keeps non-string values unchanged', function () use ($request) {
    $input = [
        "age" => 30,
        "active" => true,
        "items" => [1, 2, 3]
    ];

    expect($request->testSecureArray($input))->toBe($input);
});

it('removes invalid filename characters', function () use ($request) {
    $input = 'te/st:fi*le?.txt';
    $output = $request->testSecureFilename($input);

    expect($output)->toBe('testfile.txt');
});

it('removes dot traversal sequences', function () use ($request) {
    $input = '.././secret.txt';
    $output = $request->testSecureFilename($input);

    expect($output)->toBe('secret.txt');
});

it('trims extra dots and whitespace', function () use ($request) {
    $input = " file. ";
    $output = $request->testSecureFilename($input);

    expect($output)->toBe('file');
});

it('prefixes reserved Windows filenames', function () use ($request) {
    $input = "CON";
    $output = $request->testSecureFilename($input);

    expect($output)->toBe('_CON');
});

it('returns default filename when empty after sanitization', function () use ($request) {
    $input = "   ";
    $output = $request->testSecureFilename($input);

    expect($output)->toBe('file');
});
