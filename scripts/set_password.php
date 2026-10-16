<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/app.php';
$account = $argv[1] ?? '';
$password = getenv('NEW_PASSWORD') ?: '';
if ($account === '' || !valid_new_password($password)) {
    fwrite(STDERR, "用法：設定 NEW_PASSWORD 後執行 php scripts/set_password.php 既有帳號。\n");
    exit(1);
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = db()->prepare('UPDATE login SET pw = ? WHERE id = ?');
$stmt->bind_param('ss', $hash, $account);
$stmt->execute();
if (!$stmt->affected_rows) { fwrite(STDERR, "找不到帳號。\n"); exit(1); }
echo "密碼已重設，角色不變。\n";
