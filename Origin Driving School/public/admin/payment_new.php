<?php
session_start();
require_once __DIR__ . '/../../app/Models/Invoice.php';
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) { header('Location:/public/login.php'); exit; }
$invoiceId = (int)($_GET['invoice_id'] ?? $_POST['invoice_id'] ?? 0);
$row = $invoiceId ? Invoice::find($invoiceId) : null;
if (!$row) { die('Invoice not found'); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $amount = (float)($_POST['amount'] ?? 0);
  $method = trim($_POST['method'] ?? 'cash');
  $ref = trim($_POST['ref'] ?? '');
  $paidAt = $_POST['paid_at'] !== '' ? $_POST['paid_at'] : null;
  Invoice::addPayment($invoiceId,$amount,$method,$ref,$paidAt);
  header("Location:/public/admin/invoice_edit.php?id=".$invoiceId); exit;
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Payment — Invoice #<?= $invoiceId ?></title><link rel="stylesheet" href="/public/assets/app.css">
</head><body>
<main class="container form-wrap">
  <h1>Add Payment — Invoice #<?= $invoiceId ?></h1>
  <p class="help">Student: <?= htmlspecialchars($row['student']) ?> · Total: $<?= number_format((float)$row['total'],2) ?> · Status: <?= htmlspecialchars($row['status']) ?></p>
  <form method="post" class="form">
    <input type="hidden" name="invoice_id" value="<?= $invoiceId ?>">
    <div class="field"><label class="label">Amount</label><input class="input" name="amount" type="number" step="0.01" required></div>
    <div class="field"><label class="label">Method</label>
      <select class="input" name="method">
        <option value="cash">Cash</option>
        <option value="card">Card</option>
        <option value="bank">Bank Transfer</option>
      </select>
    </div>
    <div class="field"><label class="label">Reference</label><input class="input" name="ref" placeholder="Receipt / TXN ID"></div>
    <div class="field"><label class="label">Paid At</label><input class="input" name="paid_at" type="datetime-local"></div>
    <button class="btn primary" type="submit">Add Payment</button>
    <a class="btn ghost" href="/public/admin/invoice_edit.php?id=<?= $invoiceId ?>">Cancel</a>
  </form>
</main>
</body></html>
