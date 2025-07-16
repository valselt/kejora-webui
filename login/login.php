<?php
// login.php

session_start(); // Mulai session untuk menyimpan status login dan pesan

// Sertakan file koneksi database
// Path relatif dari login/ ke root proyek (ai-website/koneksi.php)
require_once '../koneksi.php';

// Memastikan request datang dari method POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan bersihkan (trim untuk menghilangkan spasi di awal/akhir)
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi input kosong di sisi server (meskipun ada validasi JS, ini penting)
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username dan password harus diisi!"; // Simpan pesan error di session
        header("Location: index.php"); // Kembali ke halaman login (index.php di folder login)
        exit;
    }

    // Gunakan prepared statement untuk mencegah SQL Injection.
    // Query mencari user berdasarkan username ATAU email.
    $sql = "SELECT id, username, password, fullname, profile_picture FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($koneksi, $sql);

    // Periksa jika prepared statement gagal (misalnya karena query salah atau koneksi DB bermasalah)
    if ($stmt === false) {
        // Untuk debugging, bisa tambahkan: . mysqli_error($koneksi)
        $_SESSION['login_error'] = "Terjadi kesalahan server saat login. Silakan coba lagi.";
        header("Location: index.php");
        exit;
    }

    // Bind parameter ke prepared statement. "ss" berarti dua parameter string.
    // Kita bind $username dua kali agar bisa mencari di kolom username atau email.
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);

    // Jalankan query
    mysqli_stmt_execute($stmt);

    // Dapatkan hasil query
    $result = mysqli_stmt_get_result($stmt);

    // Periksa apakah ada satu baris hasil (user ditemukan)
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result); // Ambil data user sebagai array asosiatif

        // Verifikasi password yang di-hash.
        // password_verify() membandingkan plain-text password dengan hash yang tersimpan.
        if (password_verify($password, $user['password'])) {
            // Login berhasil: Simpan data user penting ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            // Simpan path foto profil ke session (bisa NULL atau path default/custom)
            $_SESSION['profile_picture'] = $user['profile_picture']; 
            
            $_SESSION['login_success'] = "Selamat datang, " . $user['fullname'] . "!"; // Pesan sukses

            // Redirect ke halaman utama/dashboard setelah login.
            // Sesuaikan path ini dengan lokasi landing page utama Anda (misal: index.php di root).
            header("Location: ../"); 
            exit;
        } else {
            // Password salah
            $_SESSION['login_error'] = "Username atau password salah!";
            header("Location: index.php"); // Kembali ke halaman login
            exit;
        }
    } else {
        // Username tidak ditemukan (atau lebih dari satu, yang tidak seharusnya terjadi jika username/email unique)
        $_SESSION['login_error'] = "Username atau password salah!";
        header("Location: index.php"); // Kembali ke halaman login
        exit;
    }

    // Tutup statement dan koneksi database
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

} else {
    // Jika file ini diakses langsung tanpa method POST, arahkan kembali ke halaman login.
    header("Location: index.php");
    exit;
}
?>