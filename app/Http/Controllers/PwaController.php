<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PwaController extends Controller
{
    public function manifest(Request $request)
    {
        $base = $request->getBaseUrl().'/';

        return response()->json([
            'id' => $base,
            'name' => 'DutyTrack',
            'short_name' => 'DutyTrack',
            'description' => 'Manage OJT placements, attendance, practicum hours, and feedback with DutyTrack.',
            'start_url' => $base.'dashboard',
            'scope' => $base,
            'display' => 'standalone',
            'theme_color' => '#ef4444',
            'background_color' => '#ffffff',
            'lang' => 'en',
            'icons' => collect([192, 512])->map(fn ($size) => [
                'src' => $base."pwa/icon-{$size}.png",
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ]),
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'no-cache']);
    }

    public function worker(Request $request)
    {
        $files = ['pwa/offline.html', 'pwa/icon-192.png', 'pwa/icon-512.png', 'pwa/apple-touch-icon.png'];
        if (is_file(public_path('build/manifest.json'))) {
            foreach (File::allFiles(public_path('build/assets')) as $file) {
                $files[] = 'build/assets/'.$file->getRelativePathname();
            }
        }
        $version = hash('sha256', collect($files)->map(fn ($file) => hash_file('sha256', public_path($file)))->join('').File::get(resource_path('js/service-worker.js')));
        $base = $request->getBaseUrl().'/';
        $config = json_encode([
            'assets' => array_map(fn ($file) => $base.$file, $files),
            'offline' => $base.'pwa/offline.html',
            'prefix' => 'dutytrack-'.hash('sha256', $base).'-',
            'version' => $version,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return response('const CONFIG = '.$config.";\n".File::get(resource_path('js/service-worker.js')), 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Service-Worker-Allowed' => $base,
        ]);
    }
}
