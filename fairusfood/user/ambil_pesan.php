<?php
session_start();
require '../admin/koneksi.php';

// Atur header untuk memberitahu browser bahwa ini adalah respon JSON
header('Content-Type: application/json');

// Validasi: Pastikan pengguna sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    // Kirim array kosong jika sesi tidak valid
    echo json_encode([]);
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil ID pesan terakhir yang sudah dimiliki oleh klien.
// Gunakan (int) untuk keamanan, memastikan itu adalah angka.
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Siapkan query SQL yang efisien:
// 1. Ambil pesan HANYA untuk user yang sedang login (WHERE id_user = ?)
// 2. Ambil pesan yang ID-nya LEBIH BESAR dari ID terakhir yang diterima klien (WHERE id > ?)
$stmt = $conn->prepare("SELECT id, pesan, pengirim_role, waktu FROM chat WHERE id_user = ? AND id > ? ORDER BY waktu ASC");
$stmt->bind_param("ii", $id_user, $last_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Kirimkan hasilnya dalam format JSON.
// Jika tidak ada pesan baru, ini akan mengirimkan array kosong: []
// Jika ada pesan baru, ini akan mengirimkan array berisi pesan-pesan tersebut.
echo json_encode($messages);