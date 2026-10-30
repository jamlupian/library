<?php include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $st = $db->prepare("DELETE FROM books WHERE id = ?");
            $st->bind_param('i', $_POST['id']);
            $st->execute();
            go('books.php', 'Book deleted.');
        }
        $copies = max(1, (int)$_POST['copies']);
        $st = $db->prepare("INSERT INTO books (isbn, title, author, copies, available) VALUES (?,?,?,?,?)");
        $st->bind_param('sssii', $_POST['isbn'], $_POST['title'], $_POST['author'], $copies, $copies);
        $st->execute();
        go('books.php', 'Book added.');
    } catch (mysqli_sql_exception $ex) {
        go('books.php', 'Error: ISBN already exists, or book has loan records.');
    }
}

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$st = $db->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? ORDER BY title");
$st->bind_param('sss', $like, $like, $like);
$st->execute();
$rows = $st->get_result();
?>
<h1>Books</h1>

<div class="card">
  <form method="post" class="inline">
    <input name="isbn" placeholder="ISBN" required>
    <input name="title" placeholder="Title" required>
    <input name="author" placeholder="Author" required>
    <input name="copies" type="number" min="1" value="1" style="width:80px">
    <button>Add Book</button>
  </form>
</div>

<form class="inline" style="margin-bottom:12px">
  <input name="q" placeholder="Search title, author or ISBN" value="<?= e($q) ?>">
  <button>Search</button>
</form>

<table>
  <tr><th>ISBN</th><th>Title</th><th>Author</th><th>Available</th><th></th></tr>
  <?php while ($r = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= e($r['isbn']) ?></td>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['author']) ?></td>
    <td><?= $r['available'] ?> / <?= $r['copies'] ?></td>
    <td>
      <form method="post" onsubmit="return confirm('Delete this book?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="danger" name="delete">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
