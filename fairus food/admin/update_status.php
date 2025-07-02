<?php
session_start();
require 'koneksi.php'; // Pastikan path ini benar

// Atur header untuk output JSON
header('Content-Type: application/json');

// 1. Validasi Sesi Admin dan Input
if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_transaksi']) || !isset($_POST['status'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit;
}

// 2. Ambil dan bersihkan data
$id_transaksi = (int)$_POST['id_transaksi'];
$status_baru = trim($_POST['status']);
$possible_statuses = ['Menunggu', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];

// Validasi apakah status yang dikirim valid
if (!in_array($status_baru, $possible_statuses)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Status tidak valid.']);
    exit;
}

// 3. Gunakan Transaksi Database untuk Keamanan Data
$conn->begin_transaction();

try {
    // Ambil data user dari transaksi ini untuk dimasukkan ke log riwayat
    $stmt_get_user = $conn->prepare("SELECT id_user, tanggal_transaksi FROM transaksi WHERE id = ?");
    if($stmt_get_user === false) throw new Exception("Gagal menyiapkan query untuk mengambil data user.");
    $stmt_get_user->bind_param("i", $id_transaksi);
    $stmt_get_user->execute();
    $result_user = $stmt_get_user->get_result();
    if($result_user->num_rows === 0) throw new Exception("Transaksi tidak ditemukan.");
    $transaksi_data = $result_user->fetch_assoc();
    $id_user = $transaksi_data['id_user'];
    $tanggal_transaksi = $transaksi_data['tanggal_transaksi'];
    $stmt_get_user->close();

    // Langkah A: Update status di tabel transaksi
    $stmt_update = $conn->prepare("UPDATE transaksi SET status = ? WHERE id = ?");
    if($stmt_update === false) throw new Exception("Gagal menyiapkan query update status.");
    $stmt_update->bind_param("si", $status_baru, $id_transaksi);
    $stmt_update->execute();
    $stmt_update->close();

    // Langkah B: Catat aktivitas perubahan status ke tabel riwayat
    $order_id_display = "FF-" . substr(strtoupper(hash('sha1', $id_transaksi . $tanggal_transaksi)), 0, 8);
    $aktifitas = "Status pesanan #" . $order_id_display . " diubah menjadi '" . $status_baru . "'";
    
    $stmt_riwayat = $conn->prepare("INSERT INTO riwayat (id_user, aktifitas) VALUES (?, ?)");
    if($stmt_riwayat === false) throw new Exception("Gagal menyiapkan query pencatatan riwayat.");
    $stmt_riwayat->bind_param("is", $id_user, $aktifitas);
    $stmt_riwayat->execute();
    $stmt_riwayat->close();

    // Jika semua berhasil, simpan perubahan secara permanen
    $conn->commit();

    // Kirim respons sukses
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    // Jika ada satu saja yang gagal, batalkan semua perubahan
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>