<?php
session_start();
require_once '../koneksi.php';
require_once '../lib/device_handler.php'; // Panggil pustaka untuk urusan perangkat
require_once '../lib/otp_handler.php';    // Panggil pustaka untuk urusan OTP

// Memastikan request datang dari method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST['username']); // Bisa username atau email
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        $_SESSION['login_error'] = "Username dan password harus diisi!";
        header("Location: index.php");
        exit;
    }

    // 1. Validasi Password Pengguna
    $sql = "SELECT id, fullname, email, password, is_verified FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['password'])) {
        // ---- PASSWORD BENAR, MASUK KE LOGIKA BARU ----

        // 2. Cek apakah akun sudah diverifikasi saat registrasi
        if ($user['is_verified'] == 0) {
            // Jika belum, paksa pengguna untuk verifikasi OTP registrasi terlebih dahulu
            $_SESSION['otp_user_id'] = $user['id'];
            send_otp($user['id'], $user['email'], $user['fullname']);
            header("Location: ../otp/");
            exit;
        }

        // 3. Validasi Perangkat (Cookie)
        $user_id_from_cookie = validate_device_cookie(); // Fungsi dari device_handler.php
        
        if ($user_id_from_cookie === $user['id']) {
            // Perangkat Dikenali, Aktif, dan Terpercaya! Langsung Login.
            create_user_session($user['id']); // Fungsi dari device_handler.php
            header("Location: ../index.php");
            exit;
        } else {
            // Perangkat Baru, Tidak Dikenali, atau Sudah Inaktif > 15 hari. Minta OTP.
            $_SESSION['otp_user_id'] = $user['id'];
            $_SESSION['otp_context'] = 'device_verification'; // Tandai ini untuk verifikasi perangkat
            send_otp($user['id'], $user['email'], $user['fullname']); // Fungsi dari otp_handler.php
            header("Location: ../otp/");
            exit;
        }
        // ---- AKHIR DARI LOGIKA BARU ----

    } else {
        // Jika username tidak ditemukan atau password salah
        $_SESSION['login_error'] = "Username atau password salah!";
        header("Location: index.php");
        exit;
    }
} else {
    // Jika file diakses langsung tanpa method POST
    header("Location: index.php");
    exit;
}
?>