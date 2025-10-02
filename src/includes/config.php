<?php
/**
 * SSH Authentication Webapp - Database Configuration
 *
 * This file contains database connection settings and configuration constants.
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ssh_auth_db');
define('DB_USER', 'ssh_auth_user');
define('DB_PASS', 'CHANGE_THIS_PASSWORD');

// Application configuration
define('APP_NAME', 'SSH Authentication Webapp');
define('APP_VERSION', '0.1.0');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Security settings
define('REQUIRE_HTTPS', true);
define('SESSION_SECURE_COOKIE', true);
define('SESSION_HTTP_ONLY', true);

// Database table names
define('TABLE_USERS', 'user_accounts');

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

/**
 * Get database connection
 * @return mysqli Database connection object
 * @throws Exception If connection fails
 */
function getDbConnection() {
    static $connection = null;

    if ($connection === null) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($connection->connect_error) {
            throw new Exception('Database connection failed: ' . $connection->connect_error);
        }

        // Set charset to utf8mb4 for full Unicode support
        $connection->set_charset('utf8mb4');
    }

    return $connection;
}

/**
 * Close database connection
 */
function closeDbConnection() {
    static $connection = null;

    if ($connection !== null) {
        $connection->close();
        $connection = null;
    }
}
?>
