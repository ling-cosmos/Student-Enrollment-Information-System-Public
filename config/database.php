<?php
// config/database.php
// Single place for all DB credentials. Never hardcode these elsewhere.

define('DB_HOST', '127.0.0.1');  // ← change from localhost
define('DB_NAME', 'seis_db');     // Matches the bundled seis.sql schema
define('DB_USER', 'root');
define('DB_PASS', '');            // ← empty for XAMPP
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO instance (singleton pattern).
 * Call getPDO() anywhere you need the database.
 */
function getPDO(): PDO {
    if (getenv('PORTFOLIO_DEMO') === 'true') {
        require_once dirname(__DIR__) . '/hosting/demo.php';
        return demoDatabase();
    }
    static $pdo = null;          // Stored once, reused on every call

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";dbname=" . DB_NAME
             . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return rows as arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Don't expose DB errors to the browser in production
            error_log($e->getMessage());
            die("Database connection failed. Please contact the administrator.");
        }
    }

    return $pdo;
}
