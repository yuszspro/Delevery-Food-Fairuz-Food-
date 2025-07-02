<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$id_user = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM keranjang WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode(['count' => (int)$result['total']]);
?>
