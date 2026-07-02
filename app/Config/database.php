<?php
// Database configuration central file
$DB_PORT = 3306; // Default MySQL port
// $DB_PORT = 3307;
$DB_HOST = '127.0.0.1' . ($DB_PORT ? ":$DB_PORT" : '');
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'medicus';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    error_log('DB connect error: ' . $mysqli->connect_error);
    die('Database connection failed. Please check config/database.php');
}
$mysqli->set_charset('utf8mb4');
?>