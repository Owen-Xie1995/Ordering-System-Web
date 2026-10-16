<?php
require __DIR__ . '/includes/app.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $account = is_string($_POST['a'] ?? null) ? trim($_POST['a']) : '';
    $password = is_string($_POST['pw'] ?? null) ? $_POST['pw'] : '';
    $captcha = is_string($_POST['captcha'] ?? null) ? strtoupper(trim($_POST['captcha'])) : '';
    $expected = $_SESSION['captcha'] ?? [];
    unset($_SESSION['captcha']);
    if (!$captcha || !isset($expected['code'], $expected['expires'])
        || $expected['expires'] < time() || !hash_equals($expected['code'], $captcha)) {
        $error = '驗證碼不正確或已過期，請重新輸入。';
    } else {
        $stmt = db()->prepare('SELECT id, pw FROM login WHERE id = ?');
        $stmt->bind_param('s', $account);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user && verify_stored_password($password, $user['pw'])) {
            if (password_needs_rehash($user['pw'], PASSWORD_DEFAULT)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $update = db()->prepare('UPDATE login SET pw = ? WHERE id = ?');
                $update->bind_param('ss', $hash, $user['id']);
                $update->execute();
            }
            session_regenerate_id(true);
            $_SESSION = ['user_id' => $user['id']];
            csrf_token();
            redirect('home.php');
        }
        $error = '帳號或密碼不正確。';
    }
    http_response_code(422);
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>會員登入</title><link rel="stylesheet" href="assets/member.css"></head>
<body><main class="member-container">
  <h1>會員登入</h1>
  <?php if (isset($_GET['registered'])): ?><p role="status">註冊完成，請登入。</p><?php endif; ?>
  <?php if ($error): ?><p class="message" role="alert"><?= h($error) ?></p><?php endif; ?>
  <form method="post" action="login.php">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label for="account">帳號</label><input id="account" name="a" autocomplete="username" maxlength="64" required>
    <label for="password">密碼</label><input id="password" type="password" name="pw" autocomplete="current-password" required>
    <label for="captcha">圖片驗證碼</label>
    <img src="captcha.php" width="120" height="40" alt="五位英數字驗證碼；看不清楚時可重新整理頁面">
    <input id="captcha" name="captcha" autocomplete="off" maxlength="5" required>
    <button type="submit">登入</button>
  </form>
  <p class="note">尚未加入會員？<a href="register.php">註冊帳號</a></p>
  <a href="index.html" target="_top">回首頁</a>
</main></body></html>
