<?php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses admin ditolak.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty(trim($_POST['pesan'])) || empty($_POST['id_user'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit;
}

$id_user = (int)$_POST['id_user'];
$pesan = trim($_POST['pesan']);
$pengirim_role = 'admin';
$is_read = 0; // 0 = belum dibaca oleh user

$stmt = $conn->prepare("INSERT INTO chat (id_user, pesan, pengirim_role, is_read) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issi", $id_user, $pesan, $pengirim_role, $is_read);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Pesan terkirim.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim pesan.']);
}

$stmt->close();
$conn->close();
?>