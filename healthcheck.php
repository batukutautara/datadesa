<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

echo "DATADESA HEALTHCHECK\n";
echo "PHP_VERSION=" . PHP_VERSION . "\n";
echo "DOCUMENT_ROOT=" . ($_SERVER['DOCUMENT_ROOT'] ?? '-') . "\n";
echo "CURRENT_DIR=" . __DIR__ . "\n";

$envFile = __DIR__ . '/.env';
echo "ENV_FILE=" . (file_exists($envFile) ? 'FOUND' : 'MISSING') . "\n";

$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '#') === 0 || strpos($trimmed, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $trimmed, 2);
        $env[trim($key)] = trim($value);
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$dbname = $env['DB_NAME'] ?? 'db_penduduk_desa';

echo "DB_HOST=" . $host . "\n";
echo "DB_USER=" . $user . "\n";
echo "DB_NAME=" . $dbname . "\n";
echo "DB_PASS_SET=" . ($pass !== '' ? 'YES' : 'NO') . "\n";
echo "PDO_MYSQL=" . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "DB_CONNECTION=OK\n";
    foreach (['profil_desa', 'penduduk', 'keluarga', 'users'] as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo strtoupper($table) . "_COUNT=" . $count . "\n";
    }
} catch (Throwable $e) {
    echo "DB_CONNECTION=FAILED\n";
    echo "ERROR=" . $e->getMessage() . "\n";
}
