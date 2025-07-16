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
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phonenumber = trim($_POST['phonenumber'] ?? '');

    // Validasi dasar
    if (empty($username) || empty($fullname) || empty($email) || empty($phonenumber)) {
        $response['message'] = 'Semua bidang wajib diisi!';
        echo json_encode($response);
        exit;
    }

    // Cek duplikasi username atau email (kecuali milik user itu sendiri)
    $sql_check_duplicate = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
    $stmt_check_duplicate = mysqli_prepare($koneksi, $sql_check_duplicate);
    if ($stmt_check_duplicate === false) {
        $response['message'] = 'Database error (duplicate check).';
        error_log("Edit profile duplicate check prepare failed: " . mysqli_error($koneksi));
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_bind_param($stmt_check_duplicate, "ssi", $username, $email, $user_id);
    mysqli_stmt_execute($stmt_check_duplicate);
    mysqli_stmt_store_result($stmt_check_duplicate);
    if (mysqli_stmt_num_rows($stmt_check_duplicate) > 0) {
        $response['message'] = 'Username atau Email sudah digunakan oleh akun lain.';
        mysqli_stmt_close($stmt_check_duplicate);
        echo json_encode($response);
        exit;
    }
    mysqli_stmt_close($stmt_check_duplicate);


    // Update data pengguna
    $sql_update = "UPDATE users SET username = ?, fullname = ?, email = ?, phonenumber = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);

    if ($stmt_update === false) {
        $response['message'] = 'Database error (update).';
        error_log("Edit profile update prepare failed: " . mysqli_error($koneksi));
    } else {
        mysqli_stmt_bind_param($stmt_update, "ssssi", $username, $fullname, $email, $phonenumber, $user_id);
        if (mysqli_stmt_execute($stmt_update)) {
            $response['status'] = 'success';
            $response['message'] = 'Biodata berhasil diperbarui!';
            // Update session agar perubahan langsung terlihat di navbar/halaman lain
            $_SESSION['username'] = $username;
            $_SESSION['fullname'] = $fullname;
            // Email dan phonenumber biasanya tidak disimpan di session, tapi username/fullname sering.
        } else {
            $response['message'] = 'Gagal memperbarui biodata.';
            error_log("Edit profile update execute failed: " . mysqli_error($koneksi));
        }
        mysqli_stmt_close($stmt_update);
    }
}

mysqli_close($koneksi);
echo json_encode($response);
exit;
?>