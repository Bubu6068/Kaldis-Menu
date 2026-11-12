<?php
/**
 * Application Entry Point
 * All HTTP requests are routed through this file via .htaccess.
 */

declare(strict_types=1);

// Register shutdown handler to catch any fatal PHP errors before exit
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        $msg = $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line'];
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>500 — Server Error</title>';
        echo '<style>body{font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;padding:40px 20px;background:#fbf9f5;color:#23160e;text-align:center;}';
        echo '.box{max-width:650px;margin:0 auto;background:#fff;border-radius:12px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,0.06);}';
        echo 'pre{background:#f8f9fa;padding:15px;border-radius:8px;text-align:left;overflow-x:auto;font-size:13px;border:1px solid #e9ecef;}';
        echo '</style></head><body><div class="box"><h2 style="color:#d9534f;margin-top:0;">500 — Fatal Error</h2>';
        echo '<p>The application encountered an unexpected fatal error:</p>';
        echo '<pre>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</pre>';
        echo '<p><a href="javascript:location.reload()" style="color:#c69214;text-decoration:none;font-weight:600;">↻ Retry</a></p>';
        echo '</div></body></html>';
    }
});

define('BASE_PATH', dirname(__DIR__));

// ── 1. Core PSR-4 Fallback Autoloader for App\ namespace ─────────────────────
// Guarantees all application classes load even without Composer vendor/
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ── 2. Composer autoloader (if available) ───────────────────────────────────
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// ── 3. Helper functions ─────────────────────────────────────────────────────
require_once BASE_PATH . '/app/Helpers/functions.php';

use App\Helpers\Env;
use App\Helpers\Logger;
use App\Helpers\Session;
use App\Helpers\Router;

// ── 4. Main Application Execution ───────────────────────────────────────────
try {
    // Load environment
    Env::load(BASE_PATH . '/.env');

    // Initialize logger safely
    Logger::init(BASE_PATH . '/storage/logs/');

    // Start session
    Session::start();

    // Load routes & dispatch
    require BASE_PATH . '/routes/web.php';
    Router::dispatch();
} catch (\Throwable $ex) {
    if (class_exists(Logger::class)) {
        try {
            Logger::error('Unhandled exception: ' . $ex->getMessage(), [
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ]);
        } catch (\Throwable) {
            // Ignore logger failure if storage/logs is not writable
        }
    }

    if (!headers_sent()) {
        http_response_code(500);
    }

    $debug = class_exists(Env::class) ? Env::getBool('APP_DEBUG', true) : true;
    $message = $ex->getMessage();
    $details = $ex->getFile() . ':' . $ex->getLine();

    if (file_exists(BASE_PATH . '/resources/views/errors/500.php')) {
        try {
            require BASE_PATH . '/resources/views/errors/500.php';
        } catch (\Throwable $viewEx) {
            echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:30px;background:#fbf9f5;text-align:center;">';
            echo '<h2 style="color:#d9534f;">500 — Server Error</h2>';
            echo '<p><strong>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</strong></p>';
            if ($debug) {
                echo '<p style="color:#666;">' . htmlspecialchars($details, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            echo '</body></html>';
        }
    } else {
        echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:30px;background:#fbf9f5;text-align:center;">';
        echo '<h2 style="color:#d9534f;">500 — Server Error</h2>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '</body></html>';
    }
}
