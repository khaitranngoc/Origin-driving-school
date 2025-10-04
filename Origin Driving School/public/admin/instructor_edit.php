<?php
session_start();
require_once __DIR__ . '/../../app/Models/Instuctor.php';
require_once __DIR__ . '/../../app/Core/DB.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) {
  header('Location: /public/login.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
$row = Instructor::find($id);
if (!$row) { die('Instructor not found'); }

$pdo = DB::pdo();
$branches = $pdo->query("SELECT id,name FROM branches ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $branch= (int)($_POST['branch_id'] ?? 0);
  $pass  = trim($_POST['password'] ?? '');

  $quals = trim($_POST['qualifications'] ?? '');
  $rating= $_POST['rating'] !== '' ? (float)$_POST['rating'] : null;
  $cert  = trim($_POST['cert_iv_no'] ?? '');
  $member= trim($_POST['adtav_member_no'] ?? '');

  Instructor::update($id,$name,$email,$phone,$branch,$pass,$quals,$rating,$cert,$member);
  header('Location: /public/admin/instructor_list.php'); exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Instructor</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<main class="container form-wrap">
  <h1>Edit Instructor</h1>
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

    <div class="field"><label class="label">Qualifications</label><input class="input" name="qualifications" value="<?= htmlspecialchars($row['qualifications'] ?? '') ?>"></div>
    <div class="field"><label class="label">Rating</label><input class="input" name="rating" type="number" step="0.1" min="0" max="5" value="<?= htmlspecialchars($row['rating'] ?? '') ?>"></div>
    <div class="field"><label class="label">Cert IV No.</label><input class="input" name="cert_iv_no" value="<?= htmlspecialchars($row['cert_iv_no'] ?? '') ?>"></div>
    <div class="field"><label class="label">ADTAV Member No.</label><input class="input" name="adtav_member_no" value="<?= htmlspecialchars($row['adtav_member_no'] ?? '') ?>"></div>

    <button class="btn primary" type="submit">Update</button>
    <a class="btn ghost" href="/public/admin/instructor_list.php">Cancel</a>
  </form>
</main>
</body>
</html>
