<?php
session_start();
require_once __DIR__ . '/../../app/Models/Branch.php';
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) { header('Location:/public/login.php'); exit; }
$rows = Branch::all();
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Branches — Admin</title><link rel="stylesheet" href="/public/assets/app.css">
</head><body>
<header class="header"><div class="container topbar">
  <a class="brand" href="/public/admin/dashboard.php">Origin Driving <span class="badge">AU</span></a>
  <nav class="nav">
    <a href="/public/admin/dashboard.php">Dashboard</a>
    <a href="/public/admin/branch_list.php" aria-current="page">Branches</a>
    <a class="btn ghost" href="/public/logout.php">Logout</a>
  </nav>
</div></header>

<main class="container">
  <section class="hero">
    <h1>Branches</h1>
    <a class="btn primary" href="/public/admin/branch_new.php">+ Add Branch</a>
  </section>

  <table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Suburb</th><th>State</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($rows as $b): ?>
      <tr>
        <td>#<?= (int)$b['id'] ?></td>
        <td><?= htmlspecialchars($b['name']) ?></td>
        <td><?= htmlspecialchars($b['suburb']) ?></td>
        <td><span class="badge"><?= htmlspecialchars($b['state']) ?></span></td>
        <td>
          <a href="/public/admin/branch_edit.php?id=<?= $b['id'] ?>">Edit</a> |
          <a href="/public/admin/branch_delete.php?id=<?= $b['id'] ?>" onclick="return confirm('Delete this branch?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body></html>
