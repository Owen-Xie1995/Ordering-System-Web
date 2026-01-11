<?php
session_start();

// 檢查驗證碼
if ($_POST['captcha'] !== $_SESSION['captcha']) {
    echo "<script>alert('驗證碼不正確'); history.back();</script>";
    exit;
}

// 檢查帳密
$id = $_POST["a"];
$pw = $_POST["pw"];

$con = mysqli_connect('localhost', 'root', '', 'uch');
mysqli_query($con, 'set names utf8');

$r = mysqli_query($con, "SELECT * FROM login");

$test = 0;

while ($row = mysqli_fetch_array($r)) {
    if ($id == $row[0] && $pw == $row[1]) {
        $test = 1;
        $img = $row[2];  // 圖片名
        break;
    }
}

if ($test == 1) {
    $_SESSION['id'] = $id;
    $_SESSION['img'] = $img;

    // 傳 postMessage 給 home.html 的 iframe 父層，並自動導向 home.php
    echo <<<HTML
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>登入成功</title>
</head>
<body>
  <script>
    // 通知父層登入成功（讓 iframe 切換）
    window.parent.postMessage('login-success', '*');
    // 繼續導向原本的 home.php 顯示會員資訊
    setTimeout(() => {
      window.location.href = 'home.php';
    }, 100); // 等待一點時間讓 postMessage 發送完成
  </script>
</body>
</html>
HTML;
    exit;
} else {
    echo "<script>alert('帳號或密碼錯誤'); location.href='login.html';</script>";
    exit;
}
?>
