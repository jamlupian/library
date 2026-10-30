<?php
require_once __DIR__ . '/../config.php';
if (empty($public)) require_login($need ?? 'admin');
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Library System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav>
  <strong>📚 Library</strong>
  <?php if ($role === 'admin'): ?>
    <a href="index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="books.php">Books</a>
    <a href="loans.php">Issue / Return</a>
    <a href="logout.php" style="margin-left:auto">Logout (<?= e($_SESSION['username']) ?>)</a>
  <?php elseif ($role === 'student'): ?>
    <a href="student.php">My Library</a>
    <a href="logout.php" style="margin-left:auto">Logout (<?= e($_SESSION['name']) ?>)</a>
  <?php endif; ?>
</nav>
<main>
<?php if (!empty($_GET['msg'])): ?>
  <div class="msg"><?= e($_GET['msg']) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
  <div class="msg err"><?= e($error) ?></div>
<?php endif; ?>
