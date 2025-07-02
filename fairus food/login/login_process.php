<?php
session_start();
require 'koneksi.php'; // Pastikan path ini benar

// Atur header untuk output JSON
header('Content-Type: application/json');

// Fungsi untuk mengirim respons JSON dan menghentikan skrip
function send_json_response($status, $message, $redirect_url = null) {
    $response = ['status' => $status, 'message' => $message];
    if ($redirect_url) {
        $response['redirect_url'] = $redirect_url;
    }
    echo json_encode($response);
    exit;
}

// Ambil data dari form
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    send_json_response('error', 'Username dan Password harus diisi.');
}

// --- LANGKAH 1: Cek di tabel 'users' ---
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // DATA DITEMUKAN DI TABEL USERS
    $user = $result->fetch_assoc();
    
    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Jika password cocok, set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Arahkan berdasarkan role (meskipun dari tabel users, bisa saja rolenya admin)
        $redirect_url = ($user['role'] === 'admin') ? '../admin/index.php' : '../user/index.php';
        if ($user['role'] === 'admin') {
             $_SESSION['admin_username'] = $user['username'];
        }
        
        send_json_response('success', 'Login berhasil!', $redirect_url);

    } else {
        // Jika password salah
        send_json_response('error', 'Password yang Anda masukkan salah.');
    }
    $stmt->close();

} else {
    // --- LANGKAH 2: Jika tidak ada di tabel 'users', cek di tabel 'admin' ---
    $stmt->close(); // Tutup statement user sebelum membuka yang baru
    
    $stmt_admin = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt_admin->bind_param("s", $username);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin->num_rows === 1) {
        // DATA DITEMUKAN DI TABEL ADMIN
        $admin = $result_admin->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password, $admin['password'])) {
            // Jika password cocok, set session khusus admin
            $_SESSION['admin_id'] = $admin['id']; // Menggunakan admin_id untuk membedakan
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['role'] = 'admin'; // Set role secara eksplisit
            
            send_json_response('success', 'Login Admin berhasil!', '../admin/index.php');
        
        } else {
            // Jika password salah
            send_json_response('error', 'Password yang Anda masukkan salah.');
        }

    } else {
        // Jika tidak ditemukan di kedua tabel
        send_json_response('error', 'Username tidak ditemukan.');
    }
    $stmt_admin->close();
}

$conn->close();
?>