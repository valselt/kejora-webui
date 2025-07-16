<?php
function getGroqResponse($prompt, $apiKey, $model) {
    $api_url = 'https://api.groq.com/openai/v1/chat/completions';

    $post_data = [
        'model' => $model,
        'messages' => [['role' => 'user', 'content' => $prompt]]
        // 'temperature', 'max_tokens', dll bisa ditambahkan di sini
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code != 200) {
        // Decode error response from Groq
        $error_data = json_decode($response, true);
        $error_message = $error_data['error']['message'] ?? 'Gagal terhubung ke Groq API.';
        throw new Exception("Groq API Error: " . $error_message);
    }

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? 'Tidak ada respons dari AI.';
}
?>