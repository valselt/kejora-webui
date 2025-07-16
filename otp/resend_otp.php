<?php
session_start();
require_once '../koneksi.php';
require_once '../lib/otp_handler.php'; // Memanggil fungsi send_otp

header('Content-Type: application/json');

// Pastikan pengguna berada dalam alur OTP yang benar
if (!isset($_SESSION['otp_user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid.']);
    exit;
}

$user_id = $_SESSION['otp_user_id'];
$response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];

// --- Logika Rate Limiting ---
// Cek berapa banyak OTP yang telah dikirim dalam 10 menit terakhir
$time_limit = date('Y-m-d H:i:s', time() - (10 * 60)); // 10 menit yang lalu
$sql_check = "SELECT COUNT(*) as count FROM user_otps WHERE user_id = ? AND created_at > ?";
$stmt_check = mysqli_prepare($koneksi, $sql_check);
mysqli_stmt_bind_param($stmt_check, "is", $user_id, $time_limit);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt_check);

if ($row['count'] >= 3) {
    $response['message'] = 'Anda terlalu sering meminta OTP. Silakan coba lagi dalam 10 menit.';
    echo json_encode($response);
    exit;
}

// --- Jika Lolos Rate Limit, Kirim Ulang OTP ---
// Ambil email dan nama lengkap pengguna untuk dikirim ke fungsi send_otp
$sql_user = "SELECT email, fullname FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($koneksi, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$user_result = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($stmt_user);

if ($user_data) {
    try {
        // Panggil fungsi yang sama seperti saat registrasi
        send_otp($user_id, $user_data['email'], $user_data['fullname']);
        $response['status'] = 'success';
        $response['message'] = 'Kode OTP baru telah dikirim ke email Anda.';
    } catch (Exception $e) {
        $response['message'] = 'Gagal mengirim email OTP. Silakan coba lagi.';
    }
}

mysqli_close($koneksi);
echo json_encode($response);
?>