<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT b.*, s.start_time, s.end_time, c.title AS course
  FROM bookings b
  JOIN schedules s ON b.schedule_id = s.id
  JOIN courses c ON b.course_id = c.id
  WHERE b.id=?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if(!$booking) { echo "Booking not found"; exit; }

if($_SERVER['REQUEST_METHOD']==='POST') {
  $status = $_POST['status'] ?? $booking['status'];
  $st = $pdo->prepare("UPDATE bookings SET status=? WHERE id=?");
  $st->execute([$status, $id]);
  header("Location: bookings.php"); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Booking</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container">
  <h1>Edit Booking #<?= $id ?></h1>
  <p>Course: <?= htmlspecialchars($booking['course']) ?> | 
     Time: <?= date('d M Y H:i', strtotime($booking['start_time'])) ?></p>

  <form method="post">
    <label>Status</label>
    <select name="status" required>
      <?php foreach(['Pending','Confirmed','Cancelled','Completed'] as $opt): ?>
        <option value="<?= $opt ?>" <?= $opt==$booking['status']?'selected':'' ?>><?= $opt ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn primary">Update</button>
  </form>
</main>
</body>
</html>
