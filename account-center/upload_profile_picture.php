<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../koneksi.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // Kita butuh username untuk nama file

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['image'])) {
    $data = $_POST['image'];
    
    // Memisahkan data URI dari data base64
    list($type, $data) = explode(';', $data);
    list(, $data)      = explode(',', $data);
    $data = base64_decode($data);

    if ($data === false) {
        $response['message'] = 'Data base64 tidak valid.';
        echo json_encode($response);
        exit;
    }
    
    // Tentukan ekstensi file
    $image_type = str_replace('data:image/', '', $type);
    $filename = $username . '.' . $image_type;
    $filepath = '../uploads/profile_pictures/' . $filename;
    $db_path = 'uploads/profile_pictures/' . $filename;

    // Hapus file lama jika ada (untuk mencegah penumpukan file dengan ekstensi berbeda)
    $old_picture_path_query = mysqli_query($koneksi, "SELECT profile_picture FROM users WHERE id = $user_id");
    $old_picture_data = mysqli_fetch_assoc($old_picture_path_query);
    if ($old_picture_data && !empty($old_picture_data['profile_picture'])) {
        $old_file_full_path = '../' . $old_picture_data['profile_picture'];
        if (file_exists($old_file_full_path)) {
            unlink($old_file_full_path);
        }
    }

    // Simpan file baru
    if (file_put_contents($filepath, $data)) {
        // Update path di database
        $sql_update = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($koneksi, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "si", $db_path, $user_id);
        if (mysqli_stmt_execute($stmt_update)) {
            $response['status'] = 'success';
            $response['message'] = 'Foto profil berhasil diperbarui!';
        } else {
            $response['message'] = 'Gagal memperbarui database.';
        }
        mysqli_stmt_close($stmt_update);
    } else {
        $response['message'] = 'Gagal menyimpan file gambar.';
    }
}

mysqli_close($koneksi);
echo json_encode($response);
?>