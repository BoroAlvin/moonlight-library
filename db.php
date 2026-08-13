<?php
declare(strict_types=1);

// XAMPP defaults are root with a blank password. Environment variables can
// override these values when the site is deployed elsewhere.
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'moonlight_library';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $mysqli->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL in the XAMPP Control Panel and try again.');
}
