<?php
session_start();
require '../admin/koneksi.php'; 

header('Content-Type: application/json');

// Jika user belum login, kirimkan 0 notifikasi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['unread_count' => 0]);
    exit;
}

$id_user = $_SESSION['user_id'];

// Hitung jumlah pesan dari 'admin' yang belum dibaca ('is_read' = 0)
$stmt = $conn->prepare("SELECT COUNT(id) as unread_count FROM chat WHERE id_user = ? AND pengirim_role = 'admin' AND is_read = 0");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Kembalikan jumlahnya dalam format JSON
echo json_encode(['unread_count' => $data['unread_count'] ?? 0]);
?>