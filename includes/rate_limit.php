<?php

declare(strict_types=1);

function rate_limit_storage_dir(): string
{
    $dir = dirname(__DIR__) . '/storage/rate_limit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    return $dir;
}

function rate_limit_client_ip(): string
{
    $remote = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    $remoteOk = $remote !== '' && filter_var($remote, FILTER_VALIDATE_IP);

    // En local, ignorer les en-têtes transmis (sinon contournement facile du rate-limit).
    if ($remoteOk && in_array($remote, ['127.0.0.1', '::1'], true)) {
        return $remote;
    }

    // Derrière Cloudflare : REMOTE_ADDR est l’edge CF → utiliser CF-Connecting-IP.
    $cf = trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if ($remoteOk && $cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) {
        return $cf;
    }

    // X-Forwarded-For uniquement si explicitement autorisé (proxy de confiance).
    static $trustProxy = null;
    if ($trustProxy === null) {
        $trustProxy = false;
        $envTrust = getenv('TRUST_PROXY_HEADERS');
        if (is_string($envTrust) && $envTrust !== '') {
            $trustProxy = filter_var($envTrust, FILTER_VALIDATE_BOOLEAN);
        } else {
            $appCfgPath = dirname(__DIR__) . '/config/app.php';
            if (is_file($appCfgPath)) {
                /** @var mixed $appCfg */
                $appCfg = include $appCfgPath;
                if (is_array($appCfg)) {
                    $trustProxy = !empty($appCfg['trust_proxy_headers']);
                }
            }
        }
    }

    if ($trustProxy) {
        $xff = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
        if ($xff !== '') {
            $first = trim(explode(',', $xff)[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }
    }

    if ($remoteOk) {
        return $remote;
    }

    return '0.0.0.0';
}

/**
 * @return array{allowed:bool, remaining:int, retry_after:int}
 */
function rate_limit_hit(string $bucket, int $max, int $windowSeconds): array
{
    $ip = rate_limit_client_ip();
    $key = hash('sha256', $bucket . '|' . $ip);
    $file = rate_limit_storage_dir() . '/' . $key . '.json';
    $now = time();
    $data = ['start' => $now, 'count' => 0];

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        return ['allowed' => true, 'remaining' => $max, 'retry_after' => 0];
    }

    try {
        flock($fp, LOCK_EX);
        $raw = stream_get_contents($fp);
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data['start'] = (int) ($decoded['start'] ?? $now);
                $data['count'] = (int) ($decoded['count'] ?? 0);
            }
        }

        if (($now - $data['start']) >= $windowSeconds) {
            $data = ['start' => $now, 'count' => 0];
        }

        if ($data['count'] >= $max) {
            $retry = max(1, $windowSeconds - ($now - $data['start']));
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE));
            fflush($fp);

            return ['allowed' => false, 'remaining' => 0, 'retry_after' => $retry];
        }

        $data['count']++;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE));
        fflush($fp);

        return [
            'allowed' => true,
            'remaining' => max(0, $max - $data['count']),
            'retry_after' => 0,
        ];
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/**
 * Refuse la requête avec 429 si le bucket est saturé.
 */
function rate_limit_enforce(string $bucket, int $max, int $windowSeconds, string $message = ''): void
{
    $result = rate_limit_hit($bucket, $max, $windowSeconds);
    if ($result['allowed']) {
        return;
    }

    if (!headers_sent()) {
        http_response_code(429);
        header('Retry-After: ' . (string) $result['retry_after']);
        header('Cache-Control: no-store');
        header('Content-Type: text/plain; charset=UTF-8');
    }

    $msg = $message !== ''
        ? $message
        : 'Trop de requêtes. Réessayez dans ' . $result['retry_after'] . ' seconde(s).';
    echo $msg;
    exit;
}
