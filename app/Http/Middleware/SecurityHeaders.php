<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add browser security headers to every web response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Prevent MIME-type sniffing.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent the application from being embedded in an iframe.
        $response->headers->set('X-Frame-Options', 'DENY');

        // Limit referrer information sent to another website.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable browser capabilities that this system does not use.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        // Disable legacy Adobe cross-domain policy files.
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Do not advertise the framework through an X-Powered-By header.
        $response->headers->remove('X-Powered-By');

        // Apply HSTS only over HTTPS and only outside local development.
        // Never send HSTS over plain HTTP because browsers may cache it.
        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // Enforce CSP in production. Local Vite HMR uses a different port and
        // would otherwise be blocked during development.
        if (app()->environment('production')) {
            // This policy is compatible with the project's current inline scripts.
            // Later, replace 'unsafe-inline' with CSP nonces for stronger XSS defense.
            $contentSecurityPolicy = implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "object-src 'none'",
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
                "font-src 'self' https://fonts.bunny.net data:",
                "img-src 'self' data: blob:",
                "connect-src 'self'",
                "media-src 'self'",
                "worker-src 'self' blob:",
                "manifest-src 'self'",
                'upgrade-insecure-requests',
            ]);

            $response->headers->set('Content-Security-Policy', $contentSecurityPolicy);
        }

        return $response;
    }
}
