<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';
require_once __DIR__ . '/../../app/Core/csrf.php';

if (empty($_SESSION['user_id']) || $_SESSION['role']!=='student') {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$studentId = (int)$_SESSION['user_id'];
$errors = [];

$courses = $pdo->query("SELECT id,title,price FROM courses ORDER BY id")->fetchAll();
$schedules = $pdo->query("
  SELECT s.id, s.start_time, s.end_time, u.name AS instructor, v.rego
  FROM schedules s
  JOIN users u ON s.instructor_id=u.id
  LEFT JOIN vehicles v ON v.id=s.vehicle_id
  WHERE s.status='Open' AND s.start_time>=NOW()
  ORDER BY s.start_time ASC
  LIMIT 20
")->fetchAll();

/* Xử lý submit booking */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'Invalid CSRF token.';
  $course_id   = (int)($_POST['course_id'] ?? 0);
  $schedule_id = (int)($_POST['schedule_id'] ?? 0);

  if ($course_id<=0) $errors[] = 'Select a course.';
  if ($schedule_id<=0) $errors[] = 'Select a schedule.';

  if (!$errors) {
    try {
      $pdo->beginTransaction();

      // tạo booking
      $pdo->prepare("INSERT INTO bookings(student_id,schedule_id,course_id,status,created_at)
                     VALUES(?,?,?,?,NOW())")
          ->execute([$studentId,$schedule_id,$course_id,'Confirmed']);
      $bookingId = (int)$pdo->lastInsertId();

      // update schedule status
      $pdo->prepare("UPDATE schedules SET status='Reserved' WHERE id=?")
          ->execute([$schedule_id]);

      // lấy course price
      $st = $pdo->prepare("SELECT title, price FROM courses WHERE id=?");
      $st->execute([$course_id]);
      $course = $st->fetch();

      if ($course) {
        // tạo invoice
        $due = date('Y-m-d', strtotime('+7 days'));
        $pdo->prepare("INSERT INTO invoices(student_id,total,status,due_date,created_at)
                       VALUES(?,?,?,?,NOW())")
            ->execute([$studentId,$course['price'],'Unpaid',$due]);
        $invoiceId = (int)$pdo->lastInsertId();

        // chi tiết invoice item
        $pdo->prepare("INSERT INTO invoice_items(invoice_id,description,qty,unit_price)
                       VALUES(?,?,?,?)")
            ->execute([$invoiceId,$course['title'],1,$course['price']]);
      }

      $pdo->commit();
      header("Location: /public/student/invoices.php?booked=1"); exit;
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $errors[] = 'Booking failed: '.$e->getMessage();
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Book Lesson</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/index.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/student/dashboard.php">Dashboard</a>
      <a href="/public/student/book.php" aria-current="page">Book Lesson</a>
      <a href="/public/student/invoices.php">Invoices</a>
      <a href="/public/student/profile.php">Profile</a>
      <a class="btn ghost" href="/public/logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container form-wrap">
  <h1>Book a Lesson</h1>

  <?php if($errors): ?>
    <div class="notice" style="border-left-color:var(--danger)">
      <?= htmlspecialchars(implode(' ', $errors)) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="form">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

    <div class="field">
      <label class="label" for="course">Select Course</label>
      <select class="input" name="course_id" id="course" required>
        <option value="">Choose…</option>
        <?php foreach($courses as $c): ?>
          <option value="<?= $c['id'] ?>">
            <?= htmlspecialchars($c['title']) ?> — $<?= number_format($c['price'],2) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label class="label" for="schedule">Select Schedule</label>
      <select class="input" name="schedule_id" id="schedule" required>
        <option value="">Choose…</option>
        <?php foreach($schedules as $s): ?>
          <option value="<?= $s['id'] ?>">
            <?= date('D d M H:i', strtotime($s['start_time'])) ?> —
            <?= date('H:i', strtotime($s['end_time'])) ?> |
            <?= htmlspecialchars($s['instructor']) ?> |
            <?= htmlspecialchars($s['rego'] ?? 'No car') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-actions">
      <button class="btn primary" type="submit">Confirm Booking</button>
      <a class="btn ghost" href="/public/student/dashboard.php">Cancel</a>
    </div>
  </form>
</main>
</body>
</html>
