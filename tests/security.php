<?php
require dirname(__DIR__) . '/includes/app.php';
function check(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, $message . "\n"); exit(1); }
}
session_save_path(sys_get_temp_dir());
start_session();
$token = csrf_token();
check(valid_csrf($token), 'CSRF token should validate');
check(!valid_csrf(null) && !valid_csrf('wrong') && !valid_csrf([]), 'Missing or malformed CSRF must fail');
check(!verify_stored_password('password123', 'password123'), 'Plaintext credentials must never authenticate');
$hash = password_hash('password123', PASSWORD_DEFAULT);
check(verify_stored_password('password123', $hash), 'Hash verification failed');
check(!verify_stored_password('wrongpass', $hash), 'Wrong password authenticated');
check(!valid_new_password('short'), 'Short new password accepted');
check(!valid_new_password(str_repeat('a', 73)), 'bcrypt truncation must be rejected');
check(!valid_new_password("password\0withnul"), 'NUL must be rejected');
foreach (['-1', '1.5', '1e3', '9999999999', '', [], null] as $value) {
    check(checked_quantity($value) === null, 'Invalid inventory quantity accepted');
}
check(checked_quantity('0') === 0 && checked_quantity('999999999') === 999999999, 'Valid quantities rejected');
echo "Security checks passed.\n";
