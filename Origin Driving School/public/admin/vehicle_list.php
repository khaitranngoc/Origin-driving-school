<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$vehicles = $pdo->query("
  SELECT v.id, v.rego, v.make, v.model, v.year, v.status, b.name AS branch
  FROM vehicles v
  LEFT JOIN branches b ON v.branch_id = b.id
  ORDER BY v.id DESC
")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Vehicles — Admin</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/admin/vehicle_list.php" aria-current="page">Vehicles</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="hero">
    <h1>Vehicles</h1>
    <p class="lede">Manage all training vehicles.</p>
    <a class="btn primary" href="/public/admin/vehicle_new.php">+ Add Vehicle</a>
  </section>

  <table class="table">
    <thead><tr><th>ID</th><th>Rego</th><th>Make</th><th>Model</th><th>Year</th><th>Status</th><th>Branch</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($vehicles as $v): ?>
      <tr>
        <td>#<?= (int)$v['id'] ?></td>
        <td><?= htmlspecialchars($v['rego']) ?></td>
        <td><?= htmlspecialchars($v['make']) ?></td>
        <td><?= htmlspecialchars($v['model']) ?></td>
        <td><?= htmlspecialchars($v['year']) ?></td>
        <td><span class="badge"><?= htmlspecialchars($v['status']) ?></span></td>
        <td><?= htmlspecialchars($v['branch'] ?? '—') ?></td>
        <td>
          <a href="/public/admin/vehicle_edit.php?id=<?= $v['id'] ?>">Edit</a> |
          <a href="/public/admin/vehicle_delete.php?id=<?= $v['id'] ?>" onclick="return confirm('Delete this vehicle?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
