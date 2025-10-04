<?php
session_start();
require_once __DIR__ . '/../../app/Models/Branch.php';
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','staff'], true)) { header('Location:/public/login.php'); exit; }
$id = (int)($_GET['id'] ?? 0);
$row = Branch::find($id);
if (!$row) { die('Branch not found'); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = trim($_POST['name'] ?? '');
  $suburb = trim($_POST['suburb'] ?? '');
  $state = trim($_POST['state'] ?? 'VIC');
  Branch::update($id,$name,$suburb,$state);
  header('Location:/public/admin/branch_list.php'); exit;
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Branch</title><link rel="stylesheet" href="/public/assets/app.css">
</head><body>
<main class="container form-wrap">
  <h1>Edit Branch</h1>
  <form method="post" class="form">
    <div class="field"><label class="label">Name</label><input class="input" name="name" value="<?= htmlspecialchars($row['name']) ?>" required></div>
    <div class="field"><label class="label">Suburb</label><input class="input" name="suburb" value="<?= htmlspecialchars($row['suburb']) ?>" required></div>
    <div class="field"><label class="label">State</label>
      <select class="input" name="state">
        <?php
          $states=['VIC','NSW','QLD','SA','WA','TAS','ACT','NT'];
          foreach($states as $st){
            $sel = ($row['state']===$st)?'selected':'';
            echo "<option value=\"$st\" $sel>$st</option>";
          }
        ?>
      </select>
    </div>
    <button class="btn primary" type="submit">Update</button>
    <a class="btn ghost" href="/public/admin/branch_list.php">Cancel</a>
  </form>
</main>
</body></html>
