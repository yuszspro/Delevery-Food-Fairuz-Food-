<?php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// 1. Cek ID Transaksi Terbaru di Server
$latest_id_result = $conn->query("SELECT MAX(id) as max_id FROM transaksi");
$latest_id_on_server = $latest_id_result->fetch_assoc()['max_id'] ?? 0;

// 2. Cek Status Terkini untuk Pesanan yang Sudah Tampil
$ids_json = file_get_contents('php://input');
$request_data = json_decode($ids_json, true);
$ids_on_page = $request_data['ids'] ?? [];

$statuses = [];
if (!empty($ids_on_page) && is_array($ids_on_page)) {
    $transaction_ids = array_map('intval', $ids_on_page);
    
    if (!empty($transaction_ids)) {
        $placeholders = implode(',', array_fill(0, count($transaction_ids), '?'));
        $types = str_repeat('i', count($transaction_ids));

        $query = "SELECT id, status FROM transaksi WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$transaction_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $statuses[$row['id']] = $row['status'];
        }
        $stmt->close();
    }
}

// 3. Kirim Respons Gabungan
echo json_encode([
    'latest_order_id' => (int)$latest_id_on_server,
    'updated_statuses' => $statuses
]);
?>