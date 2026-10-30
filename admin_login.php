<?php
require_once 'config.php';
$public = true;
$role = $_SESSION['role'] ?? null;
if ($role) require_login($role);

seed_admin($db);   // creates admin / adminlibrary on first run

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($username) || trim($username) === '' || !is_string($password) || $password === '') {
        $error = 'Please enter both your username and password.';
    } else {
        $username = trim($username);
        $st = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $st->bind_param('s', $username);
        $st->execute();
        $u = $st->get_result()->fetch_assoc();

        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $u['username'];
            header('Location: index.php'); exit;
        }
        $error = 'Username or password is incorrect.';
    }
}
include 'includes/header.php';
?>
<div class="card auth">
  <h1>Admin Login</h1>
  <form method="post">
    <input name="username" placeholder="Username" required autofocus>
    <input name="password" type="password" placeholder="Password" required>
    <button>Log in</button>
  </form>
  <p><a href="login.php">← Student login</a></p>
</div>
<?php include 'includes/footer.php'; ?>
