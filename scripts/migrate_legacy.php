<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/app.php';
$connection = db();
$duplicates = $connection->query('SELECT id FROM login GROUP BY id HAVING COUNT(*) > 1');
if ($duplicates->num_rows) {
    fwrite(STDERR, "存在重複帳號，請先備份並處理重複資料。\n");
    exit(1);
}
$rows = $connection->query('SELECT id, pw FROM login')->fetch_all(MYSQLI_ASSOC);
$pending = [];
foreach ($rows as $row) {
    if (password_get_info((string) $row['pw'])['algoName'] !== 'unknown') continue;
    $password = (string) $row['pw'];
    if ($password === '' || strlen($password) > 72 || strpos($password, "\0") !== false) {
        fwrite(STDERR, "帳號 " . $row['id'] . " 的舊密碼不能安全轉換，請先備份並重設為 1–72 位元組的非空值。\n");
        exit(1);
    }
    $pending[] = $row;
}
if (!in_array('--apply', $argv, true)) {
    echo count($pending) . " 筆明文密碼待轉換。備份資料庫後，加上 --apply 才會調整欄位並雜湊。\n";
    exit;
}
// DDL has implicit commits in MySQL. Back up first and keep the application offline.
$connection->query('ALTER TABLE login MODIFY pw VARCHAR(255) NOT NULL');
$columns = $connection->query("SHOW COLUMNS FROM login LIKE 'role'");
if (!$columns->num_rows) {
    $connection->query("ALTER TABLE login ADD COLUMN role ENUM('member', 'admin') NOT NULL DEFAULT 'member'");
}
$indexes = $connection->query("SHOW INDEX FROM login WHERE Column_name = 'id' AND Non_unique = 0");
if (!$indexes->num_rows) $connection->query('ALTER TABLE login ADD UNIQUE INDEX uq_login_id (id)');
$connection->begin_transaction();
try {
    $stmt = $connection->prepare('UPDATE login SET pw = ? WHERE id = ?');
    foreach ($pending as $row) {
        $hash = password_hash($row['pw'], PASSWORD_DEFAULT);
        $stmt->bind_param('ss', $hash, $row['id']);
        $stmt->execute();
    }
    $connection->commit();
} catch (Throwable $error) {
    $connection->rollback();
    throw $error;
}
echo count($pending) . " 筆密碼已轉換；既有雜湊保留，新角色預設為 member。\n";
