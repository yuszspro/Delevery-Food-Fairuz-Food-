<?php
session_start();
require 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// PERBAIKAN: Menggunakan query yang lebih kuat dan anti-error
$query = "
    SELECT 
        u.id, 
        u.username,
        u.foto_profil,
        c.pesan AS last_message,
        c.waktu AS last_message_time,
        (SELECT COUNT(*) FROM chat WHERE id_user = u.id AND is_read = 0 AND pengirim_role = 'user') as unread_count
    FROM 
        chat c
    -- Menggunakan LEFT JOIN agar jika user terhapus, data chat tetap bisa muncul (meski tanpa nama)
    LEFT JOIN 
        users u ON c.id_user = u.id
    -- Menggunakan subquery di dalam WHERE yang didukung oleh semua versi MySQL
    WHERE 
        c.id IN (SELECT MAX(id) FROM chat GROUP BY id_user)
    ORDER BY 
        c.waktu DESC
";

$result = $conn->query($query);

if (!$result) {
    http_response_code(500);
    // Tampilkan error SQL jika ada untuk debugging
    echo json_encode(['error' => 'Query database gagal: ' . $conn->error]);
    exit;
}

$conversations = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($conversations);
$conn->close();
?>