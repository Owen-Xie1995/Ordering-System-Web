<?php
// Development server only: php -S 127.0.0.1:8000 router.php
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (preg_match('#(^|/)(\\.|includes(?:/|$)|scripts(?:/|$)|database(?:/|$)|tests(?:/|$))#', $path)
    || !preg_match('#^/[a-zA-Z0-9_./-]*$#', $path)) {
    http_response_code(404);
    exit;
}
return false;
