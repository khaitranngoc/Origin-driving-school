<?php
session_start();
require_once __DIR__ . '/../../app/Models/Branch.php';
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) { header('Location:/public/login.php'); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = trim($_POST['name'] ?? '');
  $suburb = trim($_POST['suburb'] ?? '');
  $state = trim($_POST['state'] ?? 'VIC');
  Branch::create($name,$suburb,$state);
  header('Location:/public/admin/branch_list.php'); exit;
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Branch</title><link rel="stylesheet" href="/public/assets/app.css">
</head><body>
<main class="container form-wrap">
  <h1>Add Branch</h1>
  <form method="post" class="form">
    <div class="field"><label class="label">Name</label><input class="input" name="name" required></div>
    <div class="field"><label class="label">Suburb</label><input class="input" name="suburb" required></div>
    <div class="field"><label class="label">State</label>
      <select class="input" name="state">
        <option value="VIC">VIC</option><option value="NSW">NSW</option>
        <option value="QLD">QLD</option><option value="SA">SA</option>
        <option value="WA">WA</option><option value="TAS">TAS</option>
        <option value="ACT">ACT</option><option value="NT">NT</option>
      </select>
    </div>
    <button class="btn primary" type="submit">Save</button>
    <a class="btn ghost" href="/public/admin/branch_list.php">Cancel</a>
  </form>
</main>
</body></html>
