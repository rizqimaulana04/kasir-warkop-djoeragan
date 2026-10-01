<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$productionFile=__DIR__.'/production.php';
$settings=is_file($productionFile)?require $productionFile:[];
define('DB_HOST',$settings['db_host']??'127.0.0.1');
define('DB_PORT',(int)($settings['db_port']??3307));
define('DB_NAME',$settings['db_name']??'kasir_warkop_djoeragan');
define('DB_USER',$settings['db_user']??'root');
define('DB_PASS',$settings['db_pass']??'');
define('APP_NAME','Djoeragan POS');
define('BASE_URL',$settings['base_url']??'/warkop-djoeragan');

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit('Koneksi database gagal. Periksa konfigurasi aplikasi.');
    }
}
