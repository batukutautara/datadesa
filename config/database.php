<?php
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '=') !== false && strpos(trim($line), '#') !== 0) {
            list($key, $val) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
$dbname = $_ENV['DB_NAME'] ?? 'db_penduduk_desa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>Error</title><style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:#f5f5f5}.card{background:#fff;padding:40px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.1);text-align:center;max-width:420px}h2{color:#dc3545}p{color:#666}</style></head><body><div class="card"><h2>Koneksi Database Gagal</h2><p> Sistem tidak dapat terhubung ke database. Silakan periksa konfigurasi koneksi database Anda.</p></div></body></html>';
    exit;
}
