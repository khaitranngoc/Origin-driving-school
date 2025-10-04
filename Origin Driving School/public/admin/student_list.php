<?php
session_start();
require_once __DIR__ . '/../../app/Models/Student.php';
require_once __DIR__ . '/../../app/Core/DB.php';
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) { header('Location: /public/login.php'); exit; }
$rows = Student::all();
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Students — Admin</title><link rel="stylesheet" href="/public/assets/app.css">
</head><body>
<header class="header"><div class="container topbar">
<a class="brand" href="/public/admin/dashboard.php">Origin Driving <span class="badge">AU</span></a>
<nav class="nav">
  <a href="/public/admin/dashboard.php">Dashboard</a>
  <a href="/public/admin/student_list.php" aria-current="page">Students</a>
  <a class="btn ghost" href="/public/logout.php">Logout</a>
</nav></div></header>

<main class="container">
<section class="hero">
  <h1>Students</h1>
  <a class="btn primary" href="/public/admin/student_new.php">+ Add Student</a>
</section>

<table class="table">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Branch</th><th>Licence</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td>#<?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars($r['phone']) ?></td>
        <td><?= htmlspecialchars($r['branch'] ?? '—') ?></td>
        <td><?= htmlspecialchars($r['license_status'] ?? '—') ?></td>
        <td>
          <a href="/public/admin/student_edit.php?id=<?= $r['id'] ?>">Edit</a> |
          <a href="/public/admin/student_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete this student?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</main></body></html>
