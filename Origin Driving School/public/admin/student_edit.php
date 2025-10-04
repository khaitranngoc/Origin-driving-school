<?php
session_start();
require_once __DIR__ . '/../../app/Models/Student.php';
require_once __DIR__ . '/../../app/Core/DB.php';
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) { header('Location: /public/login.php'); exit; }
$id = (int)($_GET['id'] ?? 0);
$row = Student::find($id);
if (!$row) { die('Student not found'); }
$pdo = DB::pdo();
$branches = $pdo->query("SELECT id,name FROM branches ORDER BY name")->fetchAll();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $branch= (int)($_POST['branch_id'] ?? 0);
  $pass  = trim($_POST['password'] ?? '');
  $status= trim($_POST['license_status'] ?? '');
  $notes = trim($_POST['notes'] ?? '');
  Student::update($id,$name,$email,$phone,$branch,$pass,$status,$notes);
  header('Location: /public/admin/student_list.php'); exit;
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Student</title><link rel="stylesheet" href="/public/assets/app.css">
</head><body>
<main class="container form-wrap">
<h1>Edit Student</h1>
<form method="post" class="form">
  <div class="field"><label class="label">Name</label><input class="input" name="name" value="<?= htmlspecialchars($row['name']) ?>" required></div>
  <div class="field"><label class="label">Email</label><input class="input" name="email" type="email" value="<?= htmlspecialchars($row['email']) ?>" required></div>
  <div class="field"><label class="label">Phone</label><input class="input" name="phone" value="<?= htmlspecialchars($row['phone']) ?>"></div>
  <div class="field"><label class="label">Branch</label>
    <select class="input" name="branch_id">
      <?php foreach($branches as $b): ?>
        <option value="<?= $b['id'] ?>" <?= $b['id']==$row['branch_id']?'selected':'' ?>><?= htmlspecialchars($b['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field"><label class="label">Password (leave blank to keep)</label><input class="input" name="password" type="text"></div>
  <div class="field"><label class="label">Licence Status</label>
    <select class="input" name="license_status">
      <option value="" <?= ($row['license_status']===''?'selected':'') ?>>—</option>
      <option value="learner" <?= ($row['license_status']==='learner'?'selected':'') ?>>Learner</option>
      <option value="provisional" <?= ($row['license_status']==='provisional'?'selected':'') ?>>Provisional</option>
      <option value="full" <?= ($row['license_status']==='full'?'selected':'') ?>>Full</option>
    </select>
  </div>
  <div class="field"><label class="label">Notes</label><textarea class="input" name="notes"><?= htmlspecialchars($row['notes'] ?? '') ?></textarea></div>
  <button class="btn primary" type="submit">Update</button>
  <a class="btn ghost" href="/public/admin/student_list.php">Cancel</a>
</form>
</main></body></html>
