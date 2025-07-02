<?php
session_start();
require 'koneksi.php';

// Validasi sesi pengguna
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login/index.php");
    exit;
}

$id_user = $_SESSION['user_id'];
$username = $_SESSION['user_username'];

// [PERUBAHAN] Menambahkan query untuk data header
// Ambil data alamat user
$stmt_user = $conn->prepare("SELECT alamat FROM users WHERE id = ?");
$stmt_user->bind_param("i", $id_user);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();
$alamat_user = $user_data['alamat'] ?? '';
$stmt_user->close();

// Hitung item di keranjang
$cart_query = $conn->prepare("SELECT COUNT(id) as total_items FROM keranjang WHERE id_user = ?");
$cart_query->bind_param("i", $id_user);
$cart_query->execute();
$cart_result = $cart_query->get_result();
$cart_count = $cart_result->fetch_assoc()['total_items'] ?? 0;
$cart_query->close();
// Akhir penambahan query

// Ambil semua riwayat percakapan awal untuk user ini (logika tidak berubah)
$stmt = $conn->prepare("SELECT id, pesan, pengirim_role, waktu FROM chat WHERE id_user = ? ORDER BY waktu ASC");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$initial_messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Tandai pesan dari admin sebagai sudah dibaca
$update_stmt = $conn->prepare("UPDATE chat SET is_read = 1 WHERE id_user = ? AND pengirim_role = 'admin' AND is_read = 0");
$update_stmt->bind_param("i", $id_user);
$update_stmt->execute();
$update_stmt->close();

// Dapatkan ID pesan terakhir
$last_message_id = empty($initial_messages) ? 0 : end($initial_messages)['id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan Admin</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <style>
        .bg-primary { background-color: #f59e0b; }
        #chat-box { scroll-behavior: smooth; }
        /* [PERUBAHAN] Menyesuaikan padding body untuk header utama & nav bawah */
        body { padding-top: 90px; padding-bottom: 80px; }
    </style>
</head>
<body class="bg-pink-50 font-sans">

    <header class="fixed top-0 inset-x-0 bg-amber-500 p-4 text-white shadow-lg z-40">
        <div class="w-full max-w-screen-xl mx-auto flex justify-between items-center">
            <a href="profil.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-amber-500 text-2xl shadow">👤</div>
                <div>
                    <span class="font-semibold text-lg truncate max-w-[150px] sm:max-w-xs block group-hover:text-amber-200 transition-colors"><?= htmlspecialchars($username) ?></span>
                    <div class="text-xs flex items-center gap-1 opacity-90 group-hover:opacity-100 transition-opacity">
                        <i class="ph-map-pin-line"></i>
                        <span><?php if (!empty($alamat_user)) { echo htmlspecialchars(substr($alamat_user, 0, 35)) . '...'; } else { echo 'Atur Lokasi'; } ?></span>
                    </div>
                </div>
            </a>
            <div class="flex items-center gap-8">
                <nav class="hidden md:flex items-center gap-6 text-base font-semibold">
                    <a href="index.php" class="hover:text-amber-200 transition-colors">Home</a>
                    <a href="riwayat.php" class="hover:text-amber-200 transition-colors">Riwayat</a>
                    <a href="chat.php" class="text-amber-200 font-bold">Chat</a> <a href="profil.php" class="hover:text-amber-200 transition-colors">Profil</a>
                </nav>
                <a href="keranjang.php" class="p-2 relative">
                    <img src="cart.png" alt="Keranjang Belanja" class="w-10 h-10">
                    <span class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-amber-500 <?= $cart_count > 0 ? '' : 'hidden' ?>">
                        <?= $cart_count ?>
                    </span>
                </a>
            </div>
        </div>
    </header>

    <div class="p-4 max-w-screen-md mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <header class="bg-primary p-4 flex items-center text-white font-semibold text-lg border-b border-amber-600">
                <img src="https://placehold.co/40x40/FFFFFF/b45309?text=A" class="w-10 h-10 rounded-full mr-3" alt="Admin">
                <div>
                    <h1 class="font-bold">Admin Fairuz Food</h1>
                    <p class="text-xs opacity-80">Online</p>
                </div>
            </header>

            <main id="chat-box" class="overflow-y-auto p-4 space-y-4 h-96">
                <?php if (empty($initial_messages)): ?>
                    <div class="text-center text-gray-400 text-sm">Mulai percakapan dengan admin!</div>
                <?php else: ?>
                    <?php foreach ($initial_messages as $msg): ?>
                        <?php if ($msg['pengirim_role'] === 'user'): ?>
                            <div class="flex justify-end">
                                <div class="bg-amber-300 text-gray-800 rounded-lg rounded-br-none p-3 max-w-xs sm:max-w-sm shadow">
                                    <p class="break-words"><?= htmlspecialchars($msg['pesan']) ?></p>
                                    <p class="text-xs text-gray-600 mt-1 text-right"><?= date('H:i', strtotime($msg['waktu'])) ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex justify-start">
                                <div class="bg-gray-100 text-gray-800 rounded-lg rounded-bl-none p-3 max-w-xs sm:max-w-sm shadow">
                                    <p class="break-words"><?= htmlspecialchars($msg['pesan']) ?></p>
                                    <p class="text-xs text-gray-400 mt-1 text-left"><?= date('H:i', strtotime($msg['waktu'])) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>

            <footer class="p-4 bg-gray-50 border-t">
                <form id="chat-form" class="flex items-center gap-3">
                    <input type="text" id="pesan-input" placeholder="Ketik pesan..." required autocomplete="off" class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <button type="submit" id="submit-button" class="bg-primary text-white rounded-full p-3 hover:bg-amber-600 transition flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            </footer>
        </div>
    </div>


   <!-- Updated Mobile Navigation -->
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 md:hidden">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-around py-2">
            <a href="index.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs font-semibold">Beranda</span>
            </a>
            <a href="riwayat.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-history text-xl mb-1"></i>
                <span class="text-xs font-semibold">Riwayat</span>
            </a>
            <a href="chat.php" class="flex flex-col items-center py-2 relative text-amber-500 hover:text-amber-500 transition-colors">
    <i class="fas fa-comments text-xl mb-1"></i>
    <span class="text-xs font-semibold">Chat</span>
    <span id="chat-badge-mobile" class="absolute top-0 right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">0</span>
</a>

            <a href="profil.php" class="flex flex-col items-center py-2 text-gray-500 hover:text-amber-500 transition-colors">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-semibold">Profil</span>
            </a>
        </div>
    </div>
</nav>
<script>
// Script chat tidak ada yang diubah dan akan tetap berfungsi
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('chat-box');
    const chatForm = document.getElementById('chat-form');
    const pesanInput = document.getElementById('pesan-input');
    let lastMessageId = <?= $last_message_id ?>;

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function createMessageElement(msg) {
        const isSent = msg.pengirim_role === 'user';
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${isSent ? 'justify-end' : 'justify-start'}`;

        const bubble = document.createElement('div');
        bubble.className = `max-w-xs sm:max-w-sm p-3 rounded-lg shadow ${isSent ? 'bg-amber-300 text-gray-800 rounded-br-none' : 'bg-gray-100 text-gray-800 rounded-bl-none'}`;
        
        const text = document.createElement('p');
        text.className = 'break-words';
        text.textContent = msg.pesan;
        
        const time = document.createElement('p');
        time.className = `text-xs mt-1 ${isSent ? 'text-gray-600 text-right' : 'text-gray-400 text-left'}`;
        time.textContent = new Date(msg.waktu).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        bubble.appendChild(text);
        bubble.appendChild(time);
        wrapper.appendChild(bubble);
        return wrapper;
    }

    setTimeout(scrollToBottom, 100);

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const messageText = pesanInput.value.trim();
        if (messageText === '') return;

        const originalButton = this.querySelector('button');
        originalButton.disabled = true;
        pesanInput.value = '';

        const formData = new FormData();
        formData.append('pesan', messageText);

        fetch('kirim_pesan.php', {
            method: 'POST',
            body: formData,
            cache: 'no-cache'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                console.error('Gagal mengirim pesan:', data.message);
                pesanInput.value = messageText;
            } else {
                fetchNewMessages(true);
            }
        }).catch(error => {
            console.error('Error:', error);
            pesanInput.value = messageText;
        }).finally(() => {
            originalButton.disabled = false;
        });
    });

    function fetchNewMessages(forceScroll = false) {
        fetch(`ambil_pesan.php?last_id=${lastMessageId}`, {
            cache: 'no-cache'
        })
        .then(res => res.json())
        .then(messages => {
            if (messages.length > 0) {
                const loadingDiv = chatBox.querySelector('div.text-center');
                if (loadingDiv) loadingDiv.remove();

                messages.forEach(msg => {
                    const el = createMessageElement(msg);
                    chatBox.appendChild(el);
                    lastMessageId = msg.id;
                });

                if (forceScroll || messages.some(m => m.pengirim_role === 'admin')) {
                    scrollToBottom();
                }
            }
        })
        .catch(err => console.error('Gagal ambil pesan:', err));
    }

    setInterval(fetchNewMessages, 2000); // Interval 2 detik untuk polling
});

function updateChatBadge() {
    fetch('get_chat_notifikasi.php')
        .then(res => res.json())
        .then(data => {
            const count = data.unread_count || 0;
            const badgeMobile = document.getElementById('chat-badge-mobile');
            const badgeDesktop = document.getElementById('chat-badge-desktop');

            [badgeMobile, badgeDesktop].forEach(badge => {
                if (!badge) return;
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
        })
        .catch(err => console.error('Gagal mengambil chat badge:', err));
}

// Jalankan saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    updateChatBadge();
    // Perbarui badge setiap 10 detik
    setInterval(updateChatBadge, 10000);
});
</script>
</body>
</html>