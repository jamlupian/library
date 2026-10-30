<?php
ob_start();            // lets redirects work even after HTML has started
session_start();

// Default XAMPP credentials: user "root", empty password
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('localhost', 'root', '', 'library_db');
$db->set_charset('utf8mb4');

const LOAN_DAYS = 14;      // days a book can be borrowed
const FINE_PER_DAY = 5;    // fine per overdue day

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function go($page, $msg) { header("Location: $page?msg=" . urlencode($msg)); exit; }

// Redirects anyone who isn't logged in as $role
function require_login($role) {
    $r = $_SESSION['role'] ?? null;
    if ($r === $role) return;
    if ($r === 'admin')   { header('Location: index.php');   exit; }
    if ($r === 'student') { header('Location: student.php'); exit; }
    header('Location: ' . ($role === 'admin' ? 'admin_login.php' : 'login.php'));
    exit;
}

// Creates the default admin account (admin / adminlibrary) if none exists
function seed_admin($db) {
    if ($db->query("SELECT COUNT(*) FROM admins")->fetch_row()[0] == 0) {
        $u = 'admin';
        $h = password_hash('adminlibrary', PASSWORD_DEFAULT);
        $st = $db->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $st->bind_param('ss', $u, $h);
        $st->execute();
    }
}
