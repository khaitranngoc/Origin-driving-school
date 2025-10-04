<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $instructor = $_POST['instructor_id'];
  $vehicle = $_POST['vehicle_id'] ?: null;
  $branch = $_POST['branch_id'] ?: null;
  $start = $_POST['start_time'];
  $end = $_POST['end_time'];

  $stmt = $pdo->prepare("INSERT INTO schedules (instructor_id, vehicle_id, branch_id, start_time, end_time, status)
                         VALUES (?,?,?,?,?, 'Open')");
  $stmt->execute([$instructor, $vehicle, $branch, $start, $end]);
  header("Location: schedule_board.php"); exit;
}

$instructors = $pdo->query("SELECT id, name FROM users WHERE role='instructor' ORDER BY name")->fetchAll();
$vehicles = $pdo->query("SELECT id, rego FROM vehicles ORDER BY rego")->fetchAll();
$branches = $pdo->query("SELECT id, name FROM branches ORDER BY name")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>New Schedule Slot</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container">
  <h1>Add New Slot</h1>
  <form method="post">
    <label>Instructor</label>
    <select name="instructor_id" required>
      <?php foreach($instructors as $i): ?>
        <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Vehicle</label>
    <select name="vehicle_id">
      <option value="">—</option>
      <?php foreach($vehicles as $v): ?>
        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['rego']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Branch</label>
    <select name="branch_id">
      <option value="">—</option>
      <?php foreach($branches as $b): ?>
        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Start Time</label>
    <input type="datetime-local" name="start_time" required>

    <label>End Time</label>
    <input type="datetime-local" name="end_time" required>

    <button class="btn primary">Save</button>
  </form>
</main>
</body>
</html>
