<?php
require __DIR__ . '/includes/app.php';
$role = null;
if (isset($_SESSION['user_id'])) {
    $user = require_member();
    $role = $user['role'];
}
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['authenticated' => $role !== null, 'is_admin' => $role === 'admin', 'csrf' => csrf_token()]);
