<?php
require_once __DIR__ . '/../Core/DB.php';

class Invoice {
  public static function all(): array {
    $pdo = DB::pdo();
    return $pdo->query("
      SELECT i.id, i.student_id, i.total, i.status, i.due_date, i.created_at,
             u.name AS student
      FROM invoices i
      JOIN users u ON u.id = i.student_id
      ORDER BY i.created_at DESC
    ")->fetchAll();
  }

  public static function find(int $id): ?array {
    $pdo = DB::pdo();
    $st = $pdo->prepare("
      SELECT i.*, u.name AS student
      FROM invoices i
      JOIN users u ON u.id = i.student_id
      WHERE i.id=?
      LIMIT 1
    ");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function create(int $studentId, float $total, string $status, ?string $dueDate): int {
    $pdo = DB::pdo();
    $st = $pdo->prepare("INSERT INTO invoices(student_id,total,status,due_date,created_at) VALUES(?,?,?,?,NOW())");
    $st->execute([$studentId,$total,$status,$dueDate]);
    return (int)$pdo->lastInsertId();
  }

  public static function update(int $id, int $studentId, float $total, string $status, ?string $dueDate): bool {
    $pdo = DB::pdo();
    $st = $pdo->prepare("UPDATE invoices SET student_id=?, total=?, status=?, due_date=? WHERE id=?");
    return $st->execute([$studentId,$total,$status,$dueDate,$id]);
  }

  public static function delete(int $id): bool {
    $pdo = DB::pdo();
    $pdo->prepare("DELETE FROM payments WHERE invoice_id=?")->execute([$id]);
    $st = $pdo->prepare("DELETE FROM invoices WHERE id=?");
    return $st->execute([$id]);
  }

  public static function payments(int $invoiceId): array {
    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT id, amount, method, paid_at, ref FROM payments WHERE invoice_id=? ORDER BY paid_at DESC");
    $st->execute([$invoiceId]);
    return $st->fetchAll();
  }

  public static function addPayment(int $invoiceId, float $amount, string $method, ?string $ref, ?string $paidAt=null): bool {
    $pdo = DB::pdo();
    $st = $pdo->prepare("INSERT INTO payments(invoice_id,amount,method,paid_at,ref) VALUES(?,?,?,?,?)");
    $ok = $st->execute([$invoiceId,$amount,$method,$paidAt ?? date('Y-m-d H:i:s'),$ref]);
    if ($ok) {
      $sum = (float)$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id=?")
                        ->execute([$invoiceId]) ?: 0;
      $q = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total_paid FROM payments WHERE invoice_id=?");
      $q->execute([$invoiceId]);
      $paid = (float)$q->fetchColumn();
      $q2 = $pdo->prepare("SELECT total FROM invoices WHERE id=?");
      $q2->execute([$invoiceId]);
      $invTotal = (float)$q2->fetchColumn();
      $newStatus = ($paid >= $invTotal && $invTotal > 0) ? 'Paid' : 'Unpaid';
      $pdo->prepare("UPDATE invoices SET status=? WHERE id=?")->execute([$newStatus,$invoiceId]);
    }
    return $ok;
  }
}
