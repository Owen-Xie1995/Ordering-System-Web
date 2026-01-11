<?php
// 連線設定
$con = mysqli_connect("localhost", "root", "", "uch");
mysqli_query($con, "SET NAMES utf8");

// 處理調整庫存表單送出
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['quantity'])) {
    $id = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);

    $stmt = $con->prepare("UPDATE inventory SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('更新成功！'); window.location.href = 'inventory_admin.php';</script>";
    exit;
}

// 讀取所有商品資料
$result = mysqli_query($con, "SELECT id, product_name, quantity FROM inventory");
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>庫存管理</title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      background: linear-gradient(to bottom right, #fdfbfb, #ebedee);
      margin: 0;
      padding: 2rem;
    }

    .admin-container {
      max-width: 900px;
      margin: auto;
      background-color: #fff;
      padding: 2rem 3rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    h2 {
      text-align: center;
      color: #ff5722;
      margin-bottom: 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th, table td {
      padding: 1rem;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    table th {
      background-color: #ffe0b2;
      color: #333;
    }

    tr:hover {
      background-color: #f5f5f5;
    }

    input[type="number"] {
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      width: 60px;
    }

    input[type="submit"] {
      background-color: #ff5722;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 30px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #e64a19;
    }

    .logout-btn {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.5rem 1.5rem;
      background-color: #e53935;
      color: white;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      text-decoration: none;
    }

    .logout-btn:hover {
      background-color: #c62828;
    }
  </style>
</head>
<body>
  <div class="admin-container">
    <h2>庫存查看與調整</h2>
    <table>
      <thead>
        <tr>
          <th>商品名稱</th>
          <th>庫存數量</th>
          <th>調整庫存</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= htmlspecialchars($row['product_name']) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td>
            <form method="post" action="">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="number" name="quantity" value="<?= $row['quantity'] ?>" min="0" required>
              <input type="submit" value="更新">
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <a href="home.html" target="_top" class="logout-btn">回主頁</a>
  </div>
</body>
</html>
