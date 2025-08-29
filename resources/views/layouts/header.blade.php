<header class="bg-white shadow-sm">
    {{-- Baris paling atas --}}
    <div class="bg-gray-800 text-white py-1">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between md:justify-end items-center md:gap-6">
                {{-- Waktu & Tanggal --}}
                <div class="flex items-center space-x-2 text-xs sm:text-sm shrink-0">
                    <i class="far fa-calendar-alt"></i>
                    <span id="datetime-widget">Memuat waktu...</span>
                </div>
                {{-- Cuaca (Dinamis dari Server Laravel) --}}
                <div class="flex items-center space-x-2 text-xs sm:text-sm shrink-0">
                    <i id="weather-icon" class="fas fa-spinner fa-spin"></i>
                    <span id="weather-widget">Memuat cuaca...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Header Utama (Logo dan Ikon Sosial Media) --}}
    <div class="bg-white border-b">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center lg:justify-between items-center py-4">
                {{-- Kiri: Ikon Sosial Media --}}
                <div class="hidden lg:flex flex-1 justify-start">
                    <div class="flex items-center space-x-3">
                        <a href="https://www.facebook.com/brin.indonesia/" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-600 text-white hover:opacity-80"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/brin_indonesia/" class="h-8 w-8 rounded-full flex items-center justify-center bg-gradient-to-br from-purple-600 to-pink-500 text-white hover:opacity-80"><i class="fab fa-instagram"></i></a>
                        <a href="https://x.com/brin_indonesia" class="h-8 w-8 rounded-full flex items-center justify-center bg-gray-900 text-white hover:opacity-80"><i class="fab fa-x-twitter"></i></a>
                        <a href="https://www.youtube.com/channel/UCr1ihEI566IJib9P-JjENSA" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-600 text-white hover:opacity-80"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                {{-- Tengah: Logo --}}
                <div class="text-center">
                    <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight">
                        <span class="text-red-600">S</span><span class="text-gray-900">ISIRAJA</span>
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">
                        Sistem Informasi Sesar Jawa Bagian Barat
                    </p>
                </div>

                {{-- Kanan: Spacer --}}
                <div class="hidden lg:block flex-1"></div>
            </div>
        </div>
    </div>
</header>

{{-- ================================================================= --}}
{{--         SCRIPT UNTUK MENGAMBIL DATA CUACA DARI SERVER LARAVEL     --}}
{{-- ================================================================= --}}
<script>
    async function fetchLocalWeather() {
        const weatherWidget = document.getElementById('weather-widget');
        const weatherIcon = document.getElementById('weather-icon');
        
        // URL sekarang mengarah ke server Laravel kita sendiri
        const apiUrl = '/api/weather';

        try {
            const response = await fetch(apiUrl);
            if (!response.ok) {
                throw new Error('Server gagal merespons.');
            }
            
            const data = await response.json();

            // Cek jika ada error yang dikirim dari server
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Tampilkan data yang sudah matang dari server
            weatherWidget.textContent = `${data.location}, ${data.temperature}°C`;
            weatherIcon.className = getWeatherIconClass(data.icon);

        } catch (error) {
            console.error('Error fetching local weather:', error);
            weatherWidget.textContent = 'Gagal memuat cuaca';
            weatherIcon.className = 'fas fa-exclamation-circle text-red-500';
        }
    }

    // Fungsi ini tetap sama untuk mengubah kode ikon menjadi kelas FontAwesome
    function getWeatherIconClass(iconCode) {
        const iconMapping = { '01d': 'fas fa-sun text-yellow-400', '01n': 'fas fa-moon text-blue-200', '02d': 'fas fa-cloud-sun text-gray-400', '02n': 'fas fa-cloud-moon text-gray-400', '03d': 'fas fa-cloud text-gray-500', '03n': 'fas fa-cloud text-gray-500', '04d': 'fas fa-cloud-meatball text-gray-600', '04n': 'fas fa-cloud-meatball text-gray-600', '09d': 'fas fa-cloud-showers-heavy text-blue-500', '09n': 'fas fa-cloud-showers-heavy text-blue-500', '10d': 'fas fa-cloud-sun-rain text-blue-400', '10n': 'fas fa-cloud-moon-rain text-blue-400', '11d': 'fas fa-poo-storm text-yellow-600', '11n': 'fas fa-poo-storm text-yellow-600', '13d': 'fas fa-snowflake text-blue-300', '13n': 'fas fa-snowflake text-blue-300', '50d': 'fas fa-smog text-gray-500', '50n': 'fas fa-smog text-gray-500', };
        return iconMapping[iconCode] || 'fas fa-question-circle';
    }

    // Panggil fungsi yang baru
    document.addEventListener('DOMContentLoaded', fetchLocalWeather);
</script>