<?php
define('TIMEZONE', 'UTC');

define('DB_TYPE', 'mysql');

define('DB_NAME', 'lyrics_db');
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_general_ci');

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

date_default_timezone_set(TIMEZONE);

$dsn = DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$pdo = new PDO($dsn, DB_USER, DB_PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_PERSISTENT => false));
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("SET time_zone='+00:00';");
?>