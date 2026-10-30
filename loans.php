<?php include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->begin_transaction();

        if (isset($_POST['return'])) {
            $id = (int)$_POST['loan_id'];
            $st = $db->prepare("UPDATE loans SET returned_on = CURDATE() WHERE id = ? AND returned_on IS NULL");
            $st->bind_param('i', $id);
            $st->execute();
            if ($st->affected_rows) {
                $st = $db->prepare("UPDATE books b JOIN loans l ON l.book_id = b.id SET b.available = b.available + 1 WHERE l.id = ?");
                $st->bind_param('i', $id);
                $st->execute();
            }
            $db->commit();
            go('loans.php', 'Book returned.');
        }

        // Issue a book
        $sid = (int)$_POST['student_id'];
        $bid = (int)$_POST['book_id'];
        $st = $db->prepare("UPDATE books SET available = available - 1 WHERE id = ? AND available > 0");
        $st->bind_param('i', $bid);
        $st->execute();
        if (!$st->affected_rows) { $db->rollback(); go('loans.php', 'No copies available.'); }

        $days = LOAN_DAYS;
        $st = $db->prepare("INSERT INTO loans (student_id, book_id, issued_on, due_on) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))");
        $st->bind_param('iii', $sid, $bid, $days);
        $st->execute();
        $db->commit();
        go('loans.php', 'Book issued.');
    } catch (mysqli_sql_exception $ex) {
        $db->rollback();
        go('loans.php', 'Something went wrong. Please try again.');
    }
}

$students = $db->query("SELECT id, student_no, name FROM students ORDER BY name");
$books    = $db->query("SELECT id, title FROM books WHERE available > 0 ORDER BY title");
$active   = $db->query("
    SELECT l.*, s.name, s.student_no, b.title,
           GREATEST(DATEDIFF(CURDATE(), l.due_on), 0) AS days_late
    FROM loans l
    JOIN students s ON s.id = l.student_id
    JOIN books b ON b.id = l.book_id
    WHERE l.returned_on IS NULL
    ORDER BY l.due_on");
?>
<h1>Issue / Return</h1>

<div class="card">
  <form method="post" class="inline">
    <select name="student_id" required>
      <option value="">Select student</option>
      <?php while ($s = $students->fetch_assoc()): ?>
        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['student_no']) ?>)</option>
      <?php endwhile; ?>
    </select>
    <select name="book_id" required>
      <option value="">Select book</option>
      <?php while ($b = $books->fetch_assoc()): ?>
        <option value="<?= $b['id'] ?>"><?= e($b['title']) ?></option>
      <?php endwhile; ?>
    </select>
    <button>Issue Book</button>
  </form>
  <small>Loan period: <?= LOAN_DAYS ?> days. Fine: <?= FINE_PER_DAY ?> per overdue day.</small>
</div>

<h2>Currently Borrowed</h2>
<table>
  <tr><th>Student</th><th>Book</th><th>Issued</th><th>Due</th><th>Fine</th><th></th></tr>
  <?php while ($r = $active->fetch_assoc()): ?>
  <tr>
    <td><?= e($r['name']) ?> (<?= e($r['student_no']) ?>)</td>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['issued_on']) ?></td>
    <td class="<?= $r['days_late'] ? 'late' : '' ?>"><?= e($r['due_on']) ?></td>
    <td><?= $r['days_late'] ? e($r['days_late'] * FINE_PER_DAY) : '-' ?></td>
    <td>
      <form method="post">
        <input type="hidden" name="loan_id" value="<?= $r['id'] ?>">
        <button name="return">Return</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
