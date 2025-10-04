<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
  header('Location: /public/login.php');
  exit;
}

$name = $_SESSION['name'] ?? 'Student';
$studentId = (int)$_SESSION['user_id'];

$pdo = DB::pdo();

/* Upcoming lessons */
$sql = "
  SELECT sc.start_time, sc.end_time,
         iu.name AS instructor, br.name AS branch, v.rego AS vehicle
  FROM bookings b
  JOIN schedules sc     ON b.schedule_id = sc.id
  JOIN users iu         ON sc.instructor_id = iu.id
  LEFT JOIN branches br ON sc.branch_id = br.id
  LEFT JOIN vehicles v  ON sc.vehicle_id = v.id
  WHERE b.student_id = ?
    AND sc.start_time >= NOW()
    AND b.status IN ('Confirmed','Pending')
  ORDER BY sc.start_time ASC
  LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute([$studentId]);
$lessons = $stmt->fetchAll();

/* Invoices */
$stmt = $pdo->prepare("
  SELECT id, due_date, total, status
  FROM invoices
  WHERE student_id = ?
  ORDER BY created_at DESC
  LIMIT 5
");
$stmt->execute([$studentId]);
$invoices = $stmt->fetchAll();

/* Notifications */
$stmt = $pdo->prepare("
  SELECT id, type, payload_json, is_read, created_at
  FROM notifications
  WHERE user_id = ?
  ORDER BY created_at DESC
  LIMIT 5
");
$stmt->execute([$studentId]);
$notices = $stmt->fetchAll();

function badgeClass($status) {
  if (strcasecmp($status,'Paid')===0) return 'ok';
  if (strcasecmp($status,'Overdue')===0) return 'danger';
  return 'warn';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/index.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/student/dashboard.php" aria-current="page">Dashboard</a>
      <a href="/public/student/book.php">Book Lesson</a>
      <a href="/public/student/invoices.php">Invoices</a>
      <a href="/public/student/profile.php">Profile</a>
        <?php include __DIR__ . '/../../app/Views/partials/notifications_bell.php'; ?>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="hero">
    <h1>G’day, <?= htmlspecialchars($name) ?> 👋</h1>
    <p class="lede">Here’s what’s coming up for you.</p>
  </section>

  <section class="cards">
    <article class="card">
      <h3>Next lesson</h3>
      <?php if(!empty($lessons)): $n=$lessons[0]; ?>
        <p><strong><?= date('D, d M Y', strtotime($n['start_time'])) ?></strong>
           · <?= date('H:i', strtotime($n['start_time'])) ?>–<?= date('H:i', strtotime($n['end_time'])) ?></p>
        <p>Instructor: <?= htmlspecialchars($n['instructor']) ?></p>
        <p>Branch: <?= htmlspecialchars($n['branch'] ?? '—') ?> · Vehicle: <?= htmlspecialchars($n['vehicle'] ?? '—') ?></p>
      <?php else: ?>
        <p class="help">No upcoming lessons.</p>
      <?php endif; ?>
    </article>

    <article class="card">
      <h3>Invoices</h3>
      <?php if($invoices): foreach($invoices as $inv): ?>
        <div style="display:flex;justify-content:space-between;margin:.4rem 0;align-items:center">
          <div>
            <strong>#<?= (int)$inv['id'] ?></strong> · Due <?= htmlspecialchars($inv['due_date'] ?? '—') ?>
            <div class="help">$<?= number_format((float)$inv['total'],2) ?></div>
          </div>
          <span class="badge <?= badgeClass($inv['status']) ?>"><?= htmlspecialchars($inv['status']) ?></span>
        </div>
      <?php endforeach; else: ?>
        <p class="help">No invoices yet.</p>
      <?php endif; ?>
    </article>

    <article class="card">
      <h3>Notifications</h3>
      <?php if($notices): foreach($notices as $n):
        $payload = json_decode($n['payload_json'] ?? '', true);
        $text = $payload['text'] ?? $n['type'];
      ?>
        <div class="notice"><?= htmlspecialchars($text) ?></div>
      <?php endforeach; else: ?>
        <p class="help">No notifications.</p>
      <?php endif; ?>
    </article>
  </section>
</main>
</body>
</html>
