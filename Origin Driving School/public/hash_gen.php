<?php
// Ví dụ chạy: http://localhost/public/hash_gen.php?p=instructor123
$pass = $_GET['p'] ?? 'admin123';
$hash = password_hash($pass, PASSWORD_DEFAULT);

header('Content-Type: text/plain; charset=utf-8');
echo "Plain: $pass\n";
echo "Hash : $hash\n";
