<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();

/* Counters */
$counts = [
  'students'    => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
  'instructors' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn(),
  'vehicles'    => (int)$pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn(),
  'courses'     => (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
  'branches'    => (int)$pdo->query("SELECT COUNT(*) FROM branches")->fetchColumn(),
  'invoices'    => (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
];

/* KPIs */
$kpi_open_slots_today = (int)$pdo->query("
  SELECT COUNT(*) FROM schedules
  WHERE status='Open' AND DATE(start_time)=CURDATE()
")->fetchColumn();

$kpi_bookings_today = (int)$pdo->query("
  SELECT COUNT(*) FROM bookings b
  JOIN schedules s ON s.id=b.schedule_id
  WHERE DATE(s.start_time)=CURDATE()
    AND b.status IN ('Confirmed','Pending')
")->fetchColumn();

$kpi_overdue_invoices = (int)$pdo->query("
  SELECT COUNT(*) FROM invoices
  WHERE (status='Overdue') OR (status='Unpaid' AND due_date IS NOT NULL AND due_date < CURDATE())
")->fetchColumn();

/* Latest bookings */
$latestBookings = $pdo->query("
  SELECT b.id, b.status, s.start_time, s.end_time,
         su.name AS student, iu.name AS instructor
  FROM bookings b
  JOIN schedules s ON b.schedule_id=s.id
  JOIN users su ON b.student_id=su.id
  JOIN users iu ON s.instructor_id=iu.id
  ORDER BY s.start_time DESC
  LIMIT 8
")->fetchAll();

/* Open slots */
$openSlots = $pdo->query("
  SELECT s.id, s.start_time, s.end_time, iu.name AS instructor, br.name AS branch
  FROM schedules s
  JOIN users iu ON iu.id=s.instructor_id
  LEFT JOIN branches br ON br.id=s.branch_id
  WHERE s.status='Open' AND s.start_time>=NOW()
  ORDER BY s.start_time ASC
  LIMIT 8
")->fetchAll();

/* Recent invoices */
$recentInvoices = $pdo->query("
  SELECT i.id, i.total, i.status, i.due_date, u.name AS student
  FROM invoices i
  JOIN users u ON i.student_id = u.id
  ORDER BY i.created_at DESC
  LIMIT 5
")->fetchAll();

/* Helpers */
function badgeClass($s){
  $s=strtolower((string)$s);
  if (in_array($s,['paid','confirmed'])) return 'ok';
  if (in_array($s,['overdue','cancelled'])) return 'danger';
  if (in_array($s,['unpaid','pending','open','reserved'])) return 'warn';
  return '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/admin/dashboard.php">Origin Driving Admin</a>
    <?php include __DIR__ . '/../../app/Views/partials/admin_nav.php'; ?>
  </div>
</header>

<main class="container">
  <section class="hero">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?> 👋</h1>
    <p class="lede">Overview, KPIs and quick links.</p>
  </section>

  <section class="cards">
    <article class="card"><h3>Students</h3><p class="big"><?= number_format($counts['students']) ?></p><a class="btn" href="/public/admin/student_list.php">Manage</a></article>
    <article class="card"><h3>Instructors</h3><p class="big"><?= number_format($counts['instructors']) ?></p><a class="btn" href="/public/admin/instructor_list.php">Manage</a></article>
    <article class="card"><h3>Vehicles</h3><p class="big"><?= number_format($counts['vehicles']) ?></p><a class="btn" href="/public/admin/vehicle_list.php">Manage</a></article>
    <article class="card"><h3>Courses</h3><p class="big"><?= number_format($counts['courses']) ?></p><a class="btn" href="/public/admin/courses_list.php">Manage</a></article>
    <article class="card"><h3>Branches</h3><p class="big"><?= number_format($counts['branches']) ?></p><a class="btn" href="/public/admin/branch_list.php">Manage</a></article>
    <article class="card"><h3>Invoices</h3><p class="big"><?= number_format($counts['invoices']) ?></p><a class="btn" href="/public/admin/invoices_list.php">Manage</a></article>
    <article class="card"><h3>Open Slots Today</h3><p class="big"><?= $kpi_open_slots_today ?></p><a class="btn" href="/public/admin/schedule_board.php">View Board</a></article>
    <article class="card"><h3>Bookings Today</h3><p class="big"><?= $kpi_bookings_today ?></p><a class="btn" href="/public/admin/bookings.php">View All</a></article>
    <article class="card"><h3>Overdue Invoices</h3><p class="big"><?= $kpi_overdue_invoices ?></p><a class="btn" href="/public/admin/invoices_list.php">Review</a></article>
  </section>

  <section class="cards">
    <article class="card">
      <h3>Latest Bookings</h3>
      <?php if ($latestBookings): ?>
      <table class="table">
        <thead>
          <tr><th>#</th><th>When</th><th>Student</th><th>Instructor</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach($latestBookings as $b): ?>
          <tr>
            <td>#<?= (int)$b['id'] ?></td>
            <td><?= date('d M Y H:i', strtotime($b['start_time'])) ?>–<?= date('H:i', strtotime($b['end_time'])) ?></td>
            <td><?= htmlspecialchars($b['student']) ?></td>
            <td><?= htmlspecialchars($b['instructor']) ?></td>
            <td><span class="badge <?= badgeClass($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
            <td>
              <a href="/public/admin/booking_edit.php?id=<?= $b['id'] ?>">Edit</a> |
              <a href="/public/admin/booking_delete.php?id=<?= $b['id'] ?>" onclick="return confirm('Delete booking #<?= $b['id'] ?>?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p style="margin-top:.5rem"><a class="btn ghost" href="/public/admin/bookings.php">View all bookings</a></p>
      <?php else: ?><p class="help">No bookings yet.</p><?php endif; ?>
    </article>

    <article class="card">
      <h3>Upcoming Open Slots</h3>
      <?php if ($openSlots): ?>
      <table class="table">
        <thead><tr><th>When</th><th>Instructor</th><th>Branch</th></tr></thead>
        <tbody>
          <?php foreach($openSlots as $s): ?>
          <tr>
            <td><?= date('d M Y H:i', strtotime($s['start_time'])) ?>–<?= date('H:i', strtotime($s['end_time'])) ?></td>
            <td><?= htmlspecialchars($s['instructor']) ?></td>
            <td><?= htmlspecialchars($s['branch'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p style="margin-top:.5rem"><a class="btn ghost" href="/public/admin/schedule_board.php">Go to scheduling board</a></p>
      <?php else: ?><p class="help">No upcoming open slots.</p><?php endif; ?>
    </article>

    <article class="card">
      <h3>Recent Invoices</h3>
      <?php if ($recentInvoices): ?>
      <table class="table">
        <thead><tr><th>#</th><th>Student</th><th>Total</th><th>Status</th><th>Due</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($recentInvoices as $inv): ?>
          <tr>
            <td>#<?= (int)$inv['id'] ?></td>
            <td><?= htmlspecialchars($inv['student']) ?></td>
            <td>$<?= number_format($inv['total'],2) ?></td>
            <td><span class="badge <?= badgeClass($inv['status']) ?>"><?= htmlspecialchars($inv['status']) ?></span></td>
            <td><?= htmlspecialchars($inv['due_date']) ?></td>
            <td><a href="/public/admin/invoice_view.php?id=<?= (int)$inv['id'] ?>">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p style="margin-top:.5rem"><a class="btn ghost" href="/public/admin/invoices_list.php">View all invoices</a></p>
      <?php else: ?><p class="help">No invoices yet.</p><?php endif; ?>
    </article>
  </section>
</main>
</body>
</html>
