<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds security headers to all web responses.
 *
 * - CSP with nonce for Vite scripts/styles (production: strict, dev: permissive)
 * - HSTS in production (forces HTTPS)
 * - X-Frame-Options, X-Content-Type-Options, Referrer-Policy
 * - Permissions-Policy restricting browser APIs
 *
 * In development, CSP allows Vite's dev server (HMR WebSocket, inline scripts,
 * Vue DevTools) which would otherwise be blocked by strict nonce-based CSP.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        // Non-CSP headers apply in all environments
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), payment=()');

        // CSP: strict in production, permissive in dev (Vite HMR needs inline scripts + WSS)
        if (app()->isProduction()) {
            $nonce = Vite::cspNonce();

            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline'",
                "img-src 'self' data: https: blob:",
                "font-src 'self' data:",
                "connect-src 'self' https:",
                "media-src 'self'",
                "frame-ancestors 'none'",
                "form-action 'self'",
                "base-uri 'self'",
            ]));

            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
