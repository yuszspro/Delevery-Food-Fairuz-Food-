<?php
session_start();
require '../admin/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit;
}

// Ambil lokasi tersimpan dari user
$id_user = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT alamat_lat, alamat_lng FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user_loc = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Pilih Lokasi Pengiriman</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.css" />
    <script src="https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.umd.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .bg-primary { background-color: #f59e0b; }
        #map { height: 100vh; }
        .leaflet-top.leaflet-left {
            top: 80px; 
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 2rem);
            max-width: 600px;
        }
        .leaflet-control-geosearch form {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 9999px;
            padding-left: 10px;
            height: 48px;
            width: 100%;
        }
        .leaflet-control-geosearch form input {
            height: 100%;
            border: none;
            outline: none;
            padding-left: 10px;
            width: calc(100% - 50px);
        }
        .leaflet-bar a.leaflet-control-geosearch-button {
            border-radius: 9999px !important;
            border: none;
            width: 48px;
            height: 48px;
            line-height: 48px;
        }
        .leaflet-control-geosearch .results {
            margin-top: 10px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="font-sans antialiased overflow-hidden">
    <div id="map"></div>

    <header class="absolute top-0 left-0 right-0 p-4 z-[1000] bg-amber-500 flex items-center text-white font-semibold text-lg shadow-md h-16">
        <a href="javascript:history.back()" class="mr-4 text-2xl p-1 rounded-full hover:bg-black/10 transition">&larr;</a>
        <h1>Lokasi Anda Sekarang</h1>
    </header>
    
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-full z-[999] pointer-events-none flex flex-col items-center">
        <svg class="w-12 h-12 drop-shadow-lg" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#f59e0b" d="M24 0C15.163 0 8 7.163 8 16c0 8.837 16 32 16 32s16-23.163 16-32C40 7.163 32.837 0 24 0Z"/>
            <circle cx="24" cy="16" r="7" fill="#fff"/>
        </svg>
        <div class="w-4 h-4 bg-black/20 rounded-full -mt-2 blur-sm"></div>
    </div>

    <div class="absolute bottom-0 left-0 right-0 z-[1000] p-4 bg-gradient-to-t from-black/20 to-transparent">
        <div class="bg-white rounded-xl shadow-2xl p-4">
            <div class="flex items-start gap-3 mb-3">
                <i class="ph-map-trifold text-2xl text-amber-500 mt-1 flex-shrink-0"></i>
                <div>
                    <h3 class="font-bold text-gray-800">Alamat Pilihan</h3>
                    <p id="address-text" class="text-sm text-gray-600">Geser peta untuk memilih alamat...</p>
                </div>
            </div>
            <button id="confirm-button" disabled class="w-full py-3 bg-primary text-white font-bold rounded-lg hover:bg-amber-600 transition text-lg shadow-lg disabled:bg-gray-300 disabled:cursor-not-allowed">
                Tetapkan Alamat
            </button>
        </div>
    </div>

    <div id="loading" class="fixed inset-0 bg-white flex items-center justify-center z-[9999]">
        <div class="text-center">
            <i class="ph-spinner-gap text-5xl text-amber-500 animate-spin"></i>
            <p class="mt-3 text-gray-600 font-semibold">Mencari lokasi Anda...</p>
        </div>
    </div>
    
    <div id="toast" class="hidden fixed bottom-40 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-center px-6 py-3 rounded-lg font-semibold shadow-xl z-[9999] transition-opacity duration-300 opacity-0"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingOverlay = document.getElementById('loading');
    const addressText = document.getElementById('address-text');
    const confirmButton = document.getElementById('confirm-button');
    let map, currentAddress, currentLat, currentLng, geocodeTimer;

    function initMap(lat, lon) {
        map = L.map('map', { center: [lat, lon], zoom: 17, zoomControl: false });
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        }).addTo(map);

        const searchControl = new GeoSearch.GeoSearchControl({
            provider: new GeoSearch.OpenStreetMapProvider(),
            style: 'bar',
            showMarker: false, 
            autoClose: true,
            searchLabel: 'Cari alamat atau tempat...',
            notFoundMessage: 'Maaf, alamat tidak ditemukan.',
        });
        map.addControl(searchControl);
        
        map.on('moveend', () => {
            clearTimeout(geocodeTimer);
            geocodeTimer = setTimeout(() => {
                const center = map.getCenter();
                updateAddress(center.lat, center.lng);
            }, 500);
        });

        map.on('geosearch/showlocation', function(result) {
            const { y, x } = result.location;
            map.setView([y, x], 17);
        });
        
        updateAddress(lat, lon);
        loadingOverlay.style.display = 'none';
    }

    async function updateAddress(lat, lon) {
        currentLat = lat;
        currentLng = lon;
        addressText.textContent = "Mencari alamat...";
        confirmButton.disabled = true;
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
            const data = await response.json();
            if (data && data.display_name) {
                currentAddress = data.display_name;
                addressText.textContent = currentAddress;
                confirmButton.disabled = false;
            } else {
                addressText.textContent = 'Alamat tidak ditemukan.';
            }
        } catch(err) {
            addressText.textContent = 'Gagal memuat alamat.';
        }
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.remove('hidden', 'opacity-0');
        setTimeout(() => toast.classList.add('opacity-0'), 2500);
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    confirmButton.addEventListener('click', function() {
        if(!currentAddress) return;
        confirmButton.disabled = true;
        confirmButton.textContent = 'Menyimpan...';
        
        const formData = new FormData();
        formData.append('alamat', currentAddress);
        formData.append('lat', currentLat);
        formData.append('lng', currentLng);

        fetch('simpan_alamat.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('Alamat berhasil ditetapkan!');
                    // Kembali ke halaman sebelumnya setelah berhasil
                    setTimeout(() => { javascript:history.back() }, 1500);
                } else {
                    showToast('Gagal menyimpan: ' + data.message);
                    confirmButton.disabled = false;
                    confirmButton.textContent = 'Tetapkan Alamat';
                }
            })
            .catch(() => {
                showToast('Terjadi kesalahan koneksi.');
                confirmButton.disabled = false;
                confirmButton.textContent = 'Tetapkan Alamat';
            });
    });

    const savedLat = <?= json_encode($user_loc['alamat_lat'] ?? null) ?>;
    const savedLng = <?= json_encode($user_loc['alamat_lng'] ?? null) ?>;

    if (savedLat && savedLng) {
        initMap(savedLat, savedLng);
    } else {
        if (!navigator.geolocation) {
            initMap(-6.9175, 107.6191); // Lokasi default Bandung
        } else {
            navigator.geolocation.getCurrentPosition(
                (position) => initMap(position.coords.latitude, position.coords.longitude),
                () => {
                    initMap(-6.9175, 107.6191); 
                    showToast("Gagal dapatkan lokasi, menampilkan lokasi default.");
                }
            );
        }
    }
});
</script>
</body>
</html>
