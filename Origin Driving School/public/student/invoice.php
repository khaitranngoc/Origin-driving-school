<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';
require_once __DIR__ . '/../../app/Models/Invoice.php';
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '')!=='student') { header('Location:/public/login.php'); exit; }
$pdo = DB::pdo();
$studentId = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT i.*, u.name AS student FROM invoices i JOIN users u ON u.id=i.student_id WHERE i.id=? AND i.student_id=? LIMIT 1");
$st->execute([$id,$studentId]);
$row = $st->fetch();
if(!$row){ http_response_code(404); die('Invoice not found'); }
$payments = Invoice::payments($id);
function badgeClass($s){ $s=strtolower((string)$s); if($s==='paid')return'ok'; if($s==='overdue')return'danger'; return 'warn'; }
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Invoice #<?= $id ?></title><link rel="stylesheet" href="/public/assets/app.css">
</head><body>
<header class="header"><div class="container topbar">
  <a class="brand" href="/public/index.php">Origin Driving <span class="badge">AU</span></a>
  <nav class="nav">
    <a href="/public/student/dashboard.php">Dashboard</a>
    <a href="/public/student/invoices.php" aria-current="page">Invoices</a>
    <a href="/public/student/profile.php">Profile</a>
    <a class="btn ghost" href="/public/logout.php">Logout</a>
  </nav>
</div></header>

<main class="container">
  <section class="card">
    <h1>Invoice #<?= $id ?></h1>
    <p><strong>Student:</strong> <?= htmlspecialchars($row['student']) ?></p>
    <p><strong>Total:</strong> $<?= number_format((float)$row['total'],2) ?></p>
    <p><strong>Status:</strong> <span class="badge <?= badgeClass($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></p>
    <p><strong>Due:</strong> <?= htmlspecialchars($row['due_date'] ?? '—') ?></p>
    <p><strong>Created:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
    <p><a class="btn ghost" href="/public/student/invoices.php">Back</a></p>
  </section>

  <section class="card" style="margin-top:1rem">
    <h3>Payments</h3>
    <?php if ($payments): ?>
      <table class="table">
        <thead><tr><th>Amount</th><th>Method</th><th>Reference</th><th>Paid At</th></tr></thead>
        <tbody>
          <?php foreach($payments as $p): ?>
            <tr>
              <td>$<?= number_format((float)$p['amount'],2) ?></td>
              <td><?= htmlspecialchars($p['method']) ?></td>
              <td><?= htmlspecialchars($p['ref'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['paid_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="help">No payments recorded for this invoice.</p>
    <?php endif; ?>
  </section>
</main>
</body></html>
