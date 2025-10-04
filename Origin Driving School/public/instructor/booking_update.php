<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$bookingId = (int)($_GET['id'] ?? 0);
$action    = $_GET['action'] ?? '';

if (!$bookingId || !in_array($action, ['accept','decline'], true)) {
  header('Location: /public/instructor/schedule.php'); exit;
}

/* Lấy booking + schedule để kiểm tra quyền */
$stmt = $pdo->prepare("
  SELECT b.id, b.status, b.schedule_id, s.instructor_id
  FROM bookings b
  JOIN schedules s ON s.id = b.schedule_id
  WHERE b.id = ?
  LIMIT 1
");
$stmt->execute([$bookingId]);
$bk = $stmt->fetch();

if (!$bk || (int)$bk['instructor_id'] !== (int)$_SESSION['user_id']) {
  header('Location: /public/instructor/schedule.php'); exit;
}

if ($action === 'accept') {
  // Instructor xác nhận: booking -> Confirmed, slot -> Reserved
  $pdo->prepare("UPDATE bookings SET status='Confirmed' WHERE id=?")->execute([$bookingId]);
  $pdo->prepare("UPDATE schedules SET status='Reserved' WHERE id=?")->execute([(int)$bk['schedule_id']]);
} else {
  // Decline: booking -> Cancelled, slot mở lại
  $pdo->prepare("UPDATE bookings SET status='Cancelled' WHERE id=?")->execute([$bookingId]);
  $pdo->prepare("UPDATE schedules SET status='Open' WHERE id=?")->execute([(int)$bk['schedule_id']]);
}

header('Location: /public/instructor/schedule.php');
exit;
