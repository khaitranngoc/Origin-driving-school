<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$iid = (int)$_SESSION['user_id'];

$kpi_today_classes = (int)$pdo->query("
  SELECT COUNT(*) FROM bookings b
  JOIN schedules s ON b.schedule_id=s.id
  WHERE s.instructor_id={$iid}
    AND DATE(s.start_time)=CURDATE()
    AND b.status IN ('Confirmed','Pending')
")->fetchColumn();

$kpi_open_slots = (int)$pdo->query("
  SELECT COUNT(*) FROM schedules
  WHERE instructor_id={$iid} AND status='Open' AND start_time>=NOW()
")->fetchColumn();

$kpi_upcoming = (int)$pdo->query("
  SELECT COUNT(*) FROM schedules
  WHERE instructor_id={$iid} AND status IN ('Open','Reserved') AND start_time>=NOW()
")->fetchColumn();

$todayClasses = $pdo->query("
  SELECT b.id AS booking_id, s.start_time, s.end_time,
         u.name AS student, s.status AS slot_status, b.status AS booking_status
  FROM bookings b
  JOIN schedules s ON b.schedule_id=s.id
  JOIN users u ON b.student_id=u.id
  WHERE s.instructor_id={$iid} AND DATE(s.start_time)=CURDATE()
  ORDER BY s.start_time ASC
")->fetchAll();

$upcomingSlots = $pdo->query("
  SELECT id, start_time, end_time, status
  FROM schedules
  WHERE instructor_id={$iid} AND start_time>=NOW()
  ORDER BY start_time ASC
  LIMIT 8
")->fetchAll();

$notices = $pdo->query("
  SELECT id, type, payload_json, is_read, created_at
  FROM notifications
  WHERE user_id={$iid}
  ORDER BY created_at DESC
  LIMIT 5
")->fetchAll();

function badgeClass($s){
  $s=strtolower((string)$s);
  if ($s==='confirmed' || $s==='paid') return 'ok';
  if ($s==='pending' || $s==='open' || $s==='reserved') return 'warn';
  if ($s==='overdue' || $s==='cancelled') return 'danger';
  return '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Instructor Dashboard</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/instructor/dashboard.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/instructor/dashboard.php" aria-current="page">Dashboard</a>
      <a href="/public/instructor/schedule.php">My Schedule</a>
       <?php include __DIR__ . '/../../app/Views/partials/notifications_bell.php'; ?>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="hero">
    <h1>G’day, <?= htmlspecialchars($_SESSION['name'] ?? 'Instructor') ?> 👋</h1>
    <p class="lede">Your teaching overview.</p>
  </section>

  <section class="cards">
    <article class="card">
      <h3>Today’s Classes</h3>
      <p style="font-size:2rem;font-weight:700"><?= number_format($kpi_today_classes) ?></p>
    </article>
    <article class="card">
      <h3>Open Slots</h3>
      <p style="font-size:2rem;font-weight:700"><?= number_format($kpi_open_slots) ?></p>
    </article>
    <article class="card">
      <h3>Upcoming (All)</h3>
      <p style="font-size:2rem;font-weight:700"><?= number_format($kpi_upcoming) ?></p>
    </article>
  </section>

  <section class="cards">
    <article class="card">
      <h3>Today’s Schedule</h3>
      <?php if ($todayClasses): ?>
      <table class="table">
        <thead><tr><th>Time</th><th>Student</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach($todayClasses as $c): ?>
          <tr>
            <td><?= date('H:i', strtotime($c['start_time'])) ?>–<?= date('H:i', strtotime($c['end_time'])) ?></td>
            <td><?= htmlspecialchars($c['student']) ?></td>
            <td>
              <span class="badge <?= badgeClass($c['booking_status'] ?? $c['slot_status']) ?>">
                <?= htmlspecialchars($c['booking_status'] ?? $c['slot_status']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="help">No classes today.</p>
      <?php endif; ?>
    </article>

    <article class="card">
      <h3>Upcoming Slots</h3>
      <?php if ($upcomingSlots): ?>
      <table class="table">
        <thead><tr><th>When</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach($upcomingSlots as $s): ?>
          <tr>
            <td><?= date('d M Y H:i', strtotime($s['start_time'])) ?>–<?= date('H:i', strtotime($s['end_time'])) ?></td>
            <td><span class="badge <?= badgeClass($s['status']) ?>"><?= htmlspecialchars($s['status']) ?></span></td>
            <td><a href="/public/instructor/schedule.php">Manage</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="help">No upcoming slots. Create one now.</p>
      <?php endif; ?>
    </article>

    <article class="card">
      <h3>Notifications</h3>
      <?php if ($notices): ?>
        <?php foreach($notices as $n): $msg = json_decode($n['payload_json'], true)['text'] ?? $n['type']; ?>
          <div class="notice"><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="help">No notifications.</p>
      <?php endif; ?>
    </article>
  </section>
</main>
</body>
</html>
