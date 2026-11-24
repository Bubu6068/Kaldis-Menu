<?php
/**
 * Global Helper Functions
 */

declare(strict_types=1);

use App\Helpers\Env;

if (!function_exists('base_url')) {
    /**
     * Build a full URL from a relative path, using APP_URL from .env with auto-detection fallback.
     */
    function base_url(string $path = ''): string
    {
        $base = rtrim(Env::getString('APP_URL', ''), '/');
        if ($base === '' || (str_contains($base, 'localhost') && !empty($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // Auto-detect subfolder from SCRIPT_NAME if running in a subdirectory
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $subfolder = '';
            if (str_ends_with($scriptDir, '/public')) {
                $subfolder = substr($scriptDir, 0, -7);
            } elseif ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '') {
                $subfolder = $scriptDir;
            }
            $base = $scheme . '://' . $host . rtrim($subfolder, '/');
        }
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset_url')) {
    /**
     * Build an asset URL with automatic cache-busting (filemtime).
     * Guarantees the browser always loads the freshest CSS/JS upon changes.
     */
    function asset_url(string $path = ''): string
    {
        $base = base_url($path);
        $fullPath = defined('BASE_PATH') ? BASE_PATH . '/public/' . ltrim($path, '/') : '';
        if ($fullPath && file_exists($fullPath)) {
            return $base . '?v=' . filemtime($fullPath);
        }
        return $base . '?v=' . time();
    }
}

if (!function_exists('e')) {
    /**
     * Safely escape a value for HTML output.
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

