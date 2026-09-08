<?php
// Include this at the top of every counter/*.php page (after config/db.php)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['counter_staff', 'admin'])) {
    header('Location: ../login.php');
    exit;
}
