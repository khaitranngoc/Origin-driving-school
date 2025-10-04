<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || $_SESSION['role']!=='student') {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$studentId = (int)$_SESSION['user_id'];

/* Lấy danh sách invoices */
$stmt = $pdo->prepare("
  SELECT i.id, i.total, i.status, i.due_date, i.created_at,
         GROUP_CONCAT(ii.description SEPARATOR ', ') AS items
  FROM invoices i
  LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
  WHERE i.student_id=?
  GROUP BY i.id
  ORDER BY i.created_at DESC
");
$stmt->execute([$studentId]);
$invoices = $stmt->fetchAll();

function badgeClass($status) {
  if (strcasecmp($status,'Paid')===0) return 'ok';
  if (strcasecmp($status,'Overdue')===0) return 'danger';
  return 'warn';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Invoices</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/index.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/student/dashboard.php">Dashboard</a>
      <a href="/public/student/book.php">Book Lesson</a>
      <a href="/public/student/invoices.php" aria-current="page">Invoices</a>
      <a href="/public/student/profile.php">Profile</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>My Invoices</h1>

  <?php if(isset($_GET['booked'])): ?>
    <div class="notice" role="status">
      Booking confirmed. A new invoice has been generated.
    </div>
  <?php endif; ?>

  <?php if(!$invoices): ?>
    <p class="help">No invoices found.</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr><th>#</th><th>Items</th><th>Total</th><th>Status</th><th>Due</th></tr>
      </thead>
      <tbody>
        <?php foreach($invoices as $inv): ?>
          <tr>
            <td><?= (int)$inv['id'] ?></td>
            <td><?= htmlspecialchars($inv['items'] ?? '') ?></td>
            <td>$<?= number_format($inv['total'],2) ?></td>
            <td><span class="badge <?= badgeClass($inv['status']) ?>"><?= htmlspecialchars($inv['status']) ?></span></td>
            <td><?= htmlspecialchars($inv['due_date']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</main>
</body>
</html>
