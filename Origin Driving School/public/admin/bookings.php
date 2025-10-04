<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$sql = "
  SELECT b.id, b.status, b.created_at,
         s.start_time, s.end_time,
         u.name AS student, i.name AS instructor, c.title AS course
  FROM bookings b
  JOIN schedules s ON b.schedule_id = s.id
  JOIN users u ON b.student_id = u.id
  JOIN users i ON s.instructor_id = i.id
  JOIN courses c ON b.course_id = c.id
  ORDER BY s.start_time DESC
";
$bookings = $pdo->query($sql)->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Bookings</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving Admin</a>
    <nav class="nav">
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/admin/bookings.php" aria-current="page">Bookings</a>
      <a href="/public/logout.php" class="btn ghost">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Bookings</h1>
  <table class="table">
    <thead>
      <tr><th>ID</th><th>Student</th><th>Instructor</th><th>Course</th><th>Time</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach($bookings as $b): ?>
        <tr>
          <td><?= (int)$b['id'] ?></td>
          <td><?= htmlspecialchars($b['student']) ?></td>
          <td><?= htmlspecialchars($b['instructor']) ?></td>
          <td><?= htmlspecialchars($b['course']) ?></td>
          <td><?= date('d M Y H:i', strtotime($b['start_time'])) ?></td>
          <td><?= htmlspecialchars($b['status']) ?></td>
          <td>
            <a href="booking_edit.php?id=<?= $b['id'] ?>">Edit</a> |
            <a href="booking_delete.php?id=<?= $b['id'] ?>" onclick="return confirm('Delete booking #<?= $b['id'] ?>?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
