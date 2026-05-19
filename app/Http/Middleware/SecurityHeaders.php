<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    private array $baseCsp = [
        'default-src'               => ["'self'"],
        'script-src'                => ["'self'"],
        'style-src'                 => ["'self'", 'https://fonts.googleapis.com', 'https://cdnjs.cloudflare.com'],
        'img-src'                   => ["'self'", 'data:', 'blob:', 'https:'],
        'font-src'                  => ["'self'", 'data:', 'https://fonts.gstatic.com', 'https://cdnjs.cloudflare.com'],
        'connect-src'               => ["'self'"],
        'media-src'                 => ["'self'", 'blob:'],
        'object-src'                => ["'none'"],
        'frame-src'                 => ["'self'", 'https://www.youtube.com', 'https://player.vimeo.com'],
        'frame-ancestors'           => ["'none'"],
        'base-uri'                  => ["'self'"],
        'form-action'               => ["'self'"],
        'worker-src'                => ["'self'", 'blob:'],
        'upgrade-insecure-requests' => [],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = $this->baseCsp;

        $assetUrl  = config('app.asset_url', config('app.url'));
        $storageUrl = config('app.storage_url', $assetUrl);
        $adminUrl  = config('app.admin_url', '');

        $csp['img-src'][]     = $assetUrl;
        $csp['img-src'][]     = $storageUrl;
        $csp['connect-src'][] = $assetUrl;
        $csp['frame-src'][]   = $assetUrl;

        if ($adminUrl) {
            $csp['img-src'][]     = $adminUrl;
            $csp['connect-src'][] = $adminUrl;
        }

        if (app()->environment('local')) {
            $port = config('vite.dev_server_port', 5174);
            $viteOrigin = "http://127.0.0.1:{$port}";
            $viteWs     = "ws://127.0.0.1:{$port}";

            $csp['script-src'][]  = $viteOrigin;
            $csp['script-src'][]  = "'unsafe-inline'";
            $csp['style-src'][]   = $viteOrigin;
            $csp['style-src'][]   = "'unsafe-inline'";
            $csp['connect-src'][] = $viteOrigin;
            $csp['connect-src'][] = $viteWs;
            $csp['font-src'][]    = $viteOrigin;

            unset($csp['upgrade-insecure-requests']);
        } else {
            $csp['script-src'][] = "'unsafe-inline'";
            $csp['script-src'][] = "'unsafe-eval'";
        }

        $response->headers->set('Content-Security-Policy', $this->buildCsp($csp));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (!app()->environment('local')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    private function buildCsp(array $directives): string
    {
        $parts = [];
        foreach ($directives as $directive => $sources) {
            $parts[] = empty($sources)
                ? $directive
                : $directive . ' ' . implode(' ', $sources);
        }
        return implode('; ', $parts);
    }
}
