<?php
function getGoogleResponse($prompt, $apiKey, $model) {
    // URL di-build dengan model, karena Google AI Studio API memasukkannya di URL
    $api_url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $post_data = [
        'contents' => [[
            'parts' => [[
                'text' => $prompt
            ]]
        ]]
        // 'generationConfig' seperti temperature, dll. bisa ditambahkan di sini
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code != 200) {
        $error_data = json_decode($response, true);
        $error_message = $error_data['error']['message'] ?? 'Gagal terhubung ke Google AI API.';
        throw new Exception("Google AI API Error: " . $error_message);
    }
    
    $result = json_decode($response, true);
    // Google API bisa tidak mengembalikan 'parts' jika konten diblokir
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Respons diblokir atau tidak ada konten.';
}
?>