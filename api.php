<?php
session_start();
header('Content-Type: application/json'); // Mengatur header untuk respons JSON

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please login.']);
    exit;
}

// Sertakan file koneksi database
require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil jenis aksi (misal 'save_api_key')
    $action = $_POST['action'] ?? '';

    if ($action === 'save_api_key') {
        $api_type = $_POST['api_type'] ?? ''; // 'groq' atau 'google'
        $api_value = $_POST['api_value'] ?? '';

        // Validasi input
        if (empty($api_type)) {
            $response['message'] = 'API type is required.';
        } else if (!in_array($api_type, ['groq', 'google'])) {
            $response['message'] = 'Invalid API type.';
        } else {
            // Tentukan kolom database yang akan diupdate
            $db_column = ($api_type === 'groq') ? 'groq_api' : 'google_api';

            // Gunakan prepared statement untuk update
            // Catatan: Untuk API key, kita tidak hash karena ini bukan password.
            // Namun, sangat penting untuk mengirimnya melalui HTTPS.
            $sql_update = "UPDATE users SET {$db_column} = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($koneksi, $sql_update);

            if ($stmt_update === false) {
                $response['message'] = 'Database error: Failed to prepare statement.';
                error_log("API update prepare failed: " . mysqli_error($koneksi));
            } else {
                // Binding parameter: 's' untuk string (api_value), 'i' untuk integer (user_id)
                mysqli_stmt_bind_param($stmt_update, "si", $api_value, $user_id);

                if (mysqli_stmt_execute($stmt_update)) {
                    $response['status'] = 'success';
                    $response['message'] = 'API key updated successfully.';
                    // Perbarui session agar perubahan langsung terlihat tanpa reload
                    $_SESSION['profile_picture'] = null; // Reset profile picture in session for fresh load if needed
                    $_SESSION[$db_column] = $api_value; // Update API key di session
                    
                    // Simpan pesan untuk ditampilkan di halaman account center
                    $_SESSION['api_message'] = ['text' => 'API Key ' . ucfirst($api_type) . ' berhasil diperbarui.', 'type' => 'success'];

                } else {
                    $response['message'] = 'Failed to update API key.';
                    error_log("API update execute failed: " . mysqli_error($koneksi));
                }
                mysqli_stmt_close($stmt_update);
            }
        }
    } else {
        $response['message'] = 'Unknown action.';
    }
}

mysqli_close($koneksi);
echo json_encode($response);
exit;
?>