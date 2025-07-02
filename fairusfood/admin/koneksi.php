<?php
// --- Pengaturan Koneksi Database ---
$db_host = "localhost";        // Biasanya "localhost"
$db_user = "root";             // Username database Anda (default XAMPP/Laragon adalah "root")
$db_pass = "";                 // Password database Anda (default XAMPP/Laragon kosong)
$db_name = "fairus_food";      // Nama database yang telah Anda buat

// --- Membuat Koneksi ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Memeriksa Koneksi ---
if ($conn->connect_error) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error.
    // Sebaiknya jangan tampilkan error detail di lingkungan produksi.
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Mengatur set karakter menjadi utf8mb4 untuk mendukung berbagai macam karakter.
$conn->set_charset("utf8mb4");

?>