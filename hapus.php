<?php
// TAHAP 0 — Halaman web biasa: menghapus mahasiswa (Delete).
// Hanya menerima POST, sehingga tidak bisa terpicu dari sekadar membuka link.
require __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak didukung.');
}

$st = $pdo->prepare("DELETE FROM mahasiswa WHERE nim = ?");
$st->execute([$_POST['nim'] ?? '']);

$pesan = $st->rowCount() ? 'Data berhasil dihapus.' : 'Data tidak ditemukan.';
header('Location: index.php?pesan=' . urlencode($pesan));
