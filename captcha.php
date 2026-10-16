<?php
require __DIR__ . '/includes/app.php';
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code = '';
for ($i = 0; $i < 5; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
$_SESSION['captcha'] = ['code' => $code, 'expires' => time() + 300];
$image = imagecreatetruecolor(120, 40);
$background = imagecolorallocate($image, 135, 206, 250);
$foreground = imagecolorallocate($image, 30, 55, 80);
imagefill($image, 0, 0, $background);
imagestring($image, 5, 36, 12, $code, $foreground);
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
