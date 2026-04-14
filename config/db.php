<?php
define('DB_HOST', 'monorail.proxy.rlwy.net');
define('DB_PORT', '30921');
define('DB_USER', 'root');
define('DB_PASS', 'irissaDIVsnhnHVPyhsxZHbndDRqqnoj');
define('DB_NAME', 'railway');
define('CURRENCY', 'GH₵');
define('APP_NAME', 'UniStay');

$pdo = new PDO(
    "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME,
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
