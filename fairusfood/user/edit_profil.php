<?php
session_start();
require '../admin/koneksi.php'; // Sesuaikan path jika perlu

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Logika saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $hapus_foto = $_POST['hapus_foto'] ?? '0';
    
    $stmt = $conn->prepare("SELECT username, email, foto_profil FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $current_user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $foto_profil_lama = $current_user_data['foto_profil'];
    $foto_profil_baru = $foto_profil_lama;

    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $id_user);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error_message = "Username atau email sudah digunakan oleh pengguna lain.";
    }
    $stmt->close();

    $upload_dir = "../uploads/profiles/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if ($hapus_foto === '1') {
        if (!empty($foto_profil_lama) && file_exists($upload_dir . $foto_profil_lama)) {
            @unlink($upload_dir . $foto_profil_lama);
        }
        $foto_profil_baru = null;
    } 
    elseif (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file_info = $_FILES['foto_profil'];
        $file_name = time() . '_' . basename($file_info['name']);
        $upload_file = $upload_dir . $file_name;
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_type = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

        if (!in_array($file_type, $allowed_types)) {
            $error_message = "Jenis file foto tidak valid.";
        } elseif (move_uploaded_file($file_info['tmp_name'], $upload_file)) {
            if (!empty($foto_profil_lama) && file_exists($upload_dir . $foto_profil_lama)) {
                @unlink($upload_dir . $foto_profil_lama);
            }
            $foto_profil_baru = $file_name;
        } else {
            $error_message = "Gagal mengunggah foto profil.";
        }
    }

    if (empty($error_message)) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, foto_profil = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $foto_profil_baru, $id_user);
        if ($stmt->execute()) {
            $_SESSION['user_username'] = $username;
            $success_message = "Profil berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui profil.";
        }
        $stmt->close();
    }
}

// Ambil data terbaru user untuk ditampilkan di form
$stmt = $conn->prepare("SELECT username, email, foto_profil FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* PENAMBAHAN: Kelas untuk transisi fade-out */
        .fade-out {
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-pink-50 min-h-screen font-sans">

    <header class="bg-amber-500 p-5 flex items-center text-white font-semibold text-lg shadow-md sticky top-0 z-20">
        <a href="profil.php" class="mr-4 text-xl">&larr;</a>
        <h1>Edit Profil</h1>
    </header>

    <main class="p-4 pb-20">
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-6 max-w-lg mx-auto">
            
            <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
            <div id="success-alert" class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                <p><?= htmlspecialchars($success_message) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="space-y-6">
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                        <img id="image-preview" src="../uploads/profiles/<?= htmlspecialchars($user_data['foto_profil'] ?? 'placeholder.png') ?>" 
                             onerror="this.style.display='none'; document.getElementById('initial-placeholder').style.display='flex';"
                             alt="Foto Profil" class="w-full h-full object-cover">
                        <div id="initial-placeholder" class="w-full h-full bg-amber-100 text-amber-600 text-6xl font-bold items-center justify-center" style="display: <?= !empty($user_data['foto_profil']) ? 'none' : 'flex' ?>;">
                            <?= strtoupper(substr($user_data['username'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-4">
                        <label for="foto_profil" class="bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-full cursor-pointer hover:bg-gray-300 transition">
                            Ganti Foto
                        </label>
                        <?php if(!empty($user_data['foto_profil'])): ?>
                        <button type="button" id="hapus-foto-btn" class="text-red-600 hover:text-red-800 text-sm font-semibold">Hapus Foto</button>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="foto_profil" id="foto_profil" class="hidden" accept="image/*">
                    <input type="hidden" name="hapus_foto" id="hapus_foto_input" value="0">
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" value="<?= htmlspecialchars($user_data['username']) ?>" required class="mt-1 w-full px-4 py-2 rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_data['email']) ?>" required class="mt-1 w-full px-4 py-2 rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                </div>
                <div class="flex flex-col-reverse sm:flex-row gap-4 pt-4">
                    <a href="profil.php" class="w-full text-center bg-gray-200 text-gray-800 font-bold py-3 px-4 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </a>
                    <button type="submit" class="w-full bg-amber-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-amber-600 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('foto_profil');
    const previewImg = document.getElementById('image-preview');
    const initialPlaceholder = document.getElementById('initial-placeholder');
    const hapusBtn = document.getElementById('hapus-foto-btn');
    const hapusInput = document.getElementById('hapus_foto_input');
    const successAlert = document.getElementById('success-alert');

    // Pratinjau gambar saat memilih file baru
    inputFoto.addEventListener('change', function(event) {
        const [file] = event.target.files;
        if (file) {
            previewImg.src = URL.createObjectURL(file);
            previewImg.style.display = 'block';
            initialPlaceholder.style.display = 'none';
            if (hapusInput) hapusInput.value = '0'; 
            if (hapusBtn) hapusBtn.style.display = 'inline-block'; // Tampilkan lagi tombol hapus
        }
    });

    // Aksi saat tombol "Hapus Foto" diklik
    if (hapusBtn) {
        hapusBtn.addEventListener('click', function() {
            previewImg.style.display = 'none';
            initialPlaceholder.style.display = 'flex';
            hapusInput.value = '1';
            inputFoto.value = '';
            this.style.display = 'none';
        });
    }

    // PENAMBAHAN: Logika untuk notifikasi yang hilang otomatis
    if (successAlert) {
        setTimeout(() => {
            successAlert.classList.add('fade-out');
            // Hapus elemen dari DOM setelah transisi selesai
            setTimeout(() => {
                successAlert.remove();
            }, 500); // Durasi harus cocok dengan transisi di CSS
        }, 3000); // Notifikasi akan hilang setelah 3 detik
    }
    
    // PENAMBAHAN: Logika untuk memperbaiki masalah cache tombol "Batal"
    // Ini akan me-refresh halaman jika pengguna kembali menggunakan tombol back browser
    // untuk memastikan data yang ditampilkan selalu fresh dari server.
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
});
</script>
</body>
</html>