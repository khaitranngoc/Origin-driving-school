<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$instructorId = (int)$_SESSION['user_id'];

/* Lấy slot của instructor + kèm booking (nếu có) */
$sql = "
  SELECT s.id, s.start_time, s.end_time, s.status AS slot_status,
         b.id AS booking_id, b.status AS booking_status,
         stu.name AS student_name,
         br.name AS branch, v.rego AS vehicle
  FROM schedules s
  LEFT JOIN bookings b ON b.schedule_id = s.id
  LEFT JOIN users stu   ON b.student_id = stu.id
  LEFT JOIN branches br ON br.id = s.branch_id
  LEFT JOIN vehicles v  ON v.id  = s.vehicle_id
  WHERE s.instructor_id = ?
  ORDER BY s.start_time DESC
  LIMIT 50
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$instructorId]);
$rows = $stmt->fetchAll();

function badge($s) {
  $s = strtolower((string)$s);
  if (in_array($s, ['confirmed','open'])) return 'ok';
  if (in_array($s, ['pending','reserved'])) return 'warn';
  if (in_array($s, ['cancelled'])) return 'danger';
  return '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Schedule</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/instructor/dashboard.php">Instructor Portal</a>
    <nav class="nav">
      <a href="/public/instructor/dashboard.php">Dashboard</a>
      <a href="/public/instructor/schedule.php" aria-current="page">My Schedule</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>My Schedule</h1>

  <table class="table">
    <thead>
      <tr>
        <th>#</th><th>When</th><th>Branch</th><th>Vehicle</th>
        <th>Student</th><th>Booking</th><th>Slot</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td>#<?= (int)$r['id'] ?></td>
          <td><?= date('d M Y H:i', strtotime($r['start_time'])) ?>–<?= date('H:i', strtotime($r['end_time'])) ?></td>
          <td><?= htmlspecialchars($r['branch'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['vehicle'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['student_name'] ?? '—') ?></td>
          <td>
            <?php if ($r['booking_id']): ?>
              <span class="badge <?= badge($r['booking_status']) ?>">
                <?= htmlspecialchars($r['booking_status']) ?>
              </span>
            <?php else: ?>
              <span class="help">No booking</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?= badge($r['slot_status']) ?>">
              <?= htmlspecialchars($r['slot_status']) ?>
            </span>
          </td>
          <td>
            <?php if ($r['booking_id'] && $r['booking_status']==='Pending'): ?>
              <a class="btn ok" href="/public/instructor/booking_update.php?id=<?= $r['booking_id'] ?>&action=accept">Accept</a>
              <a class="btn warn" href="/public/instructor/booking_update.php?id=<?= $r['booking_id'] ?>&action=decline"
                 onclick="return confirm('Decline this booking?')">Decline</a>
            <?php elseif (!$r['booking_id']): ?>
              <?php if ($r['slot_status']==='Open'): ?>
                <a class="btn warn" href="/public/instructor/schedule_toggle.php?id=<?= $r['id'] ?>&status=Cancelled"
                   onclick="return confirm('Cancel this open slot?')">Cancel slot</a>
              <?php elseif ($r['slot_status']==='Cancelled'): ?>
                <a class="btn ok" href="/public/instructor/schedule_toggle.php?id=<?= $r['id'] ?>&status=Open">Reopen</a>
              <?php endif; ?>
            <?php else: ?>
              <!-- Booking đã Confirmed/Reserved → chỉ xem -->
              <span class="help">Booked</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
</body>
</html>
