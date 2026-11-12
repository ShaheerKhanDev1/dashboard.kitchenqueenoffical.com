<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
$cfg = require __DIR__ . '/config/config.php';
$out = [
    'ok' => true,
    'php' => PHP_VERSION,
    'db_name' => $cfg['db']['name'] ?? '',
    'db_user' => $cfg['db']['user'] ?? '',
    'db_connected' => false,
    'db_error' => null,
];
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $cfg['db']['host'] ?? 'localhost',
        $cfg['db']['port'] ?? 3306,
        $cfg['db']['name'] ?? ''
    );
    $pdo = new PDO($dsn, (string) $cfg['db']['user'], (string) $cfg['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $out['db_connected'] = true;
    $out['tables'] = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $out['ok'] = false;
    $out['db_error'] = $e->getMessage();
}
echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
