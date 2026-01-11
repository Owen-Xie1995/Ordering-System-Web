<?php
session_start();
$code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5); // 避免難辨字
$_SESSION['captcha'] = $code;

$img = imagecreatetruecolor(100, 40);
$bg = imagecolorallocate($img, 135, 206, 250);
$fg = imagecolorallocate($img, 255, 255, 255);
$line = imagecolorallocate($img, 200, 200, 200);

imagefill($img, 0, 0, $bg);
for ($i = 0; $i < 5; $i++) {
    imageline($img, rand(0,100), rand(0,40), rand(0,100), rand(0,40), $line);
}
imagestring($img, 5, 20, 10, $code, $fg);

header("Content-Type: image/png");
imagepng($img);
imagedestroy($img);
?>
