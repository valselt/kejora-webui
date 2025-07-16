<?php
session_start();
require_once '../koneksi.php';
// Memanggil pustaka baru kita
require_once '../lib/device_handler.php';

// Pastikan pengguna datang dari proses OTP yang benar
if (!isset($_SESSION['otp_user_id'])) {
    header('Location: ../login/');
    exit;
}

// Pastikan request adalah POST dari form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $user_id = $_SESSION['otp_user_id'];
    
    // Gabungkan 6 input OTP menjadi satu string
    $submitted_otp = implode('', $_POST['otp']);

    // 1. Ambil OTP yang tersimpan di database untuk user ini
    $sql_get_otp = "SELECT otp_code, expires_at FROM user_otps WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt_get = mysqli_prepare($koneksi, $sql_get_otp);
    mysqli_stmt_bind_param($stmt_get, "i", $user_id);
    mysqli_stmt_execute($stmt_get);
    $result = mysqli_stmt_get_result($stmt_get);
    $otp_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt_get);

    // 2. Lakukan Validasi
    if ($otp_data && $otp_data['otp_code'] === $submitted_otp) {
        // Cek apakah OTP sudah kedaluwarsa
        if (strtotime($otp_data['expires_at']) > time()) {
            // --- BERHASIL! OTP cocok dan belum kedaluwarsa ---
            // --- LOGIKA BARU DIMULAI DI SINI ---

            // a. Cek apakah ini verifikasi untuk registrasi baru
            // Jika tidak ada konteks, kita anggap ini dari registrasi
            if (!isset($_SESSION['otp_context']) || $_SESSION['otp_context'] !== 'device_verification') {
                // Update status user menjadi terverifikasi
                $sql_verify = "UPDATE users SET is_verified = 1 WHERE id = ?";
                $stmt_verify = mysqli_prepare($koneksi, $sql_verify);
                mysqli_stmt_bind_param($stmt_verify, "i", $user_id);
                mysqli_stmt_execute($stmt_verify);
                mysqli_stmt_close($stmt_verify);
            }
            
            // b. Selalu "percayai" perangkat ini setelah OTP berhasil
            create_trusted_device($user_id);

            // c. Hapus OTP yang sudah digunakan dari database
            $sql_delete_otp = "DELETE FROM user_otps WHERE user_id = ?";
            $stmt_delete = mysqli_prepare($koneksi, $sql_delete_otp);
            mysqli_stmt_bind_param($stmt_delete, "i", $user_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);

            // d. Hapus session OTP dan langsung loginkan pengguna
            unset($_SESSION['otp_user_id']);
            unset($_SESSION['otp_context']);
            
            create_user_session($user_id); // Gunakan fungsi terpusat untuk login
            
            // e. Arahkan ke halaman utama
            header("Location: ../");
            exit;

        } else {
            // OTP benar tapi sudah kedaluwarsa
            $_SESSION['otp_error'] = "Kode OTP sudah kedaluwarsa. Silakan minta kode baru.";
            header("Location: index.php");
            exit;
        }
    } else {
        // OTP salah
        $_SESSION['otp_error'] = "Kode OTP yang Anda masukkan salah.";
        header("Location: index.php");
        exit;
    }
} else {
    // Jika diakses langsung, tendang
    header('Location: index.php');
    exit;
}

mysqli_close($koneksi);
?>