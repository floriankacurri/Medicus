<?php
// App bootstrap: session + database
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Base URLs for links and assets (works when app is at http://localhost/Medicus/)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Medicus');
}
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', '/Medicus/public');
}

// Ensure session cookie path is correct so browser sends cookie for API requests under BASE_URL
// Set cookie path to BASE_URL (development) ; adjust secure flag for production
ini_set('session.cookie_path', BASE_URL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

?>
