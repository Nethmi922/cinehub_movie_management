<?php
/**
 * Database connection (PDO)
 * Update these four values to match your local XAMPP / server MySQL setup.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'movie_management');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Please check config/db.php settings. (" . $e->getMessage() . ")");
}

// Start session for auth/cart-hold state, used across every page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
