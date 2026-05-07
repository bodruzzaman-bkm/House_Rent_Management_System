<?php
/**
 * PDO connection helper
 * Returns a singleton PDO instance configured for MySQL with secure defaults.
 * Usage: $pdo = getPDO();
 */
function getPDO()
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $db   = getenv('DB_NAME') ?: 'house_rent_db';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        // ensure strict SQL mode where possible
        $pdo->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
    } catch (PDOException $e) {
        error_log('PDO connection failed: ' . $e->getMessage());
        // In production, avoid leaking details. Show a generic message.
        die('Database connection failed.');
    }

    return $pdo;
}
