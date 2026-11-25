<?php
// =====================================================================
// API Data Mahasiswa — pintu masuk tunggal (front controller).
// Semua request ke /api/... diarahkan ke sini oleh .htaccess.
// =====================================================================

// Jawaban selalu JSON; error PHP tidak boleh tampil ke klien
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
set_exception_handler(function (Throwable $e) {
    error_log('[API] ' . $e->getMessage());   // detail disimpan di log server
    http_response_code(500);
    echo json_encode(['error' => 'Terjadi kesalahan pada server.']);
});

// Izinkan API dipanggil dari aplikasi di domain/port lain (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Expose-Headers: Location');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require __DIR__ . '/../koneksi.php';     // dipakai ulang dari proyek web
require __DIR__ . '/../validasi.php';    // dipakai ulang dari proyek web
require __DIR__ . '/helper.php';
require __DIR__ . '/mahasiswa.php';
require __DIR__ . '/auth.php';

// ---- Routing: baca method dan alamat ----
$metode = $_SERVER['REQUEST_METHOD'];
$base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');   // mis. /crud-mahasiswa/api
define('BASE_API', $base);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, $base) === 0) {
    $path = substr($path, strlen($base));
}
$seg = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));

// contoh: /api/mahasiswa/2205101001  ->  $seg = ['mahasiswa', '2205101001']
$resource = $seg[0] ?? '';
$id       = isset($seg[1]) ? urldecode($seg[1]) : null;
if (count($seg) > 2) {
    kirim(404, ['error' => 'Endpoint tidak ditemukan.']);
}

// GET /api — keterangan singkat, untuk memastikan API sudah jalan
if ($resource === '' && $metode === 'GET') {
    kirim(200, [
        'nama'     => 'API Data Mahasiswa',
        'versi'    => '1.0',
        'endpoint' => [
            'POST /api/login', 'POST /api/logout',
            'GET /api/mahasiswa', 'GET /api/mahasiswa/{nim}',
            'POST /api/mahasiswa', 'PUT /api/mahasiswa/{nim}', 'DELETE /api/mahasiswa/{nim}',
        ],
    ]);
}

if ($resource === 'login' && $id === null) {
    if ($metode === 'POST') login($pdo);
    kirim(405, ['error' => 'Metode tidak didukung. Gunakan POST.']);
}

if ($resource === 'logout' && $id === null) {
    if ($metode === 'POST') logout($pdo);
    kirim(405, ['error' => 'Metode tidak didukung. Gunakan POST.']);
}

if ($resource === 'mahasiswa') {
    if ($id === null) {                         // /api/mahasiswa
        if ($metode === 'GET')  daftarMahasiswa($pdo);
        if ($metode === 'POST') tambahMahasiswa($pdo);
    } else {                                    // /api/mahasiswa/{nim}
        if ($metode === 'GET')    detailMahasiswa($pdo, $id);
        if ($metode === 'PUT')    ubahMahasiswa($pdo, $id);
        if ($metode === 'DELETE') hapusMahasiswa($pdo, $id);
    }
    kirim(405, ['error' => 'Metode tidak didukung untuk alamat ini.']);
}

kirim(404, ['error' => 'Endpoint tidak ditemukan.']);