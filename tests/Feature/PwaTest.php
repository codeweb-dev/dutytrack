<?php

it('serves a public standalone manifest with real branded icons', function () {
    $manifest = $this->get('/manifest.webmanifest')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json')
        ->assertJsonPath('name', 'DutyTrack')
        ->assertJsonPath('display', 'standalone')
        ->assertJsonPath('scope', '/')
        ->assertJsonPath('start_url', '/dashboard')->json();

    foreach ($manifest['icons'] as $icon) {
        [$width, $height] = getimagesize(public_path(ltrim($icon['src'], '/')));
        expect("{$width}x{$height}")->toBe($icon['sizes']);
    }
});

it('serves a non-cacheable worker with only public assets', function () {
    $worker = $this->get('/sw.js')->assertOk()
        ->assertHeader('Content-Type', 'application/javascript')
        ->assertHeader('Service-Worker-Allowed', '/')->getContent();
    preg_match('/const CONFIG = (.*);/', $worker, $matches);
    $config = json_decode($matches[1], true);
    expect($config['offline'])->toBe('/pwa/offline.html');
    foreach ($config['assets'] as $asset) {
        expect(str_starts_with($asset, '/pwa/') || str_starts_with($asset, '/build/assets/'))->toBeTrue();
    }
});

it('preserves subdirectory deployment paths', function () {
    $request = Illuminate\Http\Request::create('https://example.test/duty/manifest.webmanifest');
    $request->server->set('SCRIPT_NAME', '/duty/index.php');
    $request->server->set('SCRIPT_FILENAME', '/var/www/public/index.php');
    $manifest = (new App\Http\Controllers\PwaController)->manifest($request)->getData(true);
    expect($manifest['scope'])->toBe('/duty/')
        ->and($manifest['start_url'])->toBe('/duty/dashboard')
        ->and($manifest['icons'][0]['src'])->toBe('/duty/pwa/icon-192.png');
});
