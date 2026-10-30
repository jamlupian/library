<?php include 'includes/header.php';
$count = fn($sql) => $db->query($sql)->fetch_row()[0];
$overdue = $db->query("
    SELECT s.name, s.student_no, b.title, l.due_on,
           DATEDIFF(CURDATE(), l.due_on) AS days_late
    FROM loans l
    JOIN students s ON s.id = l.student_id
    JOIN books b ON b.id = l.book_id
    WHERE l.returned_on IS NULL AND l.due_on < CURDATE()
    ORDER BY l.due_on
    LIMIT 10");
$unavailable = $db->query("SELECT title, author FROM books WHERE available = 0 ORDER BY title LIMIT 10");
?>
<h1>Dashboard</h1>
<div class="stats">
  <div class="card"><b><?= $count("SELECT COUNT(*) FROM students") ?></b>Students</div>
  <div class="card"><b><?= $count("SELECT COALESCE(SUM(copies),0) FROM books") ?></b>Total Books</div>
  <div class="card"><b><?= $count("SELECT COUNT(*) FROM loans WHERE returned_on IS NULL") ?></b>Currently Borrowed</div>
  <div class="card"><b><?= $count("SELECT COUNT(*) FROM loans WHERE returned_on IS NULL AND due_on < CURDATE()") ?></b>Overdue</div>
</div>
<div class="card dashboard-actions">
  <h2>Quick actions</h2>
  <a class="button-link" href="students.php">Manage students</a>
  <a class="button-link" href="books.php">Manage books</a>
  <a class="button-link" href="loans.php">Issue or return books</a>
</div>
<h2>Overdue loans</h2>
<table>
  <tr><th>Student</th><th>Book</th><th>Due</th><th>Days overdue</th></tr>
  <?php if (!$overdue->num_rows): ?><tr><td colspan="4">No overdue loans.</td></tr><?php endif; ?>
  <?php while ($r = $overdue->fetch_assoc()): ?>
  <tr>
    <td><?= e($r['name']) ?> (<?= e($r['student_no']) ?>)</td>
    <td><?= e($r['title']) ?></td>
    <td class="late"><?= e($r['due_on']) ?></td>
    <td><?= (int)$r['days_late'] ?></td>
  </tr>
  <?php endwhile; ?>
</table>
<h2 style="margin-top:28px">Unavailable books</h2>
<table>
  <tr><th>Title</th><th>Author</th></tr>
  <?php if (!$unavailable->num_rows): ?><tr><td colspan="2">All listed books have copies available.</td></tr><?php endif; ?>
  <?php while ($r = $unavailable->fetch_assoc()): ?>
  <tr><td><?= e($r['title']) ?></td><td><?= e($r['author']) ?></td></tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
