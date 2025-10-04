<?php
$pdo = DB::pdo();
$uid = $_SESSION['user_id'] ?? 0;
$cnt = 0;
if ($uid) {
  $st = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
  $st->execute([$uid]);
  $cnt = (int)$st->fetchColumn();
}
?>
<a href="/public/notifications.php" class="nav-bell">
  🔔
  <?php if ($cnt>0): ?><span class="badge danger"><?= $cnt ?></span><?php endif; ?>
</a>
