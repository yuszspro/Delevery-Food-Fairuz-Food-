<?php
// 1. Mulai atau lanjutkan sesi yang ada.
// Ini wajib dilakukan untuk bisa mengakses dan menghancurkan sesi.
session_start();

// 2. Kosongkan semua variabel sesi.
// Menghapus semua data yang tersimpan di dalam array $_SESSION.
$_SESSION = array();

// 3. Hancurkan sesi.
// Menghapus ID sesi dari sisi server dan membersihkan cookie sesi di browser.
session_destroy();

// 4. Arahkan pengguna kembali ke halaman login.
// Setelah sesi dihancurkan, pengguna akan diarahkan ke halaman login utama.
header("Location: index.php");

// 5. Pastikan tidak ada kode lain yang dieksekusi setelah pengalihan.
exit;
?>
