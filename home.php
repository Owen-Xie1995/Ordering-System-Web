<?php
require __DIR__ . '/includes/app.php';
$user = require_member();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>會員中心</title><link rel="stylesheet" href="assets/member.css"></head>
<body><main class="member-container">
  <h1>歡迎 <?= h($user['id']) ?> 回家</h1>
  <p>目前提供選餐與購物車展示，尚未開放送出訂單。</p>
  <a class="button" href="order.html">開始選餐</a>
  <?php if ($user['role'] === 'admin'): ?><a class="button" href="admin.php">庫存管理</a><?php endif; ?>
  <form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><button type="submit">登出</button></form>
  <a href="index.html" target="_top">回首頁</a>
</main>
<script>if (window.parent !== window) window.parent.postMessage('session-changed', window.location.origin);</script>
</body></html>
