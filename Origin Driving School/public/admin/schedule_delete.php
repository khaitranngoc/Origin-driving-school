<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$id = (int)($_GET['id'] ?? 0);

if ($id) {
  $stmt = $pdo->prepare("DELETE FROM schedules WHERE id=?");
  $stmt->execute([$id]);
}

header("Location: schedule_board.php");
exit;
