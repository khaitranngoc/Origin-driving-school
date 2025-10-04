<?php
session_start();
require_once __DIR__ . '/../../app/Models/Instuctor.php';
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$rows = Instructor::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Instructors — Admin</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/admin/instructor_list.php" aria-current="page">Instructors</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="hero">
    <h1>Instructors</h1>
    <a class="btn primary" href="/public/admin/instructor_new.php">+ Add Instructor</a>
  </section>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Branch</th>
        <th>Rating</th><th>Qualifications</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td>#<?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td><?= htmlspecialchars($r['phone']) ?></td>
          <td><?= htmlspecialchars($r['branch'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['rating'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['qualifications'] ?? '') ?></td>
          <td>
            <a href="/public/admin/instructor_edit.php?id=<?= $r['id'] ?>">Edit</a> |
            <a href="/public/admin/instructor_delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete this instructor?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
