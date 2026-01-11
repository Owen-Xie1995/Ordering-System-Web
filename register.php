<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>會員註冊</title>
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

    .register-container {
      background-color: #fff;
      padding: 2rem 3rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      width: 100%;
      max-width: 450px;
      text-align: center;
    }

    .register-container h2 {
      color: #ff5722;
      margin-bottom: 1.5rem;
    }

    .register-container input[type="text"],
    .register-container input[type="email"],
    .register-container input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      margin: 0.5rem 0 1rem 0;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
    }

    .register-container button {
      padding: 0.75rem 2rem;
      background-color: #ff5722;
      color: white;
      border: none;
      border-radius: 30px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.3s;
    }

    .register-container button:hover {
      background-color: #e64a19;
      transform: scale(1.05);
    }

    .register-container .note {
      margin-top: 1rem;
      font-size: 0.9rem;
      color: #666;
    }

  </style>
</head>
<body>
  <div class="register-container">
    <h2>會員註冊</h2>
    <form action="" method="post">
      <input type="text" name="id" placeholder="帳號" required>
      <input type="password" name="pw" placeholder="密碼" required>
      <button type="submit" name="submit">註冊</button>
    </form>
    <div class="note">已經有帳號了？請回首頁登入</div>
  </div>
</body>

<?php
	if(isset($_POST['submit']))
	{
	   $id = $_POST['id']; $pw = $_POST['pw'];
	   $con = mysqli_connect("localhost","root","","uch");
	   mysqli_query($con, 'set names utf8');
	   $r = mysqli_query($con, "SELECT * FROM login WHERE id = '$id'");
	   if(mysqli_fetch_array($r))
		echo "帳號名稱重複！";
	   else
	   {    
		mysqli_query($con, "INSERT INTO login (id, pw) VALUES ('$id', '$pw')");
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
		echo "註冊成功！";
	   }
	}
?>

</html>
