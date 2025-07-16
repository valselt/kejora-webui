<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../koneksi.php';
$user_id = $_SESSION['user_id'];

$sql_get = "SELECT profile_picture FROM users WHERE id = ?";
$stmt_get = mysqli_prepare($koneksi, $sql_get);
mysqli_stmt_bind_param($stmt_get, "i", $user_id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt_get);

if ($user && !empty($user['profile_picture'])) {
    // Hapus file dari server
    $file_to_delete = '../' . $user['profile_picture'];
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }
    
    // Hapus path dari database (set ke NULL)
    $sql_update = "UPDATE users SET profile_picture = NULL WHERE id = ?";
    $stmt_update = mysqli_prepare($koneksi, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "i", $user_id);
    if (mysqli_stmt_execute($stmt_update)) {
        echo json_encode(['status' => 'success', 'message' => 'Foto profil berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data dari database.']);
    }
    mysqli_stmt_close($stmt_update);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Tidak ada foto profil untuk dihapus.']);
}

mysqli_close($koneksi);
?>