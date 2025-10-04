<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';
require_once __DIR__ . '/../../app/Core/csrf.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$errors = [];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'Invalid CSRF token.';
  $student_id = (int)($_POST['student_id'] ?? 0);
  $total = (float)($_POST['total'] ?? 0);
  $due_date = $_POST['due_date'] ?? '';

  if ($student_id<=0) $errors[] = 'Select a student.';
  if ($total<=0) $errors[] = 'Enter a valid amount.';
  if (!$due_date) $errors[] = 'Due date required.';

  if (!$errors) {
    $st = $pdo->prepare("INSERT INTO invoices(student_id,total,status,due_date,created_at) VALUES(?,?,?, ?,NOW())");
    $st->execute([$student_id,$total,'Unpaid',$due_date]);
    header("Location: /public/admin/invoices.php"); exit;
  }
}

$students = $pdo->query("SELECT id,name,email FROM users WHERE role='student' ORDER BY name")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>New Invoice</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving Admin</a>
    <nav class="nav">
      <a href="/public/admin/invoices.php" aria-current="page">Invoices</a>
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/logout.php" class="btn ghost">Logout</a>
    </nav>
  </div>
</header>

<main class="container form-wrap">
  <h1>Create New Invoice</h1>

  <?php if($errors): ?>
    <div class="notice" style="border-left-color:var(--danger)">
      <?= htmlspecialchars(implode(' ', $errors)) ?>
    </div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="field">
      <label class="label" for="student">Student</label>
      <select class="input" id="student" name="student_id" required>
        <option value="">Select…</option>
        <?php foreach($students as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name'])." ({$s['email']})" ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label class="label" for="total">Total Amount</label>
      <input class="input" type="number" step="0.01" name="total" id="total" required>
    </div>

    <div class="field">
      <label class="label" for="due_date">Due Date</label>
      <input class="input" type="date" name="due_date" id="due_date" required>
    </div>

    <div class="form-actions">
      <button class="btn primary" type="submit">Save Invoice</button>
      <a href="/public/admin/invoices.php" class="btn ghost">Cancel</a>
    </div>
  </form>
</main>
</body>
</html>
