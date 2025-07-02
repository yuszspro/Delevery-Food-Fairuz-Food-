<?php
/*
================================================================
|   File: edit_produk.php                                       |
|   Lokasi: admin/edit_produk.php                               |
================================================================
*/
session_start();
include 'koneksi.php';

if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("ID produk tidak valid.");
}
$id = $_GET['id'];

$error_message = '';
$success_message = '';

// Ambil data produk yang akan diedit
$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("Produk tidak ditemukan.");
$data = $result->fetch_assoc();
$gambar_lama = $data['gambar'];
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    // [PERUBAHAN] Ambil data deskripsi dan harga diskon dari form
    $deskripsi = trim($_POST['deskripsi']);
    $harga = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
    $harga_diskon = !empty($_POST['harga_diskon']) ? (int)$_POST['harga_diskon'] : null;
    $id_kategori = (int)$_POST['id_kategori'];
    $gambar_baru_info = $_FILES['gambar'];

    if (empty($nama) || empty($deskripsi) || $harga <= 0) {
        $error_message = "Nama, Deskripsi, dan Harga produk harus diisi.";
    } elseif ($harga_diskon !== null && $harga_diskon >= $harga) {
        $error_message = "Harga diskon harus lebih kecil dari harga asli.";
    } else {
        $gambar_to_save = $gambar_lama;
        // Logika untuk handle upload gambar baru
        if (isset($gambar_baru_info) && $gambar_baru_info['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "../uploads/";
            $file_type = strtolower(pathinfo($gambar_baru_info['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $gambar_to_save = time() . '_' . basename($gambar_baru_info['name']);
            $upload_file = $upload_dir . $gambar_to_save;

            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Jenis file tidak valid.";
            } elseif (move_uploaded_file($gambar_baru_info['tmp_name'], $upload_file)) {
                // Hapus gambar lama jika upload gambar baru berhasil
                if (!empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
                    @unlink($upload_dir . $gambar_lama);
                }
            } else {
                $error_message = "Gagal unggah gambar.";
                $gambar_to_save = $gambar_lama; // Jika gagal, tetap gunakan gambar lama
            }
        }
        
        // Lanjutkan update jika tidak ada error dari proses upload gambar
        if (empty($error_message)) {
            // [PERUBAHAN] Query UPDATE disesuaikan untuk menyertakan deskripsi dan harga_diskon
            $query = "UPDATE produk SET nama = ?, deskripsi = ?, harga = ?, harga_diskon = ?, gambar = ?, id_kategori = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            // [PERUBAHAN] bind_param disesuaikan: s(nama), s(deskripsi), i(harga), i(harga_diskon), s(gambar), i(id_kategori), i(id)
            $stmt->bind_param("ssiisii", $nama, $deskripsi, $harga, $harga_diskon, $gambar_to_save, $id_kategori, $id);
            
            if ($stmt->execute()) {
                $success_message = "Produk berhasil diperbarui!";
                // Refresh data setelah update untuk ditampilkan di form
                $data['nama'] = $nama; 
                $data['deskripsi'] = $deskripsi;
                $data['harga'] = $harga; 
                $data['harga_diskon'] = $harga_diskon;
                $data['gambar'] = $gambar_to_save; 
                $data['id_kategori'] = $id_kategori;
            } else {
                $error_message = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Ambil daftar kategori untuk dropdown
$kategori_result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$kategori_list = mysqli_fetch_all($kategori_result, MYSQLI_ASSOC);

// Ambil notifikasi chat
$unread_nav_query = "SELECT COUNT(id) as total_unread FROM chat WHERE is_read = 0 AND pengirim_role = 'user'";
$unread_nav_result = mysqli_query($conn, $unread_nav_query);
$total_unread_nav = mysqli_fetch_assoc($unread_nav_result)['total_unread'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Produk | Fairuz Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-gray-100 h-screen font-sans">
    <div class="flex h-full">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0 flex flex-col">
            <div class="p-4 border-b border-gray-700"><h1 class="text-2xl font-bold">Admin Panel</h1></div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="daftar_pesanan.php" class="block py-2.5 px-4 rounded transition hover:bg-gray-700">Pesanan</a>
                <a href="index.php" class="block py-2.5 px-4 rounded transition bg-amber-500 font-semibold">Produk</a>
                <a href="chat.php" class="relative block py-2.5 px-4 rounded transition hover:bg-gray-700">
                    Chat
                    <?php if ($total_unread_nav > 0): ?>
                        <span class="absolute top-3 right-3 w-2.5 h-2.5 bg-orange-500 rounded-full animate-pulse"></span>
                    <?php endif; ?>
                </a>
            </nav>
            <div class="p-4 border-t border-gray-700"><a href="../login/logout.php" class="block text-center py-2.5 px-4 rounded transition bg-red-600 hover:bg-red-700">Logout</a></div>
        </aside>
        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            <header class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Edit Produk</h2>
                <div class="flex items-center gap-3 font-semibold text-gray-600"><i class="ph-user-circle text-2xl"></i><span><?= htmlspecialchars($_SESSION['admin_username']) ?></span></div>
            </header>
            <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
                <?php if ($error_message): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><?= $error_message ?></div><?php endif; ?>
                <?php if ($success_message): ?><div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><?= $success_message ?></div><?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" autocomplete="off" class="space-y-6">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                        <input type="text" name="nama" id="nama" required value="<?= htmlspecialchars($data['nama']) ?>" class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Produk</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" required class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:ring-amber-500 focus:border-amber-500" placeholder="Jelaskan detail produk di sini..."><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">Harga Asli</label>
                            <input type="number" name="harga" id="harga" required min="1" value="<?= htmlspecialchars($data['harga']) ?>" class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:ring-amber-500 focus:border-amber-500" placeholder="Contoh: 20000">
                        </div>
                        <div>
                            <label for="harga_diskon" class="block text-sm font-medium text-gray-700 mb-1">Harga Diskon (Opsional)</label>
                            <input type="number" name="harga_diskon" id="harga_diskon" min="0" value="<?= htmlspecialchars($data['harga_diskon'] ?? '') ?>" class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:ring-amber-500 focus:border-amber-500" placeholder="Kosongkan jika tidak ada">
                        </div>
                    </div>
                    
                    <div>
                        <label for="id_kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="id_kategori" id="id_kategori" required class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:ring-amber-500 focus:border-amber-500">
                            <option value="" disabled>-- Pilih Kategori --</option>
                            <?php foreach ($kategori_list as $kategori): ?>
                                <option value="<?= $kategori['id'] ?>" <?= ($data['id_kategori'] == $kategori['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Produk</label>
                        <div class="mt-1 flex items-start gap-4">
                            <img id="image-preview" src="../uploads/<?= htmlspecialchars($data['gambar']) ?>" alt="Gambar saat ini" class="h-24 w-24 object-cover rounded-md border">
                            <div class="flex-1">
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <i class="ph-upload-simple text-4xl text-gray-400 mx-auto"></i>
                                        <div class="flex text-sm text-gray-600"><label for="gambar" class="relative cursor-pointer bg-white rounded-md font-medium text-amber-600 hover:text-amber-500"><span>Unggah file baru</span><input id="gambar" name="gambar" type="file" accept="image/*" class="sr-only"></label></div>
                                        <p class="text-xs text-gray-500" id="image-text">Kosongkan jika tidak ingin ganti gambar</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-4 pt-4"><a href="index.php" class="w-full text-center bg-gray-200 text-gray-800 font-bold py-3 px-4 rounded-lg hover:bg-gray-300 transition">Kembali</a><button type="submit" class="w-full bg-amber-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-amber-600 transition">Update Produk</button></div>
                </form>
            </div>
        </main>
    </div>
<script>
    document.getElementById('gambar').addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            const preview = document.getElementById('image-preview');
            preview.src = URL.createObjectURL(file);
            document.getElementById('image-text').textContent = `File baru: ${file.name}`;
        }
    });
</script>
</body>
</html>