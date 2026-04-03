<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL
define('BASE_URL', '/'); // Adjust to your subfolder if needed
define('ROOT_PATH', __DIR__);
if (!defined('APP_NAME')) {
    define('APP_NAME', 'School Management System');
}

// Database connection
function getDB() {
    static $db = null;
    if ($db === null) {
        // Check for PostgreSQL via DATABASE_URL (Railway Postgres provides this)
        if (!empty($_ENV['DATABASE_URL']) || !empty(getenv('DATABASE_URL'))) {
            $dbUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
            $db = new PDO($dbUrl);
        } else {
            // Fall back to SQLite for local development
            $db = new PDO('sqlite:' . ROOT_PATH . '/database/school.db');
        }
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $db;
}

// Include helper functions
require_once ROOT_PATH . '/db.php';
require_once ROOT_PATH . '/includes/settings_helper.php';