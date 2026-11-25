<?php
// Autentikasi: login menghasilkan token, logout menghapusnya.

/** POST /api/login  body: {"username": "...", "password": "..."} */
function login(PDO $pdo): void {
    $in = bacaBody();
    $username = $in['username'] ?? '';
    $password = $in['password'] ?? '';
    if (!is_string($username) || !is_string($password) || $username === '' || $password === '') {
        kirim(400, ['error' => 'Username dan password wajib diisi.']);
    }
    $st = $pdo->prepare("SELECT * FROM pengguna WHERE username = ?");
    $st->execute([$username]);
    $u = $st->fetch();

    if (!$u || !password_verify($password, $u['password_hash'])) {
        kirim(401, ['error' => 'Username atau password salah.']);
    }
    $token       = bin2hex(random_bytes(32));          // 64 karakter acak
    $kedaluwarsa = date('Y-m-d H:i:s', time() + 8 * 3600);   // berlaku 8 jam
    $up = $pdo->prepare("UPDATE pengguna SET token = ?, token_kedaluwarsa = ? WHERE id = ?");
    $up->execute([$token, $kedaluwarsa, $u['id']]);
    kirim(200, [
        'pesan'       => 'Login berhasil.',
        'token'       => $token,
        'peran'       => $u['peran'],
        'kedaluwarsa' => $kedaluwarsa,
    ]);
}

/** POST /api/logout  (wajib membawa token) */
function logout(PDO $pdo): void {
    $u = wajibLogin($pdo);
    $pdo->prepare("UPDATE pengguna SET token = NULL, token_kedaluwarsa = NULL WHERE id = ?")
        ->execute([$u['id']]);
    kirim(204);
}