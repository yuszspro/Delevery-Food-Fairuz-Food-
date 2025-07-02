<?php
session_start();
require '../admin/koneksi.php'; // Pastikan path ini benar

header('Content-Type: application/json');

// 1. Validasi Sesi dan Input
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Silakan login kembali.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty(trim($_POST['alamat'])) || !isset($_POST['lat']) || !isset($_POST['lng'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Data alamat atau koordinat tidak lengkap.']);
    exit;
}

// 2. Ambil Data
$id_user = $_SESSION['user_id'];
$alamat = trim($_POST['alamat']);
$lat = (float)$_POST['lat'];
$lng = (float)$_POST['lng'];

// 3. Update ke Database menggunakan prepared statement
$stmt = $conn->prepare("UPDATE users SET alamat = ?, alamat_lat = ?, alamat_lng = ? WHERE id = ?");
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mempersiapkan query.']);
    exit;
}

$stmt->bind_param("sddi", $alamat, $lat, $lng, $id_user);

if ($stmt->execute()) {
    // Kirim respons sukses
    echo json_encode(['status' => 'success', 'message' => 'Alamat berhasil disimpan.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan alamat ke database.']);
}

$stmt->close();
$conn->close();
?>
