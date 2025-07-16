<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please login.']);
    exit;
}

require_once '../koneksi.php';

$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    // Validasi dasar
    if (empty($old_password) || empty($new_password)) {
        $response['message'] = 'Password lama dan password baru harus diisi!';
        echo json_encode($response);
        exit;
    }

    // Cek kekuatan password baru (sama seperti di register)
    $hasLower = preg_match('/[a-z]/', $new_password);
    $hasUpper = preg_match('/[A-Z]/', $new_password);
    $hasNumber = preg_match('/\d/', $new_password);
    $hasSymbol = preg_match('/[!@#$%^&*(),.?":{}|<>]/' , $new_password);
    $hasLength = strlen($new_password) >= 8;

    if (!$hasLower || !$hasUpper || !$hasNumber || !$hasSymbol || !$hasLength) {
        $response['message'] = "Password baru tidak memenuhi syarat keamanan!";
        echo json_encode($response);
        exit;
    }

    // Ambil password lama dari database
    $sql_get_password = "SELECT password FROM users WHERE id = ?";
    $stmt_get_password = mysqli_prepare($koneksi, $sql_get_password);
    if ($stmt_get_password === false) {
        $response['message'] = 'Database error (get old password).';
        error_log("Change password get old password prepare failed: " . mysqli_error($koneksi));
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_bind_param($stmt_get_password, "i", $user_id);
    mysqli_stmt_execute($stmt_get_password);
    mysqli_stmt_bind_result($stmt_get_password, $hashed_old_password_db);
    mysqli_stmt_fetch($stmt_get_password);
    mysqli_stmt_close($stmt_get_password);

    // Verifikasi password lama
    if (!password_verify($old_password, $hashed_old_password_db)) {
        $response['message'] = 'Password lama salah!';
        echo json_encode($response);
        exit;
    }

    // Hash password baru
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password baru di database
    $sql_update_password = "UPDATE users SET password = ? WHERE id = ?";
    $stmt_update_password = mysqli_prepare($koneksi, $sql_update_password);

    if ($stmt_update_password === false) {
        $response['message'] = 'Database error (update password).';
        error_log("Change password update prepare failed: " . mysqli_error($koneksi));
    } else {
        mysqli_stmt_bind_param($stmt_update_password, "si", $hashed_new_password, $user_id);
        if (mysqli_stmt_execute($stmt_update_password)) {
            $response['status'] = 'success';
            $response['message'] = 'Password berhasil diubah!';
        } else {
            $response['message'] = 'Gagal mengubah password.';
            error_log("Change password update execute failed: " . mysqli_error($koneksi));
        }
        mysqli_stmt_close($stmt_update_password);
    }
}

mysqli_close($koneksi);
echo json_encode($response);
exit;
?>