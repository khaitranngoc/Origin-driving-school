<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id=?");
$stmt->execute([$id]);
$inv = $stmt->fetch();
if(!$inv) { echo "Invoice not found"; exit; }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $status = $_POST['status'] ?? $inv['status'];
  $st = $pdo->prepare("UPDATE invoices SET status=? WHERE id=?");
  $st->execute([$status, $id]);

  if($status === 'Paid'){
    $amount = $inv['total'];
    $st = $pdo->prepare("INSERT INTO payments(invoice_id,amount,method,paid_at,ref) VALUES(?,?,?,?,?)");
    $st->execute([$id, $amount, $_POST['method'] ?? 'manual', date('Y-m-d H:i:s'), uniqid('pay_')]);
  }

  header("Location: invoices_list.php"); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Invoice #<?= $id ?></title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container">
  <h1>Edit Invoice #<?= $id ?></h1>
  <form method="post">
    <label>Status</label>
    <select name="status">
      <?php foreach(['Unpaid','Paid','Overdue','Cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $s===$inv['status']?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>

    <label>Payment Method (if Paid)</label>
    <input type="text" name="method" value="cash">

    <button class="btn primary" type="submit">Save</button>
  </form>
</main>
</body>
</html>
