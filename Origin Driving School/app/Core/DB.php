<?php
// /app/Core/DB.php
final class DB {
  private static ?PDO $pdo = null;
  public static function pdo(): PDO {
    if (!self::$pdo) {
      $dsn = 'mysql:host=127.0.0.1;dbname=drivingschool;charset=utf8mb4';
      self::$pdo = new PDO($dsn, 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    }
    return self::$pdo;
  }
}
