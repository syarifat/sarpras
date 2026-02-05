<?php

/**
 * Database Configuration
 * Sarpras Management System
 */

// Set timezone to Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Cloud Database (Filess.io)
define('DB_HOST', '60vwjf.h.filess.io');
define('DB_PORT', '61002');
define('DB_NAME', 'sarpras_factoryjar');
define('DB_USER', 'sarpras_factoryjar');
define('DB_PASS', 'c7ac010ef79d50f980469573fd23c3c4f88ee5e4');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create PDO Connection
 */
function getConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Set MySQL timezone to Indonesia (WIB = UTC+7)
            $pdo->exec("SET time_zone = '+07:00'");
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Execute Query with Parameters
 */
function query($sql, $params = [])
{
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch All Results
 */
function fetchAll($sql, $params = [])
{
    return query($sql, $params)->fetchAll();
}

/**
 * Fetch Single Result
 */
function fetch($sql, $params = [])
{
    return query($sql, $params)->fetch();
}

/**
 * Get Last Insert ID
 */
function lastInsertId()
{
    return getConnection()->lastInsertId();
}
