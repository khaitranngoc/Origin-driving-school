<?php
session_start();
require_once __DIR__ . '/../app/Core/DB.php';
require_once __DIR__ . '/../app/Core/csrf.php';

$pdo = DB::pdo();
$title = 'Register — Origin Driving School';
$errors = [];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$branch_id = (int)($_POST['branch_id'] ?? 0);
$license_status = $_POST['license_status'] ?? 'learner';

$branches = $pdo->query("SELECT id,name FROM branches ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'Invalid CSRF token.';
  if ($name==='') $errors[] = 'Name is required.';
  if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
  if (strlen($_POST['password'] ?? '') < 6) $errors[] = 'Password must be at least 6 characters.';
  if (($_POST['password'] ?? '') !== ($_POST['password_confirm'] ?? '')) $errors[] = 'Passwords do not match.';
  if ($branch_id<=0) $errors[] = 'Please choose a branch.';

  if (!$errors) {
    $st = $pdo->prepare("SELECT 1 FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    if ($st->fetch()) $errors[] = 'Email is already registered.';
  }

  if (!$errors) {
    try {
      $pdo->beginTransaction();
      $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
      $st = $pdo->prepare("INSERT INTO users(role,name,email,phone,password_hash,branch_id,created_at) VALUES('student',?,?,?,?,?,NOW())");
      $st->execute([$name,$email,$phone,$hash,$branch_id]);
      $uid = (int)$pdo->lastInsertId();
      $st2 = $pdo->prepare("INSERT INTO students(id,license_status,notes) VALUES(?,?,NULL)");
      $st2->execute([$uid,$license_status]);
      $pdo->commit();
      header('Location: /public/login.php?registered=1'); exit;
    } catch(Throwable $e){
      if ($pdo->inTransaction()) $pdo->rollBack();
      $errors[] = 'Server error. Please try again.';
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
      <a href="/public/login.php">Login</a>
    </nav>
  </div>
</header>

<main class="container form-wrap">
  <div class="auth-header">
    <h1>Create a student account</h1>
    <p class="help">Book lessons, track invoices, get reminders.</p>
  </div>

  <?php if ($errors): ?>
    <div class="notice" style="border-left-color:var(--danger)" role="alert">
      <?= htmlspecialchars(implode(' ', $errors)) ?>
    </div>
  <?php endif; ?>

  <form class="form" method="post" action="/public/register.php" novalidate>
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="field">
      <label class="label" for="name">Full name</label>
      <input class="input" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
    </div>

    <div class="field">
      <label class="label" for="email">Email</label>
      <input class="input" id="email" name="email" type="email" value="<?= htmlspecialchars($email) ?>" autocomplete="email" required>
    </div>

    <div class="field">
      <label class="label" for="phone">Phone</label>
      <input class="input" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" autocomplete="tel">
    </div>

    <div class="field">
      <label class="label" for="branch">Preferred branch</label>
      <select class="input" id="branch" name="branch_id" required>
        <option value="">Choose…</option>
        <?php foreach($branches as $b): ?>
          <option value="<?= (int)$b['id'] ?>" <?= $branch_id===(int)$b['id']?'selected':'' ?>><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label class="label" for="license_status">Licence status</label>
      <select class="input" id="license_status" name="license_status">
        <option value="learner" <?= $license_status==='learner'?'selected':'' ?>>Learner</option>
        <option value="probationary" <?= $license_status==='probationary'?'selected':'' ?>>Probationary</option>
        <option value="full" <?= $license_status==='full'?'selected':'' ?>>Full</option>
        <option value="overseas" <?= $license_status==='overseas'?'selected':'' ?>>Overseas</option>
      </select>
    </div>

    <div class="field">
      <label class="label" for="password">Password</label>
      <input class="input" id="password" name="password" type="password" autocomplete="new-password" required>
    </div>

    <div class="field">
      <label class="label" for="password_confirm">Confirm password</label>
      <input class="input" id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required>
    </div>

    <div class="form-actions">
      <button class="btn primary" type="submit">Create account</button>
      <a class="btn ghost" href="/public/login.php">I already have an account</a>
    </div>
  </form>
</main>

<footer class="footer">
  <div class="container">
    <p class="ack">We pay our respect to Elders past and present.</p>
  </div>
</footer>
<script src="/public/assets/app.js" defer></script>
</body>
</html>
