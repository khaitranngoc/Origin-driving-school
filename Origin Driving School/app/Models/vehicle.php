<?php
require_once __DIR__ . '/../Core/DB.php';

class Vehicle {
    public static function all() {
        $pdo = DB::pdo();
        return $pdo->query("
            SELECT v.id, v.rego, v.make, v.model, v.year, v.status, b.name AS branch
            FROM vehicles v
            LEFT JOIN branches b ON v.branch_id = b.id
            ORDER BY v.id DESC
        ")->fetchAll();
    }

    public static function find($id) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($rego, $make, $model, $year, $branchId, $status) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("
            INSERT INTO vehicles (rego,make,model,year,branch_id,status) 
            VALUES (?,?,?,?,?,?)
        ");
        return $stmt->execute([$rego,$make,$model,$year,$branchId,$status]);
    }

    public static function update($id, $rego, $make, $model, $year, $branchId, $status) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("
            UPDATE vehicles SET rego=?, make=?, model=?, year=?, branch_id=?, status=? 
            WHERE id=?
        ");
        return $stmt->execute([$rego,$make,$model,$year,$branchId,$status,$id]);
    }

    public static function delete($id) {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id=?");
        return $stmt->execute([$id]);
    }
}
