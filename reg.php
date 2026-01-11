<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
</head>

<body bgcolor="lightskyblue" text="cornsilk">
    <h1>
	<center>
	<font color="black" size=32>
	會員註冊
	<font>

        <p><form action="" method="post">
            帳號:<input name="id" type="text" required><br><br>
            密碼:<input name="pw" type="password" required><br><br>
	    <input type="submit" value="註冊" name="submit">
        </form></p>
	<hr>
	<font size=10 color=cornsilk><br>
	</center>
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
		echo "註冊成功！";
	   }
	}
?>