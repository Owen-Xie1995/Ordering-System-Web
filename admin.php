<?php
require __DIR__ . '/includes/app.php';
require_admin();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $quantity = checked_quantity($_POST['quantity'] ?? null);
    if (!$id || $quantity === null) {
        http_response_code(422);
        $error = '庫存需為 0–999999999 的整數。';
    } else {
        $stmt = db()->prepare('SELECT id FROM inventory WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            http_response_code(404);
            $error = '找不到此商品。';
        } else {
            $stmt = db()->prepare('UPDATE inventory SET quantity = ? WHERE id = ?');
            $stmt->bind_param('ii', $quantity, $id);
            $stmt->execute();
            redirect('admin.php?updated=1');
        }
    }
}
$result = db()->query('SELECT id, product_name, quantity FROM inventory ORDER BY id');
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
      padding: 2rem;
      overflow-x: auto;
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
    <p>庫存資料供管理展示，尚未與選餐購物車連動。</p>
    <?php if (isset($_GET['updated'])): ?><p role="status">庫存已更新。</p><?php endif; ?>
    <?php if ($error): ?><p role="alert"><?= h($error) ?></p><?php endif; ?>
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
          <td><?= h($row['product_name']) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td>
            <form method="post" action="admin.php">
              <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="number" name="quantity" value="<?= $row['quantity'] ?>" min="0" max="999999999" step="1" required>
              <input type="submit" value="更新">
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <a href="index.html" target="_top" class="logout-btn">回主頁</a>
  </div>
</body>
</html>
