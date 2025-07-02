<?php
/*
================================================================
|   File: hapus_produk.php                                     |
|   Lokasi: admin/hapus_produk.php                             |
================================================================
*/
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_username']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT gambar FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $gambar_file = "../uploads/" . $row['gambar'];
    if (file_exists($gambar_file)) {
        @unlink($gambar_file);
    }
}
$stmt->close();

$delete_stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
$delete_stmt->bind_param("i", $id);
$delete_stmt->execute();
$delete_stmt->close();

header("Location: index.php");
exit;
?>

---
