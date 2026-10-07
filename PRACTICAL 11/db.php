<?php
// Practical 11: DB connection using MySQLi
// XAMPP defaults: host=localhost, user=root, password='', db=student_db

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "student_db";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage())
        . "<br>Tip: Import <b>setup.sql</b> in phpMyAdmin to create <b>student_db</b>.");
}

// Helper: flash messages using session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function set_msg($type, $text) {
    $_SESSION['msg_type'] = $type; // success | error
    $_SESSION['msg_text'] = $text;
}
function show_msg() {
    if (!empty($_SESSION['msg_text'])) {
        $cls = ($_SESSION['msg_type'] === 'success') ? 'msg-success' : 'msg-error';
        echo '<div class="' . $cls . '">' . htmlspecialchars($_SESSION['msg_text']) . '</div>';
        unset($_SESSION['msg_type'], $_SESSION['msg_text']);
    }
}
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
