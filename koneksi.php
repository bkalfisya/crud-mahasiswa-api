<?php
// Koneksi basis data. Sesuaikan bila pengaturan MySQL Anda berbeda
// (XAMPP bawaan: user root, tanpa password).
$DB_HOST = 'localhost';
$DB_NAME = 'db_kampus';
$DB_USER = 'root';
$DB_PASS = '';

$pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);
