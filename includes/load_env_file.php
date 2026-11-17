<?php

declare(strict_types=1);

/**
 * Charge des variables d'environnement depuis un fichier type .env (sans dépendance Composer).
 * N'écrase pas une variable déjà définie par le serveur (Apache, Docker, CLI).
 */
function goo_bridge_load_env_file(string $path): void
{
    static $loadedPaths = [];

    if (isset($loadedPaths[$path])) {
        return;
    }
    $loadedPaths[$path] = true;

    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        if ($key === '' || str_contains($key, ' ')) {
            continue;
        }
        if (getenv($key) !== false) {
            continue;
        }
        $value = trim($value);
        if ($value !== '' && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = stripcslashes(substr($value, 1, -1));
        } elseif ($value !== '' && str_starts_with($value, "'") && str_ends_with($value, "'")) {
            $value = substr($value, 1, -1);
        }
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}
