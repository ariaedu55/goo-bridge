<?php

declare(strict_types=1);

/**
 * En-têtes HTTP de durcissement (idempotents si déjà envoyés ailleurs).
 */
function security_send_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
}

/**
 * N’autorise qu’une URL de redirection interne (pas d’open redirect).
 * Accepte un chemin relatif ou une URL absolue vers le même hôte.
 */
function security_safe_redirect_url(?string $candidate, string $fallback): string
{
    if ($candidate === null) {
        return $fallback;
    }

    $candidate = trim($candidate);
    if ($candidate === '' || preg_match('/[\r\n\0]/', $candidate) === 1) {
        return $fallback;
    }

    // Protocol-relative ou schéma → vérifier l’hôte
    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $candidate) === 1) {
        $parts = parse_url($candidate);
        if (!is_array($parts) || empty($parts['host'])) {
            return $fallback;
        }
        $expected = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $got = strtolower((string) $parts['host']);
        if ($expected === '' || $got !== $expected) {
            return $fallback;
        }
        $path = (string) ($parts['path'] ?? '/');
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }

    // Chemin relatif uniquement
    if ($candidate[0] !== '/') {
        // Autoriser aussi les chemins type "admin/..." en les normalisant via le fallback si douteux
        if (preg_match('#^[a-z0-9_./?-]+$#i', $candidate) !== 1) {
            return $fallback;
        }
    }

    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $candidate) === 1) {
        return $fallback;
    }

    return $candidate;
}

/**
 * Redirection admin : doit pointer vers une page /admin/ du site.
 */
function security_safe_admin_return(?string $candidate, string $fallback): string
{
    $url = security_safe_redirect_url($candidate, $fallback);
    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path) || !str_contains($path, '/admin/')) {
        return $fallback;
    }

    return $url;
}

/**
 * Chemin local sûr pour une redirection POST → GET (formulaires publics).
 */
function security_safe_request_path(string $fallback): string
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? $fallback);
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '' || preg_match('/[\r\n\0]/', $path) === 1) {
        return $fallback;
    }
    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $path) === 1) {
        return $fallback;
    }
    if ($path[0] !== '/') {
        return $fallback;
    }

    return $path;
}
