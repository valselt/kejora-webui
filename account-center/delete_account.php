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
    // Tidak ada input tambahan yang perlu diverifikasi dari sisi server (konfirmasi "HAPUS" sudah di JS)
    // Namun, jika Anda ingin lapisan keamanan ekstra, Anda bisa meminta password lagi di sini.
    // Untuk saat ini, kita ikuti instruksi bahwa konfirmasi "HAPUS" ada di JS.

    // Hapus data pengguna dari tabel users
    $sql_delete_user = "DELETE FROM users WHERE id = ?";
    $stmt_delete_user = mysqli_prepare($koneksi, $sql_delete_user);

    if ($stmt_delete_user === false) {
        $response['message'] = 'Database error (delete user).';
        error_log("Delete account prepare failed: " . mysqli_error($koneksi));
    } else {
        mysqli_stmt_bind_param($stmt_delete_user, "i", $user_id);
        if (mysqli_stmt_execute($stmt_delete_user)) {
            $response['status'] = 'success';
            $response['message'] = 'Akun berhasil dihapus permanen.';

            // Hancurkan session setelah akun dihapus
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();

        } else {
            $response['message'] = 'Gagal menghapus akun.';
            error_log("Delete account execute failed: " . mysqli_error($koneksi));
        }
        mysqli_stmt_close($stmt_delete_user);
    }
}

mysqli_close($koneksi);
echo json_encode($response);
exit;
?>