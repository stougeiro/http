<?php declare(strict_types=1);

use STDW\Http\Spec\HeadersTrait;

uses(HeadersTrait::class);

it('normalizes uppercase header names to lowercase', function () {
    expect($this->normalizeHeaderName('CONTENT-TYPE'))->toBe('content-type');
});

it('normalizes mixed-case header names to lowercase', function () {
    expect($this->normalizeHeaderName('Content-Type'))->toBe('content-type');
});

it('keeps already normalized header names unchanged', function () {
    expect($this->normalizeHeaderName('content-type'))->toBe('content-type');
});

it('replaces underscores with hyphens during normalization', function () {
    expect($this->normalizeHeaderName('Content_Type'))->toBe('content-type');
});

it('replaces multiple underscores during normalization', function () {
    expect($this->normalizeHeaderName('X_Custom_Header_Name'))->toBe('x-custom-header-name');
});

it('normalizes mixed underscores and hyphens consistently', function () {
    expect($this->normalizeHeaderName('X_Custom-Header_Name'))->toBe('x-custom-header-name');
});

it('normalizes single-word header names', function () {
    expect($this->normalizeHeaderName('Host'))->toBe('host');
});

it('normalizes header names containing numbers', function () {
    expect($this->normalizeHeaderName('X-API-Version-2'))->toBe('x-api-version-2');
});

it('keeps consecutive hyphens untouched during normalization', function () {
    expect($this->normalizeHeaderName('X--Header--Name'))->toBe('x--header--name');
});

it('keeps consecutive underscores translated to double hyphens', function () {
    expect($this->normalizeHeaderName('X__Header__Name'))->toBe('x--header--name');
});

it('normalizes special forwarded-for header names', function () {
    expect($this->normalizeHeaderName('X_Forwarded_For'))->toBe('x-forwarded-for');
});

it('normalizes authorization header names', function () {
    expect($this->normalizeHeaderName('AUTHORIZATION'))->toBe('authorization');
});

it('normalizes accept-encoding header names', function () {
    expect($this->normalizeHeaderName('Accept-Encoding'))->toBe('accept-encoding');
});

it('canonicalizes a lowercase header name', function () {
    expect($this->canonicalizeHeaderName('content-type'))->toBe('Content-Type');
});

it('keeps an already canonical header name unchanged', function () {
    expect($this->canonicalizeHeaderName('Content-Type'))->toBe('Content-Type');
});

it('canonicalizes single-word header names', function () {
    expect($this->canonicalizeHeaderName('host'))->toBe('Host');
});

it('canonicalizes multiple header parts', function () {
    expect($this->canonicalizeHeaderName('x-forwarded-for'))->toBe('X-Forwarded-For');
});

it('canonicalizes header names that include numbers', function () {
    expect($this->canonicalizeHeaderName('x-api-version-2'))->toBe('X-Api-Version-2');
});

it('canonicalizes authorization header names', function () {
    expect($this->canonicalizeHeaderName('authorization'))->toBe('Authorization');
});

it('canonicalizes accept-encoding header names', function () {
    expect($this->canonicalizeHeaderName('accept-encoding'))->toBe('Accept-Encoding');
});

it('canonicalizes cache-control header names', function () {
    expect($this->canonicalizeHeaderName('cache-control'))->toBe('Cache-Control');
});

it('normalizes and canonicalizes in sequence', function () {
    $header = 'Content_Type';
    $normalized = $this->normalizeHeaderName($header);
    $canonicalized = $this->canonicalizeHeaderName($normalized);

    expect($normalized)->toBe('content-type');
    expect($canonicalized)->toBe('Content-Type');
});

it('normalizes and canonicalizes complex header names consistently', function () {
    $header = 'X_CUSTOM_HEADER_NAME';
    $normalized = $this->normalizeHeaderName($header);
    $canonicalized = $this->canonicalizeHeaderName($normalized);

    expect($normalized)->toBe('x-custom-header-name');
    expect($canonicalized)->toBe('X-Custom-Header-Name');
});

it('is idempotent when normalizing the same header twice', function () {
    $header = 'Content-Type';
    $first = $this->normalizeHeaderName($header);
    $second = $this->normalizeHeaderName($first);

    expect($first)->toBe($second);
});

it('is idempotent when canonicalizing the same header twice', function () {
    $header = 'Content-Type';
    $first = $this->canonicalizeHeaderName($header);
    $second = $this->canonicalizeHeaderName($first);

    expect($first)->toBe($second);
});

it('canonicalizes common HTTP headers as expected', function () {
    $commonHeaders = [
        'content-type' => 'Content-Type',
        'content-length' => 'Content-Length',
        'cache-control' => 'Cache-Control',
        'x-forwarded-for' => 'X-Forwarded-For',
        'authorization' => 'Authorization',
        'accept-language' => 'Accept-Language',
        'user-agent' => 'User-Agent',
        'accept-encoding' => 'Accept-Encoding',
    ];

    foreach ($commonHeaders as $normalized => $canonical) {
        expect($this->normalizeHeaderName($canonical))->toBe($normalized);
        expect($this->canonicalizeHeaderName($normalized))->toBe($canonical);
    }
});

it('keeps normalization consistent across different cases', function () {
    $variations = [
        'Content-Type',
        'CONTENT-TYPE',
        'content-type',
        'cOnTeNt-TyPe',
        'Content_Type',
        'CONTENT_TYPE',
        'content_type',
    ];

    foreach ($variations as $variation) {
        expect($this->normalizeHeaderName($variation))->toBe('content-type');
    }
});

it('keeps canonicalization consistent after normalization', function () {
    $variations = [
        'Content-Type',
        'CONTENT-TYPE',
        'content-type',
        'cOnTeNt-TyPe',
    ];

    $normalized = array_map(fn ($header) => $this->normalizeHeaderName($header), $variations);
    $canonicalized = array_map(fn ($header) => $this->canonicalizeHeaderName($header), $normalized);

    foreach ($canonicalized as $canonical) {
        expect($canonical)->toBe('Content-Type');
    }
});
