<?php
// register.php

// Memulai session untuk menangani pesan feedback (error/success)
session_start();

// Menyertakan file koneksi database
// Lokasinya ../ karena file ini ada di dalam folder 'register', dan koneksi.php ada di root.
require_once '../koneksi.php';

// Cek apakah request datang dari method POST (dari form registrasi)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil dan bersihkan data dari form.
    // trim() digunakan untuk menghapus spasi di awal dan akhir string.
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phonenumber = trim($_POST['phonenumber']);
    $password = $_POST['password'];
    $password_check = $_POST['password-check']; // Konfirmasi password

    // Inisialisasi path foto profil menjadi NULL atau path default.
    // Karena Anda ingin fitur upload foto profil dilakukan setelah login,
    // di sini kita hanya akan menyimpan NULL atau nilai DEFAULT dari DB.
    $profile_picture_path = NULL; 
    // Jika Anda telah mengatur DEFAULT 'uploads/profile_pictures/default-image.png' di DB,
    // maka Anda tidak perlu secara eksplisit mengaturnya di sini karena DB akan menanganinya.
    // Jika tidak, Anda bisa set manual di sini:
    // $profile_picture_path = 'uploads/profile_pictures/default-image.png'; // Pastikan path ini benar dari root proyek

    // 2. Validasi di Sisi Server (Sangat Penting untuk keamanan dan integritas data!)

    // Cek apakah ada field wajib yang kosong
    if (empty($fullname) || empty($username) || empty($email) || empty($phonenumber) || empty($password) || empty($password_check)) {
        $_SESSION['error'] = "Semua bidang wajib diisi!"; // Pesan error ke session
        header("Location: index.php"); // Arahkan kembali ke halaman registrasi
        exit; // Hentikan eksekusi script
    }

    // Cek apakah password cocok dengan konfirmasi password
    if ($password !== $password_check) {
        $_SESSION['error'] = "Password tidak sama!";
        header("Location: index.php");
        exit;
    }

    // Cek kekuatan password menggunakan Regular Expressions (Regex)
    $hasLower = preg_match('/[a-z]/', $password); // Setidaknya satu huruf kecil
    $hasUpper = preg_match('/[A-Z]/', $password); // Setidaknya satu huruf besar
    $hasNumber = preg_match('/\d/', $password); // Setidaknya satu angka
    $hasSymbol = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password); // Setidaknya satu simbol
    $hasLength = strlen($password) >= 8; // Minimal 8 karakter

    if (!$hasLower || !$hasUpper || !$hasNumber || !$hasSymbol || !$hasLength) {
        $_SESSION['error'] = "Password tidak memenuhi syarat keamanan! (Minimal 8 karakter, huruf besar, kecil, angka, simbol)";
        header("Location: index.php");
        exit;
    }

    // 3. Cek apakah username atau email sudah ada di database.
    // Menggunakan prepared statements untuk keamanan (melawan SQL Injection).
    $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt_check = mysqli_prepare($koneksi, $sql_check);
    
    // Periksa apakah prepare statement berhasil
    if ($stmt_check === false) {
        // Untuk debugging: . mysqli_error($koneksi)
        $_SESSION['error'] = "Gagal menyiapkan pengecekan username/email."; 
        header("Location: index.php");
        exit;
    }

    // Bind parameter: "ss" berarti dua parameter string
    mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check); // Menyimpan hasil agar bisa dihitung barisnya

    if (mysqli_stmt_num_rows($stmt_check) > 0) { // Jika ditemukan lebih dari 0 baris, berarti username/email sudah terdaftar
        $_SESSION['error'] = "Username atau email sudah terdaftar!";
        mysqli_stmt_close($stmt_check); // Tutup statement sebelum redirect
        header("Location: index.php");
        exit;
    }

    mysqli_stmt_close($stmt_check); // Tutup statement jika tidak ada duplikasi

    // 4. Hash password sebelum disimpan ke database.
    // password_hash() adalah cara yang aman untuk menyimpan password.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Masukkan data pengguna baru ke database.
    // Menggunakan prepared statements lagi untuk INSERT.
    // Pastikan urutan kolom sesuai dengan parameter.
    $sql_insert = "INSERT INTO users (fullname, username, email, phonenumber, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);

    // Periksa apakah prepare statement insert berhasil
    if ($stmt_insert === false) {
        // Untuk debugging: . mysqli_error($koneksi)
        $_SESSION['error'] = "Gagal menyiapkan registrasi user baru.";
        header("Location: index.php");
        exit;
    }

    // Bind parameter: "ssssss" berarti enam parameter string.
    // Parameter terakhir adalah $profile_picture_path (yang bisa NULL atau path default).
    mysqli_stmt_bind_param($stmt_insert, "ssssss", $fullname, $username, $email, $phonenumber, $hashed_password, $profile_picture_path);

    if (mysqli_stmt_execute($stmt_insert)) {
        // Jika insert berhasil, kirim pesan sukses dan arahkan ke halaman login
        $_SESSION['success'] = "Registrasi berhasil! Silakan masuk.";
        header("Location: ../login/index.php"); // Arahkan ke halaman login (index.php di folder login)
        exit;
    } else {
        // Jika gagal, kirim pesan error (terjadi kesalahan pada DB)
        // Untuk debugging: . mysqli_error($koneksi)
        $_SESSION['error'] = "Registrasi gagal, terjadi kesalahan pada server.";
        header("Location: index.php");
        exit;
    }

    mysqli_stmt_close($stmt_insert); // Tutup statement insert
    mysqli_close($koneksi); // Tutup koneksi database setelah semua operasi selesai

} else {
    // Jika file ini diakses langsung tanpa method POST, arahkan ke halaman registrasi.
    header("Location: index.php");
    exit;
}
?>