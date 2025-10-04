<?php
final class Notifier {
  public static function add(int $userId, string $type, array $payload): void {
    $pdo = DB::pdo();
    $pdo->prepare("INSERT INTO notifications(user_id,type,payload_json,is_read,created_at) VALUES(?,?,?,?,NOW())")
        ->execute([$userId,$type,json_encode($payload),0]);
  }
}
