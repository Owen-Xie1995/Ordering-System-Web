<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/app.php';
$account = $argv[1] ?? '';
$password = getenv('ADMIN_PASSWORD') ?: '';
if (!preg_match('/^[\\p{L}\\p{N}_-]{3,40}$/u', $account) || !valid_new_password($password)) {
    fwrite(STDERR, "用法：設定 ADMIN_PASSWORD 後執行 php scripts/create_admin.php 帳號。新密碼需為 8–72 位元組。\n");
    exit(1);
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = db()->prepare("INSERT INTO login (id, pw, role) VALUES (?, ?, 'admin')");
$stmt->bind_param('ss', $account, $hash);
try {
    $stmt->execute();
} catch (mysqli_sql_exception $error) {
    if ($error->getCode() !== 1062) throw $error;
    fwrite(STDERR, "帳號已存在；此指令不會修改或提升既有會員。\n");
    exit(1);
}
echo "管理員已建立。\n";
