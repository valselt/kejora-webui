<?php
// lib/device_handler.php

// Fungsi untuk membuat token baru dan menyimpannya di DB & Cookie
function create_trusted_device($user_id) {
    global $koneksi;

    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $validator_hash = password_hash($validator, PASSWORD_DEFAULT);

    // Simpan ke database
    $sql = "INSERT INTO trusted_devices (user_id, selector, validator_hash) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $selector, $validator_hash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Set cookie di browser pengguna (berlaku 90 hari)
    $cookie_value = $selector . ':' . $validator;
    setcookie('remember_device', $cookie_value, time() + (90 * 24 * 60 * 60), "/");
}

// Fungsi untuk memvalidasi cookie yang ada
function validate_device_cookie() {
    global $koneksi;

    if (empty($_COOKIE['remember_device'])) {
        return false; // Tidak ada cookie
    }

    list($selector, $validator) = explode(':', $_COOKIE['remember_device']);

    if (empty($selector) || empty($validator)) {
        return false; // Format cookie salah
    }

    $sql = "SELECT * FROM trusted_devices WHERE selector = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $selector);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $device_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($device_data) {
        // Jika token ditemukan, verifikasi validator
        if (password_verify($validator, $device_data['validator_hash'])) {
            // Cek apakah sudah lebih dari 15 hari tidak aktif
            $last_used_timestamp = strtotime($device_data['last_used_at']);
            $fifteen_days_ago = time() - (15 * 24 * 60 * 60);

            if ($last_used_timestamp < $fifteen_days_ago) {
                // Perangkat sudah kedaluwarsa, hapus dari DB
                delete_trusted_device($device_data['id']);
                return false;
            }
            // Perangkat valid dan aktif
            return $device_data['user_id'];
        }
    }
    
    return false; // Cookie tidak valid
}

// Fungsi untuk menghapus token dari DB
function delete_trusted_device($device_id) {
    global $koneksi;
    $sql = "DELETE FROM trusted_devices WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $device_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Fungsi untuk membuat sesi login pengguna
function create_user_session($user_id) {
    global $koneksi;
    
    // Ambil data terbaru dari user
    $sql_user = "SELECT username, fullname, profile_picture FROM users WHERE id = ?";
    $stmt_user = mysqli_prepare($koneksi, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $user_result = mysqli_stmt_get_result($stmt_user);
    $user_data = mysqli_fetch_assoc($user_result);
    mysqli_stmt_close($stmt_user);

    // Buat session
    session_regenerate_id(true); // Mencegah session fixation
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['fullname'] = $user_data['fullname'];
    $_SESSION['profile_picture'] = $user_data['profile_picture'];
    
    // Update last_login_at di tabel users
    $sql_update_login = "UPDATE users SET last_login_at = NOW() WHERE id = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update_login);
    mysqli_stmt_bind_param($stmt_update, "i", $user_id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
}
?>