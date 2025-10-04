<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$id = (int)($_GET['id'] ?? 0);
$newStatus = $_GET['status'] ?? '';

if ($id && in_array($newStatus, ['Open','Cancelled'])) {
  $stmt = $pdo->prepare("UPDATE schedules SET status=? WHERE id=? AND instructor_id=?");
  $stmt->execute([$newStatus, $id, $_SESSION['user_id']]);
}

header("Location: schedule.php");
exit;
