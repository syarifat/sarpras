<?php
/**
 * Database Configuration
 * Sarpras Management System
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'sarpras_db');
define('DB_USER', 'root');
define('DB_PASS', '1');
define('DB_CHARSET', 'utf8mb4');

/**
 * Create PDO Connection
 */
function getConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Execute Query with Parameters
 */
function query($sql, $params = []) {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch All Results
 */
function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

/**
 * Fetch Single Result
 */
function fetch($sql, $params = []) {
    return query($sql, $params)->fetch();
}

/**
 * Get Last Insert ID
 */
function lastInsertId() {
    return getConnection()->lastInsertId();
}
