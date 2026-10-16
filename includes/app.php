<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli
{
    static $connection;
    if (!$connection) {
        $connection = new mysqli(
            getenv('DB_HOST') ?: '127.0.0.1',
            getenv('DB_USER') ?: 'ordering',
            getenv('DB_PASSWORD') ?: '',
            getenv('DB_NAME') ?: 'ordering_system',
            (int) (getenv('DB_PORT') ?: '3306')
        );
        $connection->set_charset('utf8mb4');
    }
    return $connection;
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    if (!session_start()) throw new RuntimeException('無法建立工作階段。');
}

function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    start_session();
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function valid_csrf($token): bool
{
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function require_csrf(): void
{
    if (!valid_csrf($_POST['csrf'] ?? null)) {
        http_response_code(403);
        exit('表單已失效，請重新開啟頁面後再試。');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path, true, 303);
    exit;
}

function require_member(): array
{
    start_session();
    if (!isset($_SESSION['user_id']) || !is_string($_SESSION['user_id'])) redirect('login.php');
    // Re-read the role so revocation takes effect for existing sessions.
    $stmt = db()->prepare('SELECT id, role FROM login WHERE id = ?');
    $stmt->bind_param('s', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if (!$user) {
        unset($_SESSION['user_id']);
        redirect('login.php');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_member();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('此頁僅供管理員使用。');
    }
    return $user;
}

function valid_new_password(string $password): bool
{
    // bcrypt uses at most 72 bytes; reject longer values rather than truncate.
    return strlen($password) >= 8 && strlen($password) <= 72 && strpos($password, "\0") === false;
}

function verify_stored_password(string $password, string $stored): bool
{
    // Plaintext credentials must be migrated by the CLI before enabling logins.
    return $password !== '' && strlen($password) <= 72 && strpos($password, "\0") === false
        && password_get_info($stored)['algoName'] !== 'unknown'
        && password_verify($password, $stored);
}

function checked_quantity($value): ?int
{
    if (!is_string($value) || !preg_match('/^(0|[1-9][0-9]{0,8})$/D', $value)) return null;
    return (int) $value;
}

if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=UTF-8');
    set_exception_handler(function (Throwable $error): void {
        error_log((string) $error);
        http_response_code(503);
        header('Content-Type: text/html; charset=UTF-8');
        echo '服務暫時無法使用，請稍後再試。';
    });
    start_session();
}
