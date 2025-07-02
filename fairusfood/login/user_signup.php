<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Akun | Fairuz Food</title>
    <link rel="icon" href="ff.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
        .shake { animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both; }
    </style>
</head>
<body class="min-h-screen bg-[#fde2e2] flex items-center justify-center p-4">

    <div id="signup-card" class="bg-white/40 backdrop-blur-lg border border-white/20 w-full max-w-sm px-8 py-8 rounded-2xl shadow-lg">
        <div class="text-center">
            <img src="ff.png" alt="Logo" class="w-40 mx-auto mb-6" />
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Buat Akun Baru</h1>
            <p id="message-display" class="text-gray-700 text-sm mb-6 h-5 transition-colors duration-300">Mulai perjalanan kulinermu di sini.</p>
        </div>

        <form id="signup-form" action="proses_signup.php" method="POST" class="space-y-4">
            <input type="text" name="username" id="username" placeholder="Username" required
                   class="w-full px-4 py-3 bg-white/50 border border-gray-300/50 rounded-lg text-sm placeholder-gray-600 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition" />
            
            <input type="email" name="email" id="email" placeholder="Email" required
                   class="w-full px-4 py-3 bg-white/50 border border-gray-300/50 rounded-lg text-sm placeholder-gray-600 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition" />

            <input type="tel" name="phone" id="phone" placeholder="Nomor Telepon" required
                   class="w-full px-4 py-3 bg-white/50 border border-gray-300/50 rounded-lg text-sm placeholder-gray-600 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition" />

            <input type="password" name="password" id="password" placeholder="Password" required
                   class="w-full px-4 py-3 bg-white/50 border border-gray-300/50 rounded-lg text-sm placeholder-gray-600 text-gray-800 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition" />
            
            <button type="submit" id="signup-button" class="w-full bg-gray-800 text-white rounded-full py-3 font-bold cursor-pointer hover:bg-gray-900 transition-colors duration-300 shadow-md !mt-5">
                Daftar
            </button>
        </form>

        <div class="flex items-center my-5"><div class="flex-grow border-t border-gray-200/50"></div><span class="mx-4 text-gray-600 text-xs font-semibold">OR</span><div class="flex-grow border-t border-gray-200/50"></div></div>
        <div class="text-center text-sm mt-6"><span class="text-gray-600">Sudah punya akun?</span><a href="index.php" class="font-bold text-gray-800 hover:underline">Login di sini</a></div>
    </div>

    <div id="toast" 
         class="hidden fixed bottom-10 inset-x-4 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 bg-green-500 text-white text-center px-6 py-3 rounded-full font-bold shadow-lg transition-all duration-300 opacity-0 -translate-y-4">
    </div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const signupForm = document.getElementById('signup-form');
    const signupButton = document.getElementById('signup-button');
    const messageDisplay = document.getElementById('message-display');
    const signupCard = document.getElementById('signup-card');
    const toast = document.getElementById('toast');
    const allInputs = signupForm.querySelectorAll('input');

    signupForm.addEventListener('submit', function (event) {
        event.preventDefault();
        
        signupButton.textContent = 'Memproses...';
        signupButton.disabled = true;
        clearErrorState();

        const formData = new FormData(signupForm);

        fetch('proses_signup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                toast.textContent = data.message;
                toast.classList.remove('hidden');
                setTimeout(() => {
                    toast.classList.remove('opacity-0', '-translate-y-4');
                }, 10);

                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2500);

            } else {
                messageDisplay.textContent = data.message;
                messageDisplay.classList.add('text-red-600', 'font-semibold');
                signupCard.classList.add('shake');
                allInputs.forEach(input => {
                    input.classList.add('border-red-500');
                });
                
                signupButton.textContent = 'Daftar';
                signupButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDisplay.textContent = 'Tidak dapat terhubung ke server.';
            messageDisplay.classList.add('text-red-600', 'font-semibold');
            signupButton.textContent = 'Daftar';
            signupButton.disabled = false;
        });
    });

    function clearErrorState() {
        messageDisplay.textContent = 'Mulai perjalanan kulinermu di sini.';
        messageDisplay.classList.remove('text-red-600', 'font-semibold');
        signupCard.classList.remove('shake');
        allInputs.forEach(input => {
            input.classList.remove('border-red-500');
        });
    }

    allInputs.forEach(input => {
        input.addEventListener('input', clearErrorState);
    });
});
</script>

</body>
</html>