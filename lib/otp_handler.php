<?php
// lib/otp_handler.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Sertakan autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

function send_otp($user_id, $user_email, $user_fullname) {
    global $koneksi; // Menggunakan koneksi database global dari file yang memanggilnya

    // 1. Generate OTP dan waktu kedaluwarsa
    $otp_code = random_int(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', time() + (5 * 60)); // 5 menit

    // 2. Simpan OTP ke database
    $sql_insert_otp = "INSERT INTO user_otps (user_id, otp_code, expires_at) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql_insert_otp);
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $otp_code, $expires_at);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // 3. Konfigurasi dan Kirim Email dengan PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi Server SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'altkejoraai@gmail.com'; // GANTI DENGAN EMAIL ANDA
        $mail->Password   = 'juvjotjvmbltbtho';    // GANTI DENGAN 16 KARAKTER APP PASSWORD ANDA
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Pengirim dan Penerima
        $mail->setFrom('no-reply@kejora.ai', 'Kejora AI');
        $mail->addAddress($user_email, $user_fullname);

        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = 'Kode Verifikasi OTP Kejora Anda';
        
        $email_body = "
            <p>Halo {$user_fullname},</p>
            <p>Kejora telah menerima permintaan untuk memverifikasi identitas Anda atau menyelesaikan tindakan di akun Anda.</p>
            <p>Kode One-Time Password (OTP) Anda adalah:</p>
            <h2 style='font-size: 24px; letter-spacing: 5px; text-align: center;'>{$otp_code}</h2>
            <p>Kode OTP ini valid selama 5 Menit. Jika Anda tidak meminta kode ini, mohon abaikan email ini dan disarankan mengganti Password Kejora anda.</p>
            <hr>
            <p>© 2025 Kejora, an Web UI by valselt. All rights reserved.</p>
        ";
        $mail->Body = $email_body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Melempar exception agar bisa ditangkap oleh kode yang memanggil
        throw new Exception("Email tidak dapat dikirim. Mailer Error: {$mail->ErrorInfo}");
    }
}