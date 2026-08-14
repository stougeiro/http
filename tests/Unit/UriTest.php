<?php

use STDW\Contract\Http\UriInterface;
use STDW\Http\Uri;

it('creates a URI from a full URL and exposes all values', function () {
    $uri = Uri::fromUrl('https://john:secret@example.com:8443/docs/search?foo=bar&baz=qux#section');

    expect($uri)->toBeInstanceOf(UriInterface::class)
        ->and($uri->getScheme())->toBe('https')
        ->and($uri->getHost())->toBe('example.com')
        ->and($uri->getPort())->toBe(8443)
        ->and($uri->getUser())->toBe('john')
        ->and($uri->getPass())->toBe('secret')
        ->and($uri->getAuthority())->toBe('john:secret@example.com:8443')
        ->and($uri->getPath())->toBe('/docs/search')
        ->and($uri->getQuery())->toBe(['foo' => 'bar', 'baz' => 'qux'])
        ->and($uri->getFragment())->toBe('section')
        ->and((string) $uri)->toBe('https://john:secret@example.com:8443/docs/search?foo=bar&baz=qux#section');
});

it('creates a URI from an array and applies sensible defaults', function () {
    $uri = Uri::fromArray([
        'scheme' => 'https',
        'host' => 'example.com',
        'port' => 443,
        'query' => ['page' => '2', 'filter' => 'active'],
        'fragment' => 'top',
    ]);

    expect($uri->getScheme())->toBe('https')
        ->and($uri->getHost())->toBe('example.com')
        ->and($uri->getPort())->toBe(443)
        ->and($uri->getUser())->toBeNull()
        ->and($uri->getPass())->toBeNull()
        ->and($uri->getAuthority())->toBe('example.com:443')
        ->and($uri->getPath())->toBe('/')
        ->and($uri->getQuery())->toBe(['page' => '2', 'filter' => 'active'])
        ->and($uri->getFragment())->toBe('top')
        ->and((string) $uri)->toBe('https://example.com:443/?page=2&filter=active#top');
});

it('serializes without scheme, credentials, port and fragment when they are not present', function () {
    $uri = Uri::fromUrl('https://example.com/path?x=1');

    expect($uri->getScheme())->toBe('https')
        ->and($uri->getUser())->toBeNull()
        ->and($uri->getPass())->toBeNull()
        ->and($uri->getPort())->toBeNull()
        ->and($uri->getAuthority())->toBe('example.com')
        ->and((string) $uri)->toBe('https://example.com/path?x=1');
});

it('uses the default path when the URL has no path and preserves authority formatting', function () {
    $uri = Uri::fromUrl('http://user@localhost:8080?status=ok');

    expect($uri->getPath())->toBe('/')
        ->and($uri->getAuthority())->toBe('user@localhost:8080')
        ->and((string) $uri)->toBe('http://user@localhost:8080/?status=ok');
});

it('parses a router-style path without host and scheme', function () {
    $uri = Uri::fromUrl('/dashboard');

    expect($uri->getScheme())->toBe('')
        ->and($uri->getHost())->toBe('')
        ->and($uri->getPath())->toBe('/dashboard')
        ->and($uri->getQuery())->toBe([])
        ->and((string) $uri)->toBe('/dashboard');
});

it('keeps query string on a router path', function () {
    $uri = Uri::fromUrl('/dashboard?tab=profile&mode=edit');

    expect($uri->getPath())->toBe('/dashboard')
        ->and($uri->getQuery())->toBe(['tab' => 'profile', 'mode' => 'edit'])
        ->and((string) $uri)->toBe('/dashboard?tab=profile&mode=edit');
});

it('keeps fragment on a router path', function () {
    $uri = Uri::fromUrl('/dashboard#settings');

    expect($uri->getPath())->toBe('/dashboard')
        ->and($uri->getFragment())->toBe('settings')
        ->and((string) $uri)->toBe('/dashboard#settings');
});
