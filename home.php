<?php
session_start();
if (!isset($_SESSION['id'])) {
    echo "<script>alert('請先登入'); location.href='login.html';</script>";
    exit;
}
$id = $_SESSION['id'];
$img = $_SESSION['img'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>會員中心</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to bottom right, #ffecd2, #fcb69f);
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .member-container {
      background-color: #fff;
      padding: 2rem 3rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .member-container h2 {
      color: #ff5722;
      margin-bottom: 1rem;
    }

    .member-container img {
      width: 40%;
      border-radius: 50%;
      margin-bottom: 1rem;
    }

    .logout-button {
      padding: 0.6rem 1.5rem;
      background-color: #ff5722;
      color: white;
      border: none;
      border-radius: 30px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .logout-button:hover {
      background-color: #e64a19;
    }
  </style>
</head>
<body>
  <div class="member-container">
    <h2>歡迎 <?= htmlspecialchars($id) ?> 回家</h2>
    <img src="<?= htmlspecialchars($img) ?>.png" alt="頭像">
    <br>
    <a href="logout.php"><button class="logout-button">登出</button></a>
  </div>
</body>
</html>
