<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();

$sql = "
  SELECT s.id, s.start_time, s.end_time, s.status,
         i.name AS instructor, v.rego AS vehicle, b.name AS branch
  FROM schedules s
  JOIN users i ON s.instructor_id = i.id
  LEFT JOIN vehicles v ON s.vehicle_id = v.id
  LEFT JOIN branches b ON s.branch_id = b.id
  WHERE s.start_time >= CURDATE()
  ORDER BY s.start_time ASC
  LIMIT 50
";
$schedules = $pdo->query($sql)->fetchAll();

function badgeClass($status) {
  $s = strtolower($status);
  if ($s === 'open') return 'ok';
  if ($s === 'reserved') return 'warn';
  if ($s === 'cancelled') return 'danger';
  return '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Scheduling Board</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving Admin</a>
    <nav class="nav">
      <a href="/public/admin/dashboard.php">Dashboard</a>
      <a href="/public/admin/schedule_board.php" aria-current="page">Schedule Board</a>
      <a href="/public/logout.php" class="btn ghost">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Scheduling Board</h1>
  <p><a class="btn primary" href="schedule_new.php">+ Add Slot</a></p>

  <table class="table">
    <thead>
      <tr><th>#</th><th>When</th><th>Instructor</th><th>Vehicle</th><th>Branch</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach($schedules as $s): ?>
      <tr>
        <td>#<?= $s['id'] ?></td>
        <td><?= date('d M Y H:i', strtotime($s['start_time'])) ?>–<?= date('H:i', strtotime($s['end_time'])) ?></td>
        <td><?= htmlspecialchars($s['instructor']) ?></td>
        <td><?= htmlspecialchars($s['vehicle'] ?? '—') ?></td>
        <td><?= htmlspecialchars($s['branch'] ?? '—') ?></td>
        <td><span class="badge <?= badgeClass($s['status']) ?>"><?= htmlspecialchars($s['status']) ?></span></td>
        <td>
          <a href="schedule_edit.php?id=<?= $s['id'] ?>">Edit</a> |
          <a href="schedule_delete.php?id=<?= $s['id'] ?>" onclick="return confirm('Delete slot #<?= $s['id'] ?>?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
