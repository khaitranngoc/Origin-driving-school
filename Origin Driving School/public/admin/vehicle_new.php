<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$branches = $pdo->query("SELECT id,name FROM branches ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $rego = trim($_POST['rego'] ?? '');
  $make = trim($_POST['make'] ?? '');
  $model= trim($_POST['model'] ?? '');
  $year = (int)($_POST['year'] ?? 0);
  $status= trim($_POST['status'] ?? 'available');
  $branch = (int)($_POST['branch_id'] ?? 0);

  $stmt = $pdo->prepare("INSERT INTO vehicles (rego,make,model,year,branch_id,status) VALUES (?,?,?,?,?,?)");
  $stmt->execute([$rego,$make,$model,$year,$branch,$status]);
  header('Location: /public/admin/vehicle_list.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Vehicle</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container form-wrap">
  <h1>Add Vehicle</h1>
  <form method="post">
    <div class="field"><label>Rego</label><input class="input" name="rego" required></div>
    <div class="field"><label>Make</label><input class="input" name="make" required></div>
    <div class="field"><label>Model</label><input class="input" name="model" required></div>
    <div class="field"><label>Year</label><input class="input" name="year" type="number" required></div>
    <div class="field"><label>Branch</label>
      <select class="input" name="branch_id">
        <?php foreach($branches as $b): ?>
          <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>Status</label>
      <select class="input" name="status">
        <option value="available">Available</option>
        <option value="unavailable">Unavailable</option>
      </select>
    </div>
    <button class="btn primary" type="submit">Save</button>
    <a class="btn ghost" href="/public/admin/vehicle_list.php">Cancel</a>
  </form>
</main>
</body>
</html>
