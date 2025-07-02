<?php
session_start(); // Mulai session untuk keamanan di masa depan

// PERBAIKAN: Mengubah path ke file koneksi agar sesuai dengan struktur folder Anda.
require 'koneksi.php'; 

// Atur header untuk output JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim respons JSON dan menghentikan skrip
function send_json_response($status, $message, $data = []) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_json_response('error', 'Metode request tidak valid.');
}

// Ambil dan bersihkan data
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$role     = 'user'; // Role otomatis

// --- Validasi Input ---
if (empty($username) || empty($email) || empty($phone) || empty($password)) {
    send_json_response('error', 'Semua kolom wajib diisi.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json_response('error', 'Format email tidak valid.');
}
if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    send_json_response('error', 'Format nomor telepon tidak valid. Gunakan 10-15 digit angka.');
}

// --- Gunakan Prepared Statements untuk Keamanan ---

// Cek duplikasi di tabel 'users'
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR phone = ?");
$stmt->bind_param("sss", $username, $email, $phone);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Jika data sudah ada, kirim pesan error
    $stmt->close();
    send_json_response('error', 'Username, email, atau no. telepon sudah digunakan.');
}
$stmt->close();

// Jika semua aman, hash password dan masukkan user baru
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt_insert = $conn->prepare("INSERT INTO users (username, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt_insert->bind_param("sssss", $username, $email, $phone, $hashed_password, $role);

if ($stmt_insert->execute()) {
    send_json_response('success', 'Pendaftaran berhasil! Anda akan diarahkan ke halaman login.');
} else {
    // Kirim pesan error database yang lebih spesifik jika memungkinkan
    send_json_response('error', 'Gagal mendaftar. Terjadi kesalahan pada server.');
}

$stmt_insert->close();
$conn->close();
?>