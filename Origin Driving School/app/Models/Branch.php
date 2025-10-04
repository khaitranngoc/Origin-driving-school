<?php
require_once __DIR__ . '/../Core/DB.php';

class Branch {
  public static function all(): array {
    $pdo = DB::pdo();
    return $pdo->query("SELECT id,name,suburb,state FROM branches ORDER BY name")->fetchAll();
  }
  public static function find(int $id): ?array {
    $pdo = DB::pdo();
    $st = $pdo->prepare("SELECT id,name,suburb,state FROM branches WHERE id=?");
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ?: null;
  }
  public static function create(string $name, string $suburb, string $state): bool {
    $pdo = DB::pdo();
    $st = $pdo->prepare("INSERT INTO branches(name,suburb,state) VALUES(?,?,?)");
    return $st->execute([$name,$suburb,$state]);
  }
  public static function update(int $id, string $name, string $suburb, string $state): bool {
    $pdo = DB::pdo();
    $st = $pdo->prepare("UPDATE branches SET name=?, suburb=?, state=? WHERE id=?");
    return $st->execute([$name,$suburb,$state,$id]);
  }
  public static function delete(int $id): bool {
    $pdo = DB::pdo();
    $st = $pdo->prepare("DELETE FROM branches WHERE id=?");
    return $st->execute([$id]);
  }
}
