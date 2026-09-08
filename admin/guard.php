<?php
// Include this at the top of every admin/*.php page (after config/db.php)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
