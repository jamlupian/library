<?php
$need = 'student';
require_once 'config.php';
require_login($need);
$sid = (int)$_SESSION['student_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
    $bookId = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$bookId || $bookId < 1) {
        $error = 'Select a valid book to borrow.';
    } else {
        try {
            $db->begin_transaction();
            $st = $db->prepare("UPDATE books SET available = available - 1 WHERE id = ? AND available > 0");
            $st->bind_param('i', $bookId);
            $st->execute();

            if (!$st->affected_rows) {
                $db->rollback();
                $error = 'That book is currently unavailable.';
            } else {
                $days = LOAN_DAYS;
                $st = $db->prepare("INSERT INTO loans (student_id, book_id, issued_on, due_on) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))");
                $st->bind_param('iii', $sid, $bookId, $days);
                $st->execute();
                $db->commit();
                go('student.php', 'Book borrowed successfully.');
            }
        } catch (mysqli_sql_exception $ex) {
            $db->rollback();
            $error = 'Unable to borrow this book right now. Please try again.';
        }
    }
}

$st = $db->prepare("
    SELECT l.*, b.title, b.author,
           GREATEST(DATEDIFF(CURDATE(), l.due_on), 0) AS days_late
    FROM loans l JOIN books b ON b.id = l.book_id
    WHERE l.student_id = ? AND l.returned_on IS NULL
    ORDER BY l.due_on");
$st->bind_param('i', $sid);
$st->execute();
$current = $st->get_result();

$st = $db->prepare("
    SELECT l.issued_on, l.returned_on, b.title
    FROM loans l JOIN books b ON b.id = l.book_id
    WHERE l.student_id = ? AND l.returned_on IS NOT NULL
    ORDER BY l.returned_on DESC LIMIT 20");
$st->bind_param('i', $sid);
$st->execute();
$history = $st->get_result();

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$st = $db->prepare("SELECT id, title, author, available, copies FROM books WHERE title LIKE ? OR author LIKE ? ORDER BY title");
$st->bind_param('ss', $like, $like);
$st->execute();
$books = $st->get_result();
include 'includes/header.php';
?>
<h1>Welcome, <?= e($_SESSION['name']) ?></h1>

<h2>My Borrowed Books</h2>
<table>
  <tr><th>Title</th><th>Author</th><th>Issued</th><th>Due</th><th>Fine</th></tr>
  <?php if (!$current->num_rows): ?><tr><td colspan="5">You have no borrowed books.</td></tr><?php endif; ?>
  <?php while ($r = $current->fetch_assoc()): ?>
  <tr>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['author']) ?></td>
    <td><?= e($r['issued_on']) ?></td>
    <td class="<?= $r['days_late'] ? 'late' : '' ?>"><?= e($r['due_on']) ?></td>
    <td><?= $r['days_late'] ? e($r['days_late'] * FINE_PER_DAY) : '-' ?></td>
  </tr>
  <?php endwhile; ?>
</table>

<h2 style="margin-top:28px">Browse Books</h2>
<form class="inline" style="margin-bottom:12px">
  <input name="q" placeholder="Search title or author" value="<?= e($q) ?>">
  <button>Search</button>
</form>
<table>
  <tr><th>Title</th><th>Author</th><th>Available</th><th></th></tr>
  <?php while ($b = $books->fetch_assoc()): ?>
  <tr>
    <td><?= e($b['title']) ?></td>
    <td><?= e($b['author']) ?></td>
    <td><?= (int)$b['available'] ?> / <?= (int)$b['copies'] ?></td>
    <td>
      <?php if ((int)$b['available'] > 0): ?>
        <form method="post">
          <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
          <button name="borrow" value="1">Borrow</button>
        </form>
      <?php else: ?>
        Unavailable
      <?php endif; ?>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<p><small>Borrowed books are due in <?= LOAN_DAYS ?> days. Overdue fines are <?= FINE_PER_DAY ?> per day.</small></p>

<h2 style="margin-top:28px">Borrowing History</h2>
<table>
  <tr><th>Title</th><th>Issued</th><th>Returned</th></tr>
  <?php if (!$history->num_rows): ?><tr><td colspan="3">No history yet.</td></tr><?php endif; ?>
  <?php while ($r = $history->fetch_assoc()): ?>
  <tr><td><?= e($r['title']) ?></td><td><?= e($r['issued_on']) ?></td><td><?= e($r['returned_on']) ?></td></tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
