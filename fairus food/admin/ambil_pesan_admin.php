<?php
/*
================================================================
|   File: ambil_pesan_admin.php                                 |
|   Lokasi: admin/ambil_pesan_admin.php                         |
================================================================
*/
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

// Validasi sesi admin
if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Validasi input user_id
if (!isset($_GET['user_id']) || !filter_var($_GET['user_id'], FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID tidak valid']);
    exit;
}

$id_user = (int)$_GET['user_id'];

// PERBAIKAN: Mengganti 'waktu_kirim' menjadi 'waktu' sesuai dengan struktur tabel chat
$query = "SELECT id, pesan, pengirim_role, waktu FROM chat WHERE id_user = ? ORDER BY waktu ASC";

$stmt = $conn->prepare($query);

// PENAMBAHAN: Pengecekan jika prepare statement gagal
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyiapkan query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo json_encode($messages);
?>