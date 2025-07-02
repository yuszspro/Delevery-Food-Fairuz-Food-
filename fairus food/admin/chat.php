<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['admin_username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/index.php");
    exit;
}

$target_user_id = $_GET['user_id'] ?? null;
$target_username = '';

if ($target_user_id) {
    // Tandai pesan dari user ini sebagai sudah dibaca oleh admin
    $update_stmt = $conn->prepare("UPDATE chat SET is_read = 1 WHERE id_user = ? AND pengirim_role = 'user' AND is_read = 0");
    $update_stmt->bind_param("i", $target_user_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Ambil username untuk ditampilkan di header
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $target_user_id);
    $stmt->execute();
    $user_res = $stmt->get_result();
    if($user_row = $user_res->fetch_assoc()) {
        $target_username = $user_row['username'];
    }
    $stmt->close();
}

// Hitung ulang notifikasi setelah pesan ditandai terbaca
$unread_nav_query = "SELECT COUNT(id) as total_unread FROM chat WHERE is_read = 0 AND pengirim_role = 'user'";
$unread_nav_result = mysqli_query($conn, $unread_nav_query);
$total_unread_nav = mysqli_fetch_assoc($unread_nav_result)['total_unread'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Chat Admin | Fairuz Food</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .chat-bubble-sent { background-color: #fef3c7; } /* Pesan dari Admin */
        .chat-bubble-received { background-color: #e5e7eb; } /* Pesan dari User */
    </style>
</head>
<body class="bg-gray-100 h-screen font-sans">

    <div class="flex h-full">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0 flex flex-col">
            <div class="p-4 border-b border-gray-700"><h1 class="text-2xl font-bold">Admin Panel</h1></div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="daftar_pesanan.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Pesanan</a>
                <a href="index.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Produk</a>
                <a href="chat.php" class="relative block py-2.5 px-4 rounded transition duration-200 bg-amber-500 font-semibold">
                    Chat
                    <span id="main-chat-notification" class="<?= $total_unread_nav > 0 ? '' : 'hidden' ?> absolute top-3 right-3 w-2.5 h-2.5 bg-orange-500 rounded-full animate-pulse"></span>
                </a>
            </nav>
            <div class="p-4 border-t border-gray-700"><a href="../login/logout.php" class="block text-center py-2.5 px-4 rounded transition duration-200 bg-red-600 hover:bg-red-700">Logout</a></div>
        </aside>

        <div class="flex-1 flex h-full">
            <aside class="w-1/3 h-full bg-white border-r flex flex-col">
                <header class="p-4 border-b font-bold text-lg text-gray-700 flex-shrink-0">Percakapan</header>
                <div id="conversation-list" class="flex-1 overflow-y-auto">
                    <p class="p-4 text-center text-gray-400">Memuat percakapan...</p>
                </div>
            </aside>

            <main class="w-2/3 h-full flex flex-col bg-gray-50">
                <?php if ($target_user_id): ?>
                    <header class="bg-white p-4 flex items-center shadow-sm z-10 flex-shrink-0">
                        <h2 class="font-bold text-lg text-gray-800">Chat dengan <?= htmlspecialchars($target_username) ?></h2>
                    </header>
                    <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-4"></div>
                    <footer class="p-4 bg-gray-200 flex-shrink-0">
                        <form id="reply-form" class="flex items-center gap-3">
                            <input type="hidden" name="id_user" value="<?= $target_user_id ?>">
                            <input type="text" id="message-input" name="pesan" placeholder="Ketik balasan sebagai admin..." required autocomplete="off" class="w-full px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <button type="submit" class="bg-amber-500 text-white rounded-full p-3 hover:bg-amber-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" /></svg>
                            </button>
                        </form>
                    </footer>
                <?php else: ?>
                    <div class="flex-1 flex items-center justify-center text-center text-gray-500">
                        <div><i class="ph-chats-circle text-6xl"></i><p class="mt-2">Pilih percakapan untuk memulai.</p></div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const conversationListContainer = document.getElementById('conversation-list');
    const mainChatNotification = document.getElementById('main-chat-notification');
    const currentTargetUserId = <?= json_encode($target_user_id) ?>;

    function escapeHTML(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    async function updateConversationList() {
        try {
            const response = await fetch('ambil_percakapan.php');
            const conversations = await response.json();
            
            let totalUnread = 0;
            conversationListContainer.innerHTML = ''; 
            if (conversations.length > 0) {
                conversations.forEach(user => {
                    const isActive = currentTargetUserId == user.id ? 'bg-amber-100' : 'hover:bg-gray-50';
                    const unreadDot = user.unread_count > 0 ? `<span class="w-2.5 h-2.5 bg-orange-500 rounded-full flex-shrink-0"></span>` : '';
                    if (user.unread_count > 0) totalUnread++;
                    
                    let avatarHTML = '';
                    if (user.foto_profil) {
                        avatarHTML = `<img src="../uploads/profiles/${escapeHTML(user.foto_profil)}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">`;
                    } else {
                        avatarHTML = `<div class="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold flex-shrink-0">${escapeHTML(user.username.charAt(0).toUpperCase())}</div>`;
                    }

                    const userLink = `<a href="?user_id=${user.id}" class="relative flex items-center gap-3 p-4 border-b ${isActive}">${avatarHTML}<div class="flex-1 overflow-hidden"><p class="font-semibold text-gray-800 truncate">${escapeHTML(user.username)}</p><p class="text-xs text-gray-500 truncate">${escapeHTML(user.last_message)}</p></div>${unreadDot}</a>`;
                    conversationListContainer.innerHTML += userLink;
                });
            } else {
                conversationListContainer.innerHTML = '<p class="p-4 text-center text-gray-400">Belum ada percakapan.</p>';
            }
            mainChatNotification.style.display = totalUnread > 0 ? 'block' : 'none';
        } catch (error) {
            console.error("Gagal update percakapan:", error);
        }
    }

    <?php if ($target_user_id): ?>
    const chatBox = document.getElementById('chat-box');
    const replyForm = document.getElementById('reply-form');
    const messageInput = document.getElementById('message-input');
    const targetUserId = <?= $target_user_id ?>;

    function scrollToBottom() { chatBox.scrollTop = chatBox.scrollHeight; }

    // --- PERBAIKAN PADA FUNGSI INI ---
    async function fetchAdminMessages() {
        try {
            // Panggil file yang benar untuk mengambil pesan
            const response = await fetch(`ambil_pesan_admin.php?user_id=${targetUserId}`);
            if (!response.ok) return;

            const messages = await response.json();
            const isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 100;
            
            chatBox.innerHTML = ''; // Kosongkan chat box sebelum mengisi dengan yang baru
            if (messages.length > 0) {
                messages.forEach(msg => {
                    const isSentByAdmin = msg.pengirim_role === 'admin';
                    const bubble = `
                        <div class="flex ${isSentByAdmin ? 'justify-end' : 'justify-start'}">
                            <div class="max-w-xs md:max-w-lg p-3 rounded-lg shadow-sm ${isSentByAdmin ? 'chat-bubble-sent text-gray-800 rounded-br-none' : 'chat-bubble-received text-gray-800 rounded-bl-none'}">
                                <p class="break-words">${escapeHTML(msg.pesan)}</p>
                                <p class="text-xs mt-1 opacity-60 ${isSentByAdmin ? 'text-right' : 'text-left'}">${new Date(msg.waktu).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</p>
                            </div>
                        </div>`;
                    chatBox.innerHTML += bubble;
                });
            } else {
                chatBox.innerHTML = '<p class="text-center text-gray-400">Belum ada pesan di percakapan ini.</p>';
            }

            if (isScrolledToBottom) { 
                setTimeout(scrollToBottom, 0); // Scroll ke bawah jika sebelumnya sudah di bawah
            }
        } catch (error) { 
            console.error("Gagal mengambil pesan:", error); 
            chatBox.innerHTML = '<p class="text-center text-red-500">Gagal memuat pesan.</p>';
        }
    }

    replyForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;
        
        const formData = new FormData(replyForm);
        messageInput.value = ''; // Langsung kosongkan

        try {
            // Pastikan path mengarah ke file kirim pesan admin
            await fetch('kirim_pesan_admin.php', { method: 'POST', body: formData });
            await fetchAdminMessages(); // Muat ulang chat box
            await updateConversationList(); // Update daftar percakapan di kiri
        } catch (error) { 
            alert('Gagal mengirim balasan.'); 
            messageInput.value = message; // Kembalikan pesan jika gagal
        }
    });

    // Jalankan pengambilan pesan saat halaman dimuat dan setiap beberapa detik
    fetchAdminMessages();
    setInterval(fetchAdminMessages, 3000); // Polling untuk pesan baru
    <?php endif; ?>

    // Jalankan update daftar percakapan dan set interval
    updateConversationList();
    setInterval(updateConversationList, 5000); // Polling untuk daftar percakapan
});
</script>
</body>
</html>