<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();

$stmt = $pdo->query("
  SELECT i.id, i.total, i.status, i.due_date, u.name AS student
  FROM invoices i
  JOIN users u ON i.student_id=u.id
  ORDER BY i.created_at DESC
");
$invoices = $stmt->fetchAll();

function badgeClass($s) {
  $s = strtolower($s);
  if ($s === 'paid') return 'ok';
  if ($s === 'overdue') return 'danger';
  if ($s === 'cancelled') return 'danger';
  return 'warn';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Invoices</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving Admin</a>
    <nav class="nav">
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/admin/invoices_list.php" aria-current="page">Invoices</a>
      <a href="/public/logout.php" class="btn ghost">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Invoices</h1>
  <table class="table">
    <thead>
      <tr><th>#</th><th>Student</th><th>Total</th><th>Status</th><th>Due</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach($invoices as $inv): ?>
      <tr>
        <td>#<?= $inv['id'] ?></td>
        <td><?= htmlspecialchars($inv['student']) ?></td>
        <td>$<?= number_format($inv['total'],2) ?></td>
        <td><span class="badge <?= badgeClass($inv['status']) ?>"><?= htmlspecialchars($inv['status']) ?></span></td>
        <td><?= htmlspecialchars($inv['due_date']) ?></td>
        <td>
          <a href="invoice_view.php?id=<?= $inv['id'] ?>">View</a> |
          <a href="invoice_edit.php?id=<?= $inv['id'] ?>">Edit</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
