<?php
// register.php

// Memulai session untuk menangani pesan feedback (error/success)
session_start();

// Menyertakan file koneksi database
require_once '../koneksi.php';

// Cek apakah request datang dari method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil dan bersihkan data dari form.
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phonenumber = trim($_POST['phonenumber']);
    $password = $_POST['password'];
    $password_check = $_POST['password-check'];
    $profile_picture_path = NULL; 

    // 2. Validasi di Sisi Server
    if (empty($fullname) || empty($username) || empty($email) || empty($phonenumber) || empty($password) || empty($password_check)) {
        $_SESSION['error'] = "Semua bidang wajib diisi!";
        header("Location: index.php");
        exit;
    }

    if ($password !== $password_check) {
        $_SESSION['error'] = "Password tidak sama!";
        header("Location: index.php");
        exit;
    }

    $hasLower = preg_match('/[a-z]/', $password);
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasNumber = preg_match('/\d/', $password);
    $hasSymbol = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
    $hasLength = strlen($password) >= 8;

    if (!$hasLower || !$hasUpper || !$hasNumber || !$hasSymbol || !$hasLength) {
        $_SESSION['error'] = "Password tidak memenuhi syarat keamanan! (Minimal 8 karakter, huruf besar, kecil, angka, simbol)";
        header("Location: index.php");
        exit;
    }

    // 3. Cek duplikasi untuk username, email, DAN nomor telepon.
    $sql_check = "SELECT id FROM users WHERE username = ? OR email = ? OR phonenumber = ?";
    $stmt_check = mysqli_prepare($koneksi, $sql_check);
    
    if ($stmt_check === false) {
        $_SESSION['error'] = "Gagal menyiapkan pengecekan data."; 
        header("Location: index.php");
        exit;
    }

    // Bind 3 parameter string (sss)
    mysqli_stmt_bind_param($stmt_check, "sss", $username, $email, $phonenumber);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        // Berikan pesan error yang lebih umum
        $_SESSION['error'] = "Username, email, atau nomor telepon sudah terdaftar!";
        mysqli_stmt_close($stmt_check);
        header("Location: index.php");
        exit;
    }

    mysqli_stmt_close($stmt_check);

    // 4. Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Masukkan data pengguna baru ke database.
    $sql_insert = "INSERT INTO users (fullname, username, email, phonenumber, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);

    if ($stmt_insert === false) {
        $_SESSION['error'] = "Gagal menyiapkan registrasi user baru.";
        header("Location: index.php");
        exit;
    }

    mysqli_stmt_bind_param($stmt_insert, "ssssss", $fullname, $username, $email, $phonenumber, $hashed_password, $profile_picture_path);

    if (mysqli_stmt_execute($stmt_insert)) {
        $_SESSION['success'] = "Registrasi berhasil! Silakan masuk.";
        header("Location: ../login/");
        exit;
    } else {
        $_SESSION['error'] = "Registrasi gagal, terjadi kesalahan pada server.";
        header("Location: index.php");
        exit;
    }

    mysqli_stmt_close($stmt_insert);
    mysqli_close($koneksi);

} else {
    header("Location: index.php");
    exit;
}
?>