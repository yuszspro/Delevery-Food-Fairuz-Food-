<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | Fairuz Food</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Animasi pembuka (ripple) tidak berubah */
        #animation-container { position: fixed; inset: 0; z-index: 50; display: flex; justify-content: center; align-items: center; transition: opacity 0.5s ease-in-out; }
        #drop { position: absolute; width: 12px; height: 12px; background: rgba(255, 255, 255, 0.7); border-radius: 50%; opacity: 0; left: 50%; top: -30px; transform: translateX(-50%); animation: drop-fall 1.2s cubic-bezier(0.55, 0.085, 0.68, 0.53) forwards; }
        @keyframes drop-fall { 0% { opacity: 1; transform: translateX(-50%) translateY(0); } 100% { opacity: 1; transform: translateX(-50%) translateY(calc(50vh - 6px)); } }
        #ripple-logo { opacity: 0; transform: scale(0.5); transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        #ripple-logo.visible { opacity: 1; transform: scale(1); }
        .ripple { position: absolute; border: 2px solid rgba(255, 255, 255, 0.6); border-radius: 50%; opacity: 1; transform: scale(0); }
        @keyframes ripple-effect { to { transform: scale(4); opacity: 0; } }

        /* PENAMBAHAN: Animasi getar untuk form saat error */
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
        .shake {
            animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
        }
    </style>
</head>
<body class="min-h-screen bg-[#fde2e2] flex items-center justify-center p-4">
    <div id="animation-container">
        <div id="drop"></div>
        <img id="ripple-logo" src="ff.png" alt="Logo" class="w-40 relative z-10" />
    </div>

    <div id="login-card" class="bg-white/40 backdrop-blur-lg border border-white/20 w-full max-w-sm px-8 py-8 rounded-2xl shadow-lg opacity-0" style="transition: opacity 1s ease-in-out;">
        <div class="text-center">
            <img src="ff.png" alt="Logo" class="w-40 mx-auto mb-6" />
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang</h1>
            <p id="message-display" class="text-gray-700 text-sm mb-6 h-5 transition-colors duration-300">Silakan masuk ke akun Anda.</p>
        </div>

        <form id="login-form" action="login_phone.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block mb-1 text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required
                       class="w-full px-4 py-3 bg-white/50 border border-gray-300/50 rounded-lg text-sm placeholder-gray-600 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition" />
            </div>
            <div>
                <label for="password" class="block mb-1 text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required
                       class="w-full px-4 py-3 bg-white/50 border border-gray-300/50 rounded-lg text-sm placeholder-gray-600 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition" />
            </div>
            <div class="pt-2">
                <button type="submit" id="login-button" class="w-full bg-gray-800 text-white rounded-full py-3 font-bold cursor-pointer hover:bg-gray-900 transition-colors duration-300 shadow-md">
                    Login
                </button>
            </div>
        </form>

        <div class="flex items-center my-5"><div class="flex-grow border-t border-gray-200/50"></div><span class="mx-4 text-gray-600 text-xs font-semibold">OR</span><div class="flex-grow border-t border-gray-200/50"></div></div>
        <div class="text-center text-sm mt-6"><span class="text-gray-600">Belum punya akun?</span><a href="user_signup.php" class="font-bold text-gray-800 hover:underline">Daftar di sini</a></div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const animationContainer = document.getElementById('animation-container');
    const loginCard = document.getElementById('login-card');
    const loginForm = document.getElementById('login-form');
    const loginButton = document.getElementById('login-button');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const messageDisplay = document.getElementById('message-display');

    // --- LOGIKA ANIMASI PEMBUKA ---
    (function runOpeningAnimation() {
        const drop = document.getElementById('drop');
        const rippleLogo = document.getElementById('ripple-logo');
        if (!animationContainer) return;
        
        const dropDuration = 1200;
        setTimeout(() => {
            if(drop) drop.style.display = 'none';
            if(rippleLogo) rippleLogo.classList.add('visible');
            for (let i = 0; i < 3; i++) {
                setTimeout(() => {
                    const ripple = document.createElement('div');
                    ripple.classList.add('ripple');
                    animationContainer.appendChild(ripple);
                    ripple.style.animation = `ripple-effect 1s ease-out forwards`;
                    setTimeout(() => ripple.remove(), 1000);
                }, i * 200);
            }
        }, dropDuration);

        const totalAnimationTime = dropDuration + 1500; 
        setTimeout(() => {
            animationContainer.style.opacity = '0';
            setTimeout(() => animationContainer.style.display = 'none', 500);
            loginCard.style.opacity = '1';
        }, totalAnimationTime);
    })();


    // --- LOGIKA LOGIN AJAX (TANPA REFRESH) ---
    loginForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Mencegah form submit dan refresh halaman
        
        // Tampilkan status loading
        loginButton.textContent = 'Memproses...';
        loginButton.disabled = true;

        const formData = new FormData(loginForm);

        fetch('login_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Jika sukses, tampilkan pesan sukses dan arahkan
                messageDisplay.textContent = data.message;
                messageDisplay.classList.remove('text-red-600');
                messageDisplay.classList.add('text-green-600');
                loginButton.textContent = 'Berhasil!';
                
                window.location.href = data.redirect_url;

            } else {
                // Jika gagal, tampilkan pesan error
                messageDisplay.textContent = data.message;
                messageDisplay.classList.add('text-red-600', 'font-semibold');
                
                // Tambahkan border merah dan efek getar
                usernameInput.classList.add('border-red-500');
                passwordInput.classList.add('border-red-500');
                loginCard.classList.add('shake');

                // Kembalikan tombol ke keadaan normal
                loginButton.textContent = 'Login';
                loginButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDisplay.textContent = 'Tidak dapat terhubung ke server.';
            messageDisplay.classList.add('text-red-600', 'font-semibold');
            loginButton.textContent = 'Login';
            loginButton.disabled = false;
        });
    });

    // Fungsi untuk menghapus error saat pengguna mulai mengetik lagi
    function clearErrorState() {
        messageDisplay.textContent = 'Silakan masuk ke akun Anda.';
        messageDisplay.classList.remove('text-red-600', 'font-semibold');
        usernameInput.classList.remove('border-red-500');
        passwordInput.classList.remove('border-red-500');
        loginCard.classList.remove('shake');
    }

    usernameInput.addEventListener('input', clearErrorState);
    passwordInput.addEventListener('input', clearErrorState);
});
</script>

</body>
</html>