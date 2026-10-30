<?php include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $st = $db->prepare("DELETE FROM students WHERE id = ?");
            $st->bind_param('i', $_POST['id']);
            $st->execute();
            go('students.php', 'Student deleted.');
        }
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $st = $db->prepare("INSERT INTO students (student_no, name, course, email, password) VALUES (?,?,?,?,?)");
        $st->bind_param('sssss', $_POST['student_no'], $_POST['name'], $_POST['course'], $_POST['email'], $hash);
        $st->execute();
        go('students.php', 'Student added.');
    } catch (mysqli_sql_exception $ex) {
        go('students.php', 'Error: student number already exists, or student has loan records.');
    }
}

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$st = $db->prepare("SELECT * FROM students WHERE name LIKE ? OR student_no LIKE ? ORDER BY name");
$st->bind_param('ss', $like, $like);
$st->execute();
$rows = $st->get_result();
?>
<h1>Students</h1>

<div class="card">
  <form method="post" class="inline">
    <input name="student_no" placeholder="Student No." required>
    <input name="name" placeholder="Full name" required>
    <input name="course" placeholder="Course">
    <input name="email" type="email" placeholder="Email">
    <input name="password" type="password" placeholder="Password (min 6)" minlength="6" required>
    <button>Add Student</button>
  </form>
</div>

<form class="inline" style="margin-bottom:12px">
  <input name="q" placeholder="Search name or number" value="<?= e($q) ?>">
  <button>Search</button>
</form>

<table>
  <tr><th>Student No.</th><th>Name</th><th>Course</th><th>Email</th><th></th></tr>
  <?php while ($r = $rows->fetch_assoc()): ?>
  <tr>
    <td><?= e($r['student_no']) ?></td>
    <td><?= e($r['name']) ?></td>
    <td><?= e($r['course']) ?></td>
    <td><?= e($r['email']) ?></td>
    <td>
      <form method="post" onsubmit="return confirm('Delete this student?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="danger" name="delete">Delete</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>
