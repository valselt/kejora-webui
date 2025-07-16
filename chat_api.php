<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once 'koneksi.php';
$user_id = $_SESSION['user_id'];

// Menggunakan parameter 'action' untuk menentukan tugas
$action = $_GET['action'] ?? 'send_chat'; // Default ke send_chat jika tidak ada

if ($action === 'get_conversations') {
    // --- AKSI: Mengambil semua judul percakapan user ---
    $sql = "SELECT id, title FROM conversations WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $conversations = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    echo json_encode(['status' => 'success', 'conversations' => $conversations]);

} elseif ($action === 'get_messages' && isset($_GET['conversation_id'])) {
    // --- AKSI: Mengambil semua pesan dari satu percakapan ---
    $conversation_id = $_GET['conversation_id'];
    $sql = "SELECT sender, content FROM messages WHERE conversation_id = ? ORDER BY timestamp ASC";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $conversation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    echo json_encode(['status' => 'success', 'messages' => $messages]);

} elseif ($action === 'send_chat') {
    // --- AKSI: Mengirim prompt dan mendapatkan balasan AI (kode dari sebelumnya) ---
    require_once 'groq_handler.php';
    require_once 'google_handler.php';
    
    $request_data = json_decode(file_get_contents('php://input'), true);
    // ... (Sisa kode untuk send_chat dari jawaban sebelumnya tetap sama)
    // --- [Letakkan sisa kode `send_chat` Anda dari jawaban sebelumnya di sini] ---
    $prompt = $request_data['prompt'] ?? '';
    $server = $request_data['server'] ?? '';
    $model = $request_data['model'] ?? '';
    $conversation_id = $request_data['conversation_id'] ?? null;

    if (empty($prompt) || empty($server) || empty($model)) {
        echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak lengkap.']);
        exit;
    }

    $sql_keys = "SELECT groq_api, google_api FROM users WHERE id = ?";
    $stmt_keys = mysqli_prepare($koneksi, $sql_keys);
    mysqli_stmt_bind_param($stmt_keys, "i", $user_id);
    mysqli_stmt_execute($stmt_keys);
    $result_keys = mysqli_stmt_get_result($stmt_keys);
    $user_keys = mysqli_fetch_assoc($result_keys);
    mysqli_stmt_close($stmt_keys);

    $api_key = ($server === 'groq') ? $user_keys['groq_api'] : $user_keys['google_api'];

    if (empty($api_key)) {
        echo json_encode(['status' => 'error', 'message' => "API Key untuk " . ucfirst($server) . " belum diatur."]);
        exit;
    }

    $is_new_conversation = false;
    if (is_null($conversation_id)) {
        $is_new_conversation = true;
        $sql_new_conv = "INSERT INTO conversations (user_id, title, created_at) VALUES (?, 'New Chat', NOW())";
        $stmt_new_conv = mysqli_prepare($koneksi, $sql_new_conv);
        mysqli_stmt_bind_param($stmt_new_conv, "i", $user_id);
        mysqli_stmt_execute($stmt_new_conv);
        $conversation_id = mysqli_insert_id($koneksi);
        mysqli_stmt_close($stmt_new_conv);
    }

    $sql_save_user_msg = "INSERT INTO messages (conversation_id, sender, content) VALUES (?, 'user', ?)";
    $stmt_save_user_msg = mysqli_prepare($koneksi, $sql_save_user_msg);
    mysqli_stmt_bind_param($stmt_save_user_msg, "is", $conversation_id, $prompt);
    mysqli_stmt_execute($stmt_save_user_msg);
    mysqli_stmt_close($stmt_save_user_msg);

    $ai_response_text = '';
    try {
        if ($server === 'groq') {
            $ai_response_text = getGroqResponse($prompt, $api_key, $model);
        } else {
            $ai_response_text = getGoogleResponse($prompt, $api_key, $model);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }

    $sql_save_ai_msg = "INSERT INTO messages (conversation_id, sender, content) VALUES (?, 'ai', ?)";
    $stmt_save_ai_msg = mysqli_prepare($koneksi, $sql_save_ai_msg);
    mysqli_stmt_bind_param($stmt_save_ai_msg, "is", $conversation_id, $ai_response_text);
    mysqli_stmt_execute($stmt_save_ai_msg);
    mysqli_stmt_close($stmt_save_ai_msg);

    $conversation_title = null;
    if ($is_new_conversation) {
        $title_prompt = "Buatkan judul singkat (maksimal 5 kata) untuk percakapan yang diawali dengan prompt ini: \"" . $prompt . "\"";
        try {
            if ($server === 'groq') {
                $conversation_title = getGroqResponse($title_prompt, $api_key, 'llama-3.1-8b-instant');
            } else {
                $conversation_title = getGoogleResponse($title_prompt, $api_key, 'gemini-1.5-flash');
            }
            $conversation_title = trim($conversation_title, " \t\n\r\0\x0B\"*");

            $sql_update_title = "UPDATE conversations SET title = ? WHERE id = ?";
            $stmt_update_title = mysqli_prepare($koneksi, $sql_update_title);
            mysqli_stmt_bind_param($stmt_update_title, "si", $conversation_title, $conversation_id);
            mysqli_stmt_execute($stmt_update_title);
            mysqli_stmt_close($stmt_update_title);

        } catch (Exception $e) {
            $conversation_title = 'New Chat';
        }
    }

    $response_payload = [
        'status' => 'success',
        'message' => $ai_response_text,
        'conversation_id' => $conversation_id
    ];
    if ($conversation_title) {
        $response_payload['title'] = $conversation_title;
    }

    echo json_encode($response_payload);
}

mysqli_close($koneksi);
?>