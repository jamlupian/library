<?php
$public = true;
include 'includes/header.php';
if ($role) require_login($role);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no   = trim($_POST['student_no']);
    $name = trim($_POST['name']);
    if ($no === '' || $name === '') {
        $error = 'Student number and name are required.';
    } elseif (strlen($_POST['password']) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($_POST['password'] !== $_POST['confirm']) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $st = $db->prepare("INSERT INTO students (student_no, name, course, email, password) VALUES (?,?,?,?,?)");
            $st->bind_param('sssss', $no, $name, $_POST['course'], $_POST['email'], $hash);
            $st->execute();
            go('login.php', 'Account created. You can log in now.');
        } catch (mysqli_sql_exception $ex) {
            $error = 'That student number is already registered.';
        }
    }
}
?>
<div class="card auth">
  <h1>Student Registration</h1>
  <form method="post">
    <input name="student_no" placeholder="Student No." value="<?= e($_POST['student_no'] ?? '') ?>" required>
    <input name="name" placeholder="Full name" value="<?= e($_POST['name'] ?? '') ?>" required>
    <input name="course" placeholder="Course" value="<?= e($_POST['course'] ?? '') ?>">
    <input name="email" type="email" placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>">
    <input name="password" type="password" placeholder="Password (min 6 characters)" required>
    <input name="confirm" type="password" placeholder="Confirm password" required>
    <button>Register</button>
  </form>
  <p>Already registered? <a href="login.php">Log in</a></p>
</div>
<?php include 'includes/footer.php'; ?>
