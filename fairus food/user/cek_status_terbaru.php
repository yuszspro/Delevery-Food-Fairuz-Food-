<?php
session_start();
require 'koneksi.php';

// Atur header untuk merespons sebagai JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim respons error dan menghentikan skrip
function send_json_error($message) {
    http_response_code(500);
    echo json_encode(['error' => $message]);
    exit;
}

// Validasi: Pastikan user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Validasi: Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metode tidak diizinkan']);
    exit;
}

// Ambil data JSON dari body request
$json_data = file_get_contents('php://input');
$transaction_ids = json_decode($json_data);

// Validasi: Pastikan data tidak kosong dan merupakan array
if (empty($transaction_ids) || !is_array($transaction_ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data ID transaksi tidak valid']);
    exit;
}

// Amankan ID dengan mengubahnya menjadi integer
$safe_ids = array_map('intval', $transaction_ids);
if (empty($safe_ids)) {
    echo json_encode([]); // Kembalikan array kosong jika tidak ada ID yang valid
    exit;
}

$id_user = $_SESSION['user_id'];
$placeholders = implode(',', array_fill(0, count($safe_ids), '?'));
$types = str_repeat('i', count($safe_ids));

// Query untuk mengambil status terbaru, pastikan hanya milik user yang login
$query = "SELECT id, status FROM transaksi WHERE id_user = ? AND id IN ($placeholders)";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    send_json_error("Gagal mempersiapkan statement: " . $conn->error);
}

$stmt->bind_param("i" . $types, $id_user, ...$safe_ids);
$stmt->execute();
$result = $stmt->get_result();

$statuses = [];
while ($row = $result->fetch_assoc()) {
    $statuses[$row['id']] = $row['status'];
}

$stmt->close();
$conn->close();

// Kirimkan status terbaru sebagai JSON
echo json_encode($statuses);
?>