<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();

/* Revenue by month */
$rev = $pdo->query("
  SELECT DATE_FORMAT(p.paid_at,'%Y-%m') AS ym, SUM(p.amount) AS total
  FROM payments p
  GROUP BY ym
  ORDER BY ym DESC
  LIMIT 12
")->fetchAll();

/* Top 5 instructors by bookings */
$top = $pdo->query("
  SELECT u.name, COUNT(*) AS cnt
  FROM bookings b
  JOIN schedules s ON b.schedule_id=s.id
  JOIN users u ON s.instructor_id=u.id
  WHERE b.status IN ('Confirmed','Completed')
  GROUP BY u.id
  ORDER BY cnt DESC
  LIMIT 5
")->fetchAll();

/* Upcoming bookings */
$upcoming = $pdo->query("
  SELECT COUNT(*) AS cnt
  FROM schedules s
  WHERE s.start_time >= NOW() AND s.start_time <= DATE_ADD(NOW(), INTERVAL 30 DAY)
")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reports — Admin</title>
  <link rel="stylesheet" href="/public/assets/app.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving Admin</a>
    <nav class="nav">
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/admin/student_list.php">Students</a>
      <a href="/public/admin/instructor_list.php">Instructors</a>
      <a href="/public/admin/vehicle_list.php">Vehicles</a>
      <a href="/public/admin/courses_list.php">Courses</a>
      <a href="/public/admin/branch_list.php">Branches</a>
      <a href="/public/admin/invoices_list.php">Invoices</a>
      <a href="/public/admin/schedule_board.php">Scheduling</a>
      <a href="/public/admin/reports.php" aria-current="page">Reports</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Reports</h1>

  <section class="card">
    <h3>Revenue (last 12 months)</h3>
    <canvas id="revChart" height="120"></canvas>
  </section>

  <section class="card" style="margin-top:1rem">
    <h3>Top 5 Instructors by Bookings</h3>
    <ul>
      <?php foreach($top as $t): ?>
        <li><?= htmlspecialchars($t['name']) ?> — <?= (int)$t['cnt'] ?> bookings</li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="card" style="margin-top:1rem">
    <h3>Upcoming Bookings</h3>
    <p><?= (int)$upcoming ?> lessons scheduled in next 30 days</p>
  </section>
</main>

<script>
const ctx = document.getElementById('revChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_column($rev,'ym')) ?>,
    datasets: [{
      label: 'Revenue (AUD)',
      data: <?= json_encode(array_map('floatval',array_column($rev,'total'))) ?>,
      borderColor: '#0077cc',
      fill: false
    }]
  }
});
</script>
</body>
</html>
    