<?php
require __DIR__ . '/includes/app.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $account = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';
    $password = is_string($_POST['pw'] ?? null) ? $_POST['pw'] : '';
    if (!preg_match('/^[\\p{L}\\p{N}_-]{3,40}$/u', $account)) {
        $error = '帳號請使用 3–40 個字母、數字、底線或連字號。';
    } elseif (!valid_new_password($password)) {
        $error = '密碼長度需為 8–72 位元組，中文等字元可能占用多個位元組。';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare("INSERT INTO login (id, pw, role) VALUES (?, ?, 'member')");
        $stmt->bind_param('ss', $account, $hash);
        try {
            $stmt->execute();
            redirect('login.php?registered=1');
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() !== 1062) throw $exception;
            $error = '此帳號已有人使用，請選擇其他帳號。';
        }
    }
    http_response_code(422);
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>會員註冊</title><link rel="stylesheet" href="assets/member.css"></head>
<body><main class="member-container">
  <h1>會員註冊</h1>
  <?php if ($error): ?><p class="message" role="alert"><?= h($error) ?></p><?php endif; ?>
  <form method="post" action="register.php">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label for="account">帳號</label><input id="account" name="id" autocomplete="username" minlength="3" maxlength="40" required>
    <p class="note">3–40 個字母、數字、底線或連字號。</p>
    <label for="password">密碼</label><input id="password" type="password" name="pw" autocomplete="new-password" minlength="8" required>
    <p class="note">8–72 位元組；中文等字元可能占用多個位元組。</p>
    <button type="submit">註冊</button>
  </form>
  <a href="login.php">已有帳號？登入</a> · <a href="index.html" target="_top">回首頁</a>
</main></body></html>
