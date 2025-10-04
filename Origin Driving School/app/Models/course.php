<?php
require_once __DIR__ . '/../Core/DB.php';

class Course {
    public static function all() {
        $pdo = DB::pdo();
        return $pdo->query("
            SELECT id, title, price, sessions_count, description 
            FROM courses
            ORDER BY id DESC
        ")->fetchAll();
    }

    public static function find($id) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($title,$price,$sessions,$desc) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("INSERT INTO courses (title,price,sessions_count,description) VALUES (?,?,?,?)");
        return $stmt->execute([$title,$price,$sessions,$desc]);
    }

    public static function update($id,$title,$price,$sessions,$desc) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("UPDATE courses SET title=?, price=?, sessions_count=?, description=? WHERE id=?");
        return $stmt->execute([$title,$price,$sessions,$desc,$id]);
    }

    public static function delete($id) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id=?");
        return $stmt->execute([$id]);
    }
}
