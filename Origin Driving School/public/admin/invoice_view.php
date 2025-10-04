<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT i.*, u.name AS student
  FROM invoices i
  JOIN users u ON i.student_id=u.id
  WHERE i.id=? LIMIT 1
");
$stmt->execute([$id]);
$inv = $stmt->fetch();

if (!$inv) { echo "Invoice not found"; exit; }

$items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id=?");
$items->execute([$id]);
$items = $items->fetchAll();

$payments = $pdo->prepare("SELECT * FROM payments WHERE invoice_id=? ORDER BY paid_at ASC");
$payments->execute([$id]);
$payments = $payments->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Invoice #<?= $id ?></title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container">
  <h1>Invoice #<?= $id ?></h1>
  <p>Student: <?= htmlspecialchars($inv['student']) ?></p>
  <p>Status: <?= htmlspecialchars($inv['status']) ?> | Total: $<?= number_format($inv['total'],2) ?></p>
  <p>Due: <?= htmlspecialchars($inv['due_date']) ?></p>

  <h3>Items</h3>
  <ul>
    <?php foreach($items as $it): ?>
      <li><?= htmlspecialchars($it['description']) ?> · <?= $it['qty'] ?> × $<?= number_format($it['unit_price'],2) ?></li>
    <?php endforeach; ?>
  </ul>

  <h3>Payments</h3>
  <?php if($payments): ?>
    <ul>
      <?php foreach($payments as $p): ?>
        <li>$<?= number_format($p['amount'],2) ?> via <?= htmlspecialchars($p['method']) ?> (<?= $p['paid_at'] ?>)</li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="help">No payments recorded.</p>
  <?php endif; ?>

  <p><a href="invoices_list.php">Back</a></p>
</main>
</body>
</html>
