<?php
/*
================================================================
|   File: kirim_pesan.php                                      |
|   Lokasi: user/kirim_pesan.php                               |
================================================================
*/
session_start();
require '../admin/koneksi.php';

header('Content-Type: application/json');

$id_user = 0;
$pengirim_role = '';
$pesan = '';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    $id_user = $_SESSION['user_id'];
    $pengirim_role = 'user';
    $pesan = $_POST['pesan'] ?? '';
} 
elseif (isset($_SESSION['admin_username']) && $_SESSION['role'] === 'admin') {
    $id_user = $_POST['id_user'] ?? 0;
    $pengirim_role = 'admin';
    $pesan = $_POST['pesan'] ?? '';
} 
else {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid.']);
    exit;
}

if (empty(trim($pesan)) || empty($id_user)) {
    echo json_encode(['status' => 'error', 'message' => 'Pesan atau ID user tidak boleh kosong.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO chat (id_user, pengirim_role, pesan) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $id_user, $pengirim_role, $pesan);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pesan.']);
}

$stmt->close();
$conn->close();
?>
