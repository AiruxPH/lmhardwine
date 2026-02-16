<?php
$host = 'srv1999.hstgr.io';
$db = 'u130348899_lmhardwine';
$user = 'u130348899_lyndy';
$pass = 'Lyndy999@';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // For production, log the error and show a generic message
    // error_log($e->getMessage());
    die("Database Connection Failed: " . $e->getMessage());
}
