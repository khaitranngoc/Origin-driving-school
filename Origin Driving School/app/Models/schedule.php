<?php
require_once __DIR__ . '/../Core/DB.php';

class Schedule {
  public static function byInstructor(int $instructorId): array {
    $pdo = DB::pdo();
    $st = $pdo->prepare("
      SELECT s.*, b.name AS branch, v.rego AS vehicle
      FROM schedules s
      LEFT JOIN branches b ON b.id=s.branch_id
      LEFT JOIN vehicles v ON v.id=s.vehicle_id
      WHERE s.instructor_id=?
      ORDER BY s.start_time DESC
    ");
    $st->execute([$instructorId]);
    return $st->fetchAll();
  }

  public static function create(int $instructorId, ?int $vehicleId, ?int $branchId, string $start, string $end, string $status='Open'): bool {
    $pdo = DB::pdo();
    $st = $pdo->prepare("INSERT INTO schedules(instructor_id,vehicle_id,branch_id,start_time,end_time,status) VALUES(?,?,?,?,?,?)");
    return $st->execute([$instructorId,$vehicleId,$branchId,$start,$end,$status]);
  }

  public static function setStatus(int $id, string $status): bool {
    $pdo = DB::pdo();
    $st = $pdo->prepare("UPDATE schedules SET status=? WHERE id=?");
    return $st->execute([$status,$id]);
  }

  public static function openSlots(?int $branchId=null, ?int $instructorId=null, ?string $from=null, ?string $to=null): array {
    $pdo = DB::pdo();
    $sql = "
      SELECT s.id, s.start_time, s.end_time, u.name AS instructor, b.name AS branch, v.rego AS vehicle
      FROM schedules s
      JOIN users u ON u.id=s.instructor_id
      LEFT JOIN branches b ON b.id=s.branch_id
      LEFT JOIN vehicles v ON v.id=s.vehicle_id
      WHERE s.status='Open' AND s.start_time>=NOW()
    ";
    $p=[]; 
    if ($branchId) { $sql.=" AND s.branch_id=?"; $p[]=$branchId; }
    if ($instructorId) { $sql.=" AND s.instructor_id=?"; $p[]=$instructorId; }
    if ($from) { $sql.=" AND s.start_time>=?"; $p[]=$from; }
    if ($to) { $sql.=" AND s.start_time<=?"; $p[]=$to; }
    $sql.=" ORDER BY s.start_time ASC LIMIT 50";
    $st = $pdo->prepare($sql);
    $st->execute($p);
    return $st->fetchAll();
  }
}
