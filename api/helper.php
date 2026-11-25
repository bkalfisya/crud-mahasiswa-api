<?php
// Fungsi bantu API: mengirim jawaban, membaca body, dan memeriksa token.

/** Mengirim jawaban JSON dengan status code, lalu menghentikan program. */
function kirim(int $status, $isi = null): void {
    http_response_code($status);
    if ($isi !== null) {
        echo json_encode($isi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
    exit;
}

/** Membaca body request sebagai JSON. Body rusak atau bukan objek → 400. */
function bacaBody(): array {
    $mentah = file_get_contents('php://input');
    $data   = json_decode($mentah, true);
    if (!is_array($data) || array_is_list($data) && $data !== []) {
        kirim(400, ['error' => 'Body harus berupa objek JSON yang valid.']);
    }
    return $data;
}

/** Mengambil token dari header "Authorization: Bearer <token>". */
function ambilToken(): ?string {
    $h = $_SERVER['HTTP_AUTHORIZATION']
      ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
      ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '');
    return preg_match('/^Bearer\s+(\S+)$/i', $h, $m) ? $m[1] : null;
}

/**
 * Memastikan pemanggil sudah login.
 * Tanpa token / token salah / kedaluwarsa → 401.
 * Login tetapi perannya tidak termasuk $peranDiizinkan → 403.
 */
function wajibLogin(PDO $pdo, array $peranDiizinkan = []): array {
    $token = ambilToken();
    if ($token === null) {
        kirim(401, ['error' => 'Token tidak ada. Login dulu lewat POST /api/login.']);
    }
    $st = $pdo->prepare("SELECT id, username, peran, token_kedaluwarsa FROM pengguna WHERE token = ?");
    $st->execute([$token]);
    $u = $st->fetch();
    if (!$u || strtotime($u['token_kedaluwarsa']) < time()) {
        kirim(401, ['error' => 'Token tidak valid atau sudah kedaluwarsa.']);
    }
    if ($peranDiizinkan && !in_array($u['peran'], $peranDiizinkan, true)) {
        kirim(403, ['error' => 'Peran Anda tidak berhak melakukan tindakan ini.']);
    }
    return $u;
}