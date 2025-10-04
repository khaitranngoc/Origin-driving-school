<?php
session_start();
require_once __DIR__ . '/../../app/Models/Course.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$courses = Course::all();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Courses — Admin</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/admin/courses_list.php" aria-current="page">Courses</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="hero">
    <h1>Courses</h1>
    <a class="btn primary" href="/public/admin/course_new.php">+ Add Course</a>
  </section>

  <table class="table">
    <thead><tr><th>ID</th><th>Title</th><th>Price</th><th>Sessions</th><th>Description</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($courses as $c): ?>
      <tr>
        <td>#<?= $c['id'] ?></td>
        <td><?= htmlspecialchars($c['title']) ?></td>
        <td>$<?= number_format($c['price'],2) ?></td>
        <td><?= $c['sessions_count'] ?></td>
        <td><?= htmlspecialchars($c['description']) ?></td>
        <td>
          <a href="/public/admin/course_edit.php?id=<?= $c['id'] ?>">Edit</a> |
          <a href="/public/admin/course_delete.php?id=<?= $c['id'] ?>" onclick="return confirm('Delete this course?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
