<?php
require_once 'config.php';
$public = true;
$role = $_SESSION['role'] ?? null;
if ($role) require_login($role);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNo = $_POST['student_no'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($studentNo) || trim($studentNo) === '' || !is_string($password) || $password === '') {
        $error = 'Please enter both your student ID and password.';
    } else {
        $studentNo = trim($studentNo);
        $st = $db->prepare("SELECT * FROM students WHERE student_no = ?");
        $st->bind_param('s', $studentNo);
        $st->execute();
        $u = $st->get_result()->fetch_assoc();

        if ($u && !empty($u['password']) && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['role'] = 'student';
            $_SESSION['student_id'] = $u['id'];
            $_SESSION['name'] = $u['name'];
            header('Location: student.php'); exit;
        }
        $error = 'Student ID or password is incorrect.';
    }
}
include 'includes/header.php';
?>
<div class="card auth">
  <h1>Student Login</h1>
  <form method="post">
    <input name="student_no" placeholder="Student ID" value="<?= e($_POST['student_no'] ?? '') ?>" required autofocus>
    <input name="password" type="password" placeholder="Password" required>
    <button>Log in</button>
  </form>
  <p>No account? <a href="register.php">Register here</a></p>
  <p><a href="admin_login.php">Admin login →</a></p>
</div>
<?php include 'includes/footer.php'; ?>
