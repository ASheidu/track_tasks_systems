<?php
// Load this before any HTML. config.local.php lives outside public/.
date_default_timezone_set('America/Chicago');
ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        || getenv('APP_HTTPS') === '1',
]);
session_start();
header('Cache-Control: no-store');

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $location): void
{
    header('Location: ' . $location, true, 303);
    exit;
}

function signOut(): void
{
    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $cookie['path'], $cookie['domain'], $cookie['secure'], true);
    session_destroy();
}

function requireLogin(): int
{
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
    if (time() - ($_SESSION['last_activity'] ?? 0) >= 1800) {
        signOut();
        redirect('login.php?expired=1');
    }
    $_SESSION['last_activity'] = time();
    return (int) $_SESSION['user_id'];
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function checkCsrf(): void
{
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        exit('This form has expired. Go back and reload the page.');
    }
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $file = __DIR__ . '/../config.local.php';
    $local = is_file($file) ? require $file : [];
    $settings = [];
    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $key) {
        $settings[$key] = getenv($key) !== false ? getenv($key) : ($local[$key] ?? '');
    }
    $dsn = 'mysql:host=' . ($settings['DB_HOST'] ?: '127.0.0.1')
        . ';port=' . ($settings['DB_PORT'] ?: '3306')
        . ';dbname=' . $settings['DB_NAME'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $settings['DB_USER'], $settings['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

set_exception_handler(function (Throwable $error): void {
    error_log('Track Task: ' . get_class($error) . ' in ' . basename($error->getFile()) . ':' . $error->getLine());
    http_response_code(500);
    echo 'Track Task could not complete the request. Please try again later.';
});
