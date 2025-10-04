<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/Core/DB.php';
require_once __DIR__ . '/../app/Core/csrf.php';

$error = '';
$registered = isset($_GET['registered']);
$title = "Login — Origin Driving School";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) {
    $error = 'Invalid CSRF token.';
  } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
      $pdo = DB::pdo();
      $stmt = $pdo->prepare("SELECT id, role, name, password_hash FROM users WHERE email = ? LIMIT 1");
      $stmt->execute([$email]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];

        if ($user['role'] === 'student') {
          header("Location: /public/student/dashboard.php"); exit;
        } elseif ($user['role'] === 'instructor') {
          header("Location: /public/instructor/dashboard.php"); exit;
        } elseif ($user['role'] === 'admin' || $user['role'] === 'staff') {
          header("Location: /public/admin/dashboard.php"); exit;
        } else {
          header("Location: /public/index.php"); exit;
        }
      } else {
        $error = 'Invalid email or password.';
      }
    } catch (Throwable $e) {
      $error = $e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="/public/assets/app.css">
  <link rel="icon" href="/public/assets/favicon.ico">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/index.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/index.php">Home</a>
      <a href="/public/courses.php">Courses</a>
      <a href="/public/register.php">Register</a>
    </nav>
  </div>
</header>

<main class="container form-wrap">
  <div class="auth-header">
    <h1>Welcome back</h1>
    <p class="help">Log in to book lessons, view invoices, and check messages.</p>
  </div>

  <?php if($registered): ?>
    <div class="notice" role="status">Registration successful. Please log in.</div>
  <?php endif; ?>
  <?php if($error): ?>
    <div class="notice" style="border-left-color:var(--danger)" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form class="form" method="post" action="/public/login.php">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <div class="field">
      <label class="label" for="email">Email</label>
      <input class="input" id="email" name="email" type="email" autocomplete="username" required>
    </div>
    <div class="field">
      <label class="label" for="password">Password</label>
      <input class="input" id="password" name="password" type="password" autocomplete="current-password" required>
    </div>
    <div class="form-actions">
      <button class="btn primary" type="submit">Log in</button>
      <a class="btn ghost" href="/public/index.php">Back</a>
    </div>
  </form>

  <p class="help" style="text-align:center;margin-top:.8rem">
    New here? <a href="/public/register.php">Create a student account</a>.
  </p>
</main>

<footer class="footer">
  <div class="container">
    <p class="ack">We pay our respect to Elders past and present.</p>
  </div>
</footer>
<script src="/public/assets/app.js" defer></script>
</body>
</html>
