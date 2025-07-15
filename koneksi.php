<?php
$servername = "localhost";
$username = "root";
$password = ""; // Biasanya kosong untuk XAMPP default
$dbname = "db_kejora"; // Pastikan ini nama database Anda yang benar

// Membuat koneksi
$koneksi = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
// echo "Koneksi berhasil!"; // Ini bisa Anda aktifkan sementara untuk debugging
?>