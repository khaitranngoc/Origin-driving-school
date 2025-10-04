<?php
require_once __DIR__ . '/../Core/DB.php';

class Booking {
  public static function create(int $studentId, int $scheduleId, ?int $courseId, string $status='Pending'): bool {
    $pdo = DB::pdo();
    $pdo->beginTransaction();
    try {
      $chk = $pdo->prepare("SELECT status FROM schedules WHERE id=? FOR UPDATE");
      $chk->execute([$scheduleId]);
      $st = $chk->fetch();
      if (!$st || $st['status']!=='Open') { $pdo->rollBack(); return false; }
      $pdo->prepare("INSERT INTO bookings(student_id,schedule_id,course_id,status,created_at) VALUES(?,?,?,?,NOW())")
          ->execute([$studentId,$scheduleId,$courseId,$status]);
      $pdo->prepare("UPDATE schedules SET status='Reserved' WHERE id=?")->execute([$scheduleId]);
      $pdo->commit();
      return true;
    } catch (Throwable $e) {
      $pdo->rollBack();
      return false;
    }
  }
}
