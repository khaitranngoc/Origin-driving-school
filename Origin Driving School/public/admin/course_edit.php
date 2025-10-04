<?php
session_start();
require_once __DIR__ . '/../../app/Models/Course.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
$course = Course::find($id);
if (!$course) die("Course not found");

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $title = trim($_POST['title'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $sessions = (int)($_POST['sessions_count'] ?? 0);
  $desc = trim($_POST['description'] ?? '');

  Course::update($id,$title,$price,$sessions,$desc);
  header('Location: /public/admin/courses_list.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Course</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container form-wrap">
  <h1>Edit Course</h1>
  <form method="post">
    <div class="field"><label>Title</label><input class="input" name="title" value="<?= htmlspecialchars($course['title']) ?>" required></div>
    <div class="field"><label>Price</label><input class="input" name="price" type="number" step="0.01" value="<?= htmlspecialchars($course['price']) ?>" required></div>
    <div class="field"><label>Sessions</label><input class="input" name="sessions_count" type="number" value="<?= htmlspecialchars($course['sessions_count']) ?>" required></div>
    <div class="field"><label>Description</label><textarea class="input" name="description"><?= htmlspecialchars($course['description']) ?></textarea></div>
    <button class="btn primary" type="submit">Update</button>
    <a class="btn ghost" href="/public/admin/courses_list.php">Cancel</a>
  </form>
</main>
</body>
</html>
