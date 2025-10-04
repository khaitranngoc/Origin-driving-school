<?php
session_start();
require_once __DIR__ . '/../../app/Models/Course.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
  Course::delete($id);
}
header('Location: /public/admin/courses_list.php');
exit;
