<?php
session_start();
require_once __DIR__ . '/../app/Core/DB.php';
if (empty($_SESSION['user_id'])) { header('Location: /public/login.php'); exit; }

$pdo = DB::pdo();
$uid = (int)$_SESSION['user_id'];

if (isset($_GET['read'])) {
  $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([(int)$_GET['read'],$uid]);
  header('Location: notifications.php'); exit;
}

$rows = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$rows->execute([$uid]);
$rows = $rows->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Notifications</title>
  <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>
<header class="header">
  <div class="container topbar">
    <a class="brand" href="/public/index.php">Origin Driving <span class="badge">AU</span></a>
    <nav class="nav">
      <a href="/public/index.php">Home</a>
      <a href="/public/notifications.php" aria-current="page">Notifications</a>
      <a href="/public/logout.php" class="btn ghost">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Your Notifications</h1>
  <?php foreach($rows as $n): 
    $data = json_decode($n['payload_json'],true) ?: [];
    $msg = $data['text'] ?? $n['type'];
  ?>
    <div class="notice <?= $n['is_read']?'':'unread' ?>">
      <?= htmlspecialchars($msg) ?>
      <small><?= htmlspecialchars($n['created_at']) ?></small>
      <?php if(!$n['is_read']): ?>
        <a class="btn ghost" href="?read=<?= $n['id'] ?>">Mark read</a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</main>
</body>
</html>
