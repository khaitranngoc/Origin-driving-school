<?php
require_once __DIR__ . '/../Core/DB.php';

class Instructor {
  public static function all(): array {
    $pdo = DB::pdo();
    return $pdo->query("
      SELECT u.id, u.name, u.email, u.phone, u.branch_id, b.name AS branch,
             i.qualifications, i.rating, i.cert_iv_no, i.adtav_member_no
      FROM users u
      LEFT JOIN instructors i ON i.id = u.id
      LEFT JOIN branches b ON b.id = u.branch_id
      WHERE u.role = 'instructor'
      ORDER BY u.id DESC
    ")->fetchAll();
  }

  public static function find(int $id): ?array {
    $pdo = DB::pdo();
    $st = $pdo->prepare("
      SELECT u.id, u.name, u.email, u.phone, u.branch_id,
             i.qualifications, i.rating, i.cert_iv_no, i.adtav_member_no
      FROM users u
      LEFT JOIN instructors i ON i.id = u.id
      WHERE u.id = ? AND u.role = 'instructor' LIMIT 1
    ");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function create(string $name, string $email, string $phone, ?int $branchId,
                                string $passwordPlain,
                                ?string $quals, ?float $rating, ?string $cert, ?string $member): int {
    $pdo = DB::pdo();
    $pdo->beginTransaction();
    try {
      $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
      $st = $pdo->prepare("INSERT INTO users(role,name,email,phone,password_hash,branch_id,created_at)
                           VALUES('instructor',?,?,?,?,?,NOW())");
      $st->execute([$name,$email,$phone,$hash,$branchId]);
      $id = (int)$pdo->lastInsertId();
      $st2 = $pdo->prepare("INSERT INTO instructors(id,qualifications,rating,cert_iv_no,adtav_member_no)
                            VALUES(?,?,?,?,?)");
      $st2->execute([$id,$quals,$rating,$cert,$member]);
      $pdo->commit();
      return $id;
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function update(int $id, string $name, string $email, string $phone, ?int $branchId,
                                ?string $passwordPlain,
                                ?string $quals, ?float $rating, ?string $cert, ?string $member): bool {
    $pdo = DB::pdo();
    $pdo->beginTransaction();
    try {
      if ($passwordPlain !== null && $passwordPlain !== '') {
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $st = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, branch_id=?, password_hash=? WHERE id=? AND role='instructor'");
        $st->execute([$name,$email,$phone,$branchId,$hash,$id]);
      } else {
        $st = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, branch_id=? WHERE id=? AND role='instructor'");
        $st->execute([$name,$email,$phone,$branchId,$id]);
      }
      $st2 = $pdo->prepare("UPDATE instructors SET qualifications=?, rating=?, cert_iv_no=?, adtav_member_no=? WHERE id=?");
      $st2->execute([$quals,$rating,$cert,$member,$id]);
      $pdo->commit();
      return true;
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }

  public static function delete(int $id): bool {
    $pdo = DB::pdo();
    $pdo->beginTransaction();
    try {
      $pdo->prepare("DELETE FROM instructors WHERE id=?")->execute([$id]);
      $pdo->prepare("DELETE FROM users WHERE id=? AND role='instructor'")->execute([$id]);
      $pdo->commit();
      return true;
    } catch (Throwable $e) {
      $pdo->rollBack();
      throw $e;
    }
  }
}
