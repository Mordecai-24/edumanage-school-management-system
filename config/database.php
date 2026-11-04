<?php
// Database settings (XAMPP defaults)
$host     = 'localhost';
$dbname   = 'school_management';
$username = 'root';
$password = '';        // XAMPP's root user has no password by default
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors so we can catch them
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as ['email' => '...']
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());   // details go to the server's error log
    exit('Database connection failed. Please try again later.');
}