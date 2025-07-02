<?php
require 'koneksi.php';
session_start();

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $error_message = "Semua kolom wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok!";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM admin WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = "Username atau email admin sudah terdaftar.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';
            $stmt_insert = $conn->prepare("INSERT INTO admin (username, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $username, $email, $phone, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $success_message = "Pendaftaran admin berhasil! Anda akan diarahkan ke halaman login.";
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
            } else {
                $error_message = "Terjadi kesalahan saat pendaftaran.";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Akun Admin | Fairus Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-800 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white/80 backdrop-blur-lg border border-white/30 w-full max-w-md px-8 py-8 rounded-2xl shadow-lg">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Buat Akun Admin</h1>
            <p class="text-gray-600 text-sm">Halaman ini hanya untuk administrator.</p>
        </div>
        
        <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <span class="block sm:inline"><?= $error_message ?></span>
        </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <span class="block sm:inline"><?= $success_message ?></span>
        </div>
        <?php endif; ?>

        <form action="admin_signup.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block mb-1 text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" required class="w-full px-4 py-2 bg-white/80 border border-gray-300/50 rounded-lg text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500" />
            </div>
            <div>
                <label for="email" class="block mb-1 text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-2 bg-white/80 border border-gray-300/50 rounded-lg text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500" />
            </div>
            <div>
                <label for="phone" class="block mb-1 text-sm font-medium text-gray-700">Nomor Telepon</label>
                <input type="tel" id="phone" name="phone" required class="w-full px-4 py-2 bg-white/80 border border-gray-300/50 rounded-lg text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500" />
            </div>
            <div>
                <label for="password" class="block mb-1 text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2 bg-white/80 border border-gray-300/50 rounded-lg text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500" />
            </div>
             <div>
                <label for="confirm_password" class="block mb-1 text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-4 py-2 bg-white/80 border border-gray-300/50 rounded-lg text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-500" />
            </div>
            <button type="submit" class="w-full bg-amber-500 text-white rounded-full py-3 font-bold cursor-pointer hover:bg-amber-600 transition-colors shadow-md">Daftar Admin</button>
        </form>
        <div class="text-center text-sm mt-6">
            <a href="index.php" class="font-bold text-amber-700 hover:underline">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>