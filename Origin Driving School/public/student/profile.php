<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
  header('Location: /public/login.php');
  exit;
}

$pdo = DB::pdo();
$studentId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name, email, phone, created_at FROM users WHERE id=?");
$stmt->execute([$studentId]);
$user = $stmt->fetch();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Profile</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/index.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/student/dashboard.php">Dashboard</a>
      <a href="/public/student/book.php">Book Lesson</a>
      <a href="/public/student/invoices.php">Invoices</a>
      <a href="/public/student/profile.php" aria-current="page">Profile</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="hero">
    <h1>My Profile</h1>
    <p class="lede">Your account details at Origin Driving School.</p>
  </section>

  <section class="card">
    <?php if ($user): ?>
      <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? '—') ?></p>
      <p><strong>Member since:</strong> <?= date('d M Y', strtotime($user['created_at'])) ?></p>
    <?php else: ?>
      <p class="help">No profile data found.</p>
    <?php endif; ?>
  </section>
</main>

<footer class="footer">
  <div class="container">
    <p class="ack">We pay our respect to Elders past and present.</p>
  </div>
</footer>
</body>
</html>
