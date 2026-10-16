<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/app.php';
$connection = db();
$connection->multi_query(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
do {
    if ($result = $connection->store_result()) $result->free();
} while ($connection->more_results() && $connection->next_result());
echo "資料表與 18 筆展示庫存已備妥；既有庫存不會重設。\n";
