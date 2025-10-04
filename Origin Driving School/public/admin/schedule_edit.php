<?php
session_start();
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
  header('Location: /public/login.php'); exit;
}

$pdo = DB::pdo();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM schedules WHERE id=?");
$stmt->execute([$id]);
$schedule = $stmt->fetch();
if (!$schedule) { echo "Schedule not found"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $instructor = $_POST['instructor_id'];
  $vehicle = $_POST['vehicle_id'] ?: null;
  $branch = $_POST['branch_id'] ?: null;
  $start = $_POST['start_time'];
  $end = $_POST['end_time'];
  $status = $_POST['status'];

  $stmt = $pdo->prepare("UPDATE schedules 
                         SET instructor_id=?, vehicle_id=?, branch_id=?, start_time=?, end_time=?, status=? 
                         WHERE id=?");
  $stmt->execute([$instructor, $vehicle, $branch, $start, $end, $status, $id]);

  header("Location: schedule_board.php"); exit;
}

$instructors = $pdo->query("SELECT id, name FROM users WHERE role='instructor' ORDER BY name")->fetchAll();
$vehicles = $pdo->query("SELECT id, rego FROM vehicles ORDER BY rego")->fetchAll();
$branches = $pdo->query("SELECT id, name FROM branches ORDER BY name")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Schedule #<?= $id ?></title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container">
  <h1>Edit Slot #<?= $id ?></h1>
  <form method="post">
    <label>Instructor</label>
    <select name="instructor_id" required>
      <?php foreach($instructors as $i): ?>
        <option value="<?= $i['id'] ?>" <?= $i['id']==$schedule['instructor_id']?'selected':'' ?>>
          <?= htmlspecialchars($i['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Vehicle</label>
    <select name="vehicle_id">
      <option value="">—</option>
      <?php foreach($vehicles as $v): ?>
        <option value="<?= $v['id'] ?>" <?= $v['id']==$schedule['vehicle_id']?'selected':'' ?>>
          <?= htmlspecialchars($v['rego']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Branch</label>
    <select name="branch_id">
      <option value="">—</option>
      <?php foreach($branches as $b): ?>
        <option value="<?= $b['id'] ?>" <?= $b['id']==$schedule['branch_id']?'selected':'' ?>>
          <?= htmlspecialchars($b['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Start Time</label>
    <input type="datetime-local" name="start_time" value="<?= date('Y-m-d\TH:i', strtotime($schedule['start_time'])) ?>" required>

    <label>End Time</label>
    <input type="datetime-local" name="end_time" value="<?= date('Y-m-d\TH:i', strtotime($schedule['end_time'])) ?>" required>

    <label>Status</label>
    <select name="status">
      <?php foreach(['Open','Reserved','Cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $s==$schedule['status']?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>

    <button class="btn primary">Update</button>
  </form>
</main>
</body>
</html>
