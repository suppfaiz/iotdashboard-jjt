@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto flex gap-8">
    <!-- Sidebar Navigation -->
    <aside class="hidden lg:block w-64 shrink-0 sticky top-24 self-start">
        <nav class="space-y-1">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider px-3 mb-2">Documentation</p>
            <a href="#architecture" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                1. System Architecture
            </a>
            <a href="#provisioning" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                2. Device Provisioning
            </a>
            <a href="#mqtt-websocket" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                3. MQTT & WebSockets
            </a>
            <a href="#ota-update" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                4. OTA Firmware Updates
            </a>
            <a href="#scheduler" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                5. Scheduler & Logging
            </a>
            <a href="#vps-setup" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                6. VPS Deployment Guide
            </a>
            <a href="#tv-kiosk" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                7. TV Kiosk Mode
            </a>
            <a href="#floor-map" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                8. 3D Building Floor Map
            </a>
            <a href="#gemini-ai" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                9. Gemini AI Chatbot
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
        <div class="border-b border-gray-100 pb-6 mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Admin & Developer Documentation</h1>
            <p class="text-gray-500 mt-2">Technical manual for managing, provisioning, and updating Jamkrida Energy IoT infrastructure.</p>
        </div>

        <div class="space-y-12">
            <!-- Section 1: System Architecture -->
            <section id="architecture" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">1</span>
                    System Architecture
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Jamkrida Energy Platform uses a high-performance, real-time distributed architecture to fetch, store, and stream electricity usage metrics from physical microcontroller nodes (ESP32/ESP8266 + PZEM-004T).
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-4">
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Core Technical Stack:</h4>
                    <ul class="list-disc pl-5 space-y-2 text-sm text-gray-600">
                        <li><strong>Firmware:</strong> Arduino C++ listening to PZEM-004T v3 sensor over UART.</li>
                        <li><strong>Ingress Protocol:</strong> MQTT over TCP (broker.emqx.io or customized instances).</li>
                        <li><strong>Backend Listener:</strong> Continuous PHP Artisan command daemon daemonized on server.</li>
                        <li><strong>Reactive Layer:</strong> Laravel Reverb (WebSocket server) with Pusher Protocol wrapper.</li>
                        <li><strong>Frontend State:</strong> Vanilla Javascript Echo client with Alpine.js reactivity wrappers.</li>
                    </ul>
                </div>
            </section>

            <!-- Section 2: Device Provisioning -->
            <section id="provisioning" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">2</span>
                    Device Provisioning & C++ Template
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    When you add a new device through the Admin Dashboard, the system dynamically calculates a unique ID (prefixed with <code>dev_</code>) and generates a tailor-made C++ script.
                </p>
                <div class="space-y-4">
                    <div class="border-l-4 border-amber-500 bg-amber-50 p-4 rounded-r-xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-bold text-amber-800">C++ Hardware Requirements</h3>
                                <p class="text-xs text-amber-700 mt-1">Make sure PZEM-004T v3 Rx and Tx pins are properly cross-wired to the microcontroller’s configured UART Serial Pins (typically GPIO 16/17 on ESP32 or SoftwareSerial on ESP8266).</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Step-by-Step Provisioning Flow:</h4>
                        <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                            <li>Go to the main <strong>Dashboard</strong> and click <strong>Add Device</strong>.</li>
                            <li>Fill in name, area group, SSID, and Wi-Fi password.</li>
                            <li>The dashboard will generate and display a ready-to-copy code containing unique telemetry MQTT topics.</li>
                            <li>Upload the code to your microchip. As soon as the chip connects, the browser registers telemetry, displaying a success banner.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Section 3: MQTT & WebSockets -->
            <section id="mqtt-websocket" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">3</span>
                    Real-time Pipeline (MQTT & WebSockets)
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Data sent by the devices flows through a pipeline before reaching the user's dashboard screen in real-time.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">1. Inbound MQTT Payload</h4>
                        <p class="text-xs text-gray-500 mb-2 font-mono">Topic: telemetry/operational-area/device_id</p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto"><code>{
  "voltage": 224.5,
  "current": 1.45,
  "power": 325.5,
  "energy": 12.435
}</code></pre>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                        <h4 class="text-sm font-bold text-gray-800 mb-2">2. Processing & WebSocket Dispatch</h4>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">
                            The background command daemon <code>php artisan mqtt:listen</code> receives the JSON data, updates Redis/Database caches, and fires the <code>TelemetryUpdated</code> Laravel Event.
                        </p>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Laravel Reverb immediately broadcasts it over the <code>telemetry.{device_id}</code> WebSocket channel.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Section 4: OTA Firmware Updates -->
            <section id="ota-update" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">4</span>
                    OTA Firmware Updates
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    You can flash new binary firmware directly to the devices over Wi-Fi, removing the need to connect physical type-C or serial cables to microchips.
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-1">To Perform an OTA Update:</h4>
                        <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                            <li>Compile your updated firmware in Arduino IDE or VSCode PlatformIO to get a <code>.bin</code> file.</li>
                            <li>Navigate to the device's <strong>Detail View</strong> and look for the <strong>OTA Firmware Update</strong> section.</li>
                            <li>Upload the <code>.bin</code> file. The system stores this file securely in the public storage directory.</li>
                            <li>Click the <strong>Trigger OTA</strong> button. This publishes an MQTT command to the device.</li>
                        </ol>
                    </div>
                    <div class="bg-gray-800 rounded p-4 text-xs text-white">
                        <span class="text-gray-400 block font-mono mb-1">// Outgoing MQTT trigger payload published to cmd/{device_id}</span>
                        <pre class="font-mono overflow-x-auto"><code>{
  "cmd": "update_firmware",
  "url": "http://127.0.0.1:8000/storage/firmwares/firmware_dev_xxxxx.bin"
}</code></pre>
                    </div>
                    <p class="text-xs text-gray-500 italic">Note: The target ESP32/ESP8266 will parse this command, start an HTTP client connection to the URL, and execute the standard Arduino OTA Update sequence before self-rebooting.</p>
                </div>
            </section>

            <!-- Section 5: Scheduler -->
            <section id="scheduler" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">5</span>
                    Scheduler & Database Historical Logging
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Rather than writing database telemetry records every second (which would overload standard SQL engines), the system tracks real-time volatile metrics in Cache memory. Two scheduled commands run automatically to save historical snapshots:
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-1">1. Hourly energy log:</h4>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">
                            Runs at the start of every hour to capture the current metrics (V, A, W, kWh) and save them to the <code>hourly_energy_logs</code> table.
                        </p>
                        <div class="bg-gray-800 rounded p-4 text-xs font-mono text-white">
                            <span class="text-gray-400 block mb-1"># Run hourly log command</span>
                            <code>php artisan energy:log-hourly</code>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-1">2. Daily energy log:</h4>
                        <p class="text-sm text-gray-600 leading-relaxed mb-2">
                            Runs daily at 23:59 to capture the final accumulated daily energy consumption (kWh) of each active device and save it to the <code>daily_energy_logs</code> table.
                        </p>
                        <div class="bg-gray-800 rounded p-4 text-xs font-mono text-white">
                            <span class="text-gray-400 block mb-1"># Run daily log command</span>
                            <code>php artisan energy:log-daily</code>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 6: VPS Deployment Guide -->
            <section id="vps-setup" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">6</span>
                    VPS Deployment Guide (Docker & SSL)
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Panduan langkah-demi-langkah yang rinci dan teratur untuk melakukan deployment sistem Jamkrida Energy ke server VPS produksi berbasis Linux (Ubuntu/Debian).
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-6">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Langkah 1: Persiapan Server & Prasyarat</h4>
                        <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                            Jalankan perintah ini di terminal VPS Anda untuk menginstal Docker, Docker Compose, Git, dan Node.js/NPM:
                        </p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code># Update sistem
sudo apt update && sudo apt upgrade -y

# Install Git, Curl, Node.js & NPM
sudo apt install -y git curl nodejs npm

# Install Docker & Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh</code></pre>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Langkah 2: Kloning Repositori & Setup Environment</h4>
                        <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                            Salin repositori ke VPS Anda, salin berkas konfigurasi, lalu sesuaikan parameter produksi:
                        </p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code># Clone repo
git clone https://github.com/suppfaiz/iotdashboard-jjt.git
cd iotdashboard-jjt

# Salin env example ke env aktif
cp .env.example .env

# Edit berkas .env
nano .env</code></pre>
                        <div class="mt-2 text-xs text-gray-500 font-medium">Pengaturan Penting di <code>.env</code> VPS:</div>
                        <ul class="list-disc pl-5 mt-1 space-y-1 text-xs text-gray-600">
                            <li><code>APP_ENV=production</code></li>
                            <li><code>APP_DEBUG=false</code></li>
                            <li><code>APP_URL=https://domain-vps-anda.com</code></li>
                            <li><code>VITE_REVERB_SCHEME=https</code></li>
                            <li><code>VITE_REVERB_PORT=443</code></li>
                            <li>Ganti <code>DB_PASSWORD</code> dan <code>MQTT_PASSWORD</code> dengan password yang acak dan kuat.</li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Langkah 3: Kompilasi Aset Frontend di VPS Host</h4>
                        <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                            Kompilasi aset UI frontend Anda untuk performa produksi sebelum membangun container Docker:
                        </p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code># Install dependencies node
npm install

# Jalankan build production untuk aset Javascript/CSS
npm run build</code></pre>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Langkah 4: Jalankan Container Docker</h4>
                        <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                            Mulai seluruh layanan (Aplikasi, MySQL, Mosquitto MQTT Broker, phpMyAdmin) menggunakan Docker Compose:
                        </p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code># Jalankan container di background & lakukan build image
docker compose up -d --build</code></pre>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Langkah 5: Konfigurasi Nginx di Host & SSL Gratis (Certbot)</h4>
                        <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                            Supaya aplikasi Anda dapat diakses lewat domain HTTPS dan koneksi WebSocket aman (<code>wss://</code>) berjalan, buatlah Nginx Reverse Proxy di host VPS:
                        </p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code># Install Nginx & Certbot SSL
sudo apt install -y nginx certbot python3-certbot-nginx

# Buat konfigurasi server block
sudo nano /etc/nginx/sites-available/dashboard</code></pre>
                        <div class="mt-2 text-xs text-gray-500 font-medium">Isi file konfigurasi Nginx (<code>/etc/nginx/sites-available/dashboard</code>):</div>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code>server {
    listen 80;
    server_name domain-vps-anda.com;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}</code></pre>
                        <p class="text-xs text-gray-600 my-2 leading-relaxed">
                            Aktifkan konfigurasi dan pasang sertifikat SSL Let's Encrypt secara otomatis:
                        </p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code># Aktifkan konfigurasi
sudo ln -s /etc/nginx/sites-available/dashboard /etc/nginx/sites-enabled/
sudo systemctl restart nginx

# Jalankan Certbot SSL
sudo certbot --nginx -d domain-vps-anda.com</code></pre>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Langkah 6: Atur Aturan Firewall Server (UFW)</h4>
                        <p class="text-xs text-gray-600 mb-2 leading-relaxed">
                            Batasi port luar yang terbuka di VPS Anda demi keamanan, hanya buka port-port penting berikut ini:
                        </p>
                        <pre class="bg-gray-800 text-white rounded p-3 text-xs overflow-x-auto font-mono"><code># Tolak semua koneksi masuk secara default
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Izinkan SSH (Akses Server)
sudo ufw allow 22/tcp

# Izinkan port web (HTTP & HTTPS untuk Web & WebSockets)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Izinkan port MQTT broker (untuk pengiriman data dari ESP32)
sudo ufw allow 1883/tcp

# Aktifkan firewall
sudo ufw enable</code></pre>
                        <div class="border-l-4 border-blue-500 bg-blue-50 p-3 rounded-r-xl mt-3 text-xs text-blue-800">
                            <strong>Mengapa port 8085 tidak perlu dibuka?</strong> Karena kita sudah menggunakan Nginx Reverse Proxy di port 443 (HTTPS) yang secara otomatis menyalurkan semua traffic WebSocket ke port internal 8085 di dalam Docker.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 7: TV Kiosk Mode -->
            <section id="tv-kiosk" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">7</span>
                    TV Kiosk Monitoring Mode
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Aplikasi ini dilengkapi dengan mode tampilan monitor Kiosk (TV Mode) di alamat <code>/tv-mode</code> yang dirancang khusus untuk layar TV lobi kantor atau ruang kontrol operasional.
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                    <h4 class="text-sm font-bold text-gray-800 mb-1">Fitur Utama TV Kiosk Mode:</h4>
                    <ul class="list-disc pl-5 space-y-2 text-sm text-gray-600">
                        <li><strong>Tema Terang Berkekuatan Tinggi (High-Contrast Light Theme):</strong> Visibilitas optimal di ruangan kantor yang terang benderang.</li>
                        <li><strong>Jam Digital Terintegrasi:</strong> Menampilkan waktu, hari, dan tanggal real-time di layar.</li>
                        <li><strong>Mode Layar Penuh (Fullscreen):</strong> Tombol pintasan sekali klik untuk beralih ke mode layar penuh tanpa border browser.</li>
                        <li><strong>Peringatan Anomali & Overload:</strong> Kotak alat sensor akan otomatis berkedip merah terang dan memicu alarm visual di layar jika voltase listrik tidak stabil atau terjadi beban berlebih (overload).</li>
                    </ul>
                </div>
            </section>

            <!-- Section 8: 3D Building Floor Map -->
            <section id="floor-map" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">8</span>
                    3D Building Floor Map
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Fitur visualisasi spasial 3D interaktif yang memetakan letak sensor listrik berdasarkan lantai gedung Jamkrida di alamat <code>/building-map</code>.
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-2">Cara Kerja Pengelompokan & Rendering 3D:</h4>
                        <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
                            <li><strong>Konfigurasi Lantai:</strong> Saat membuat grup area operasional, Admin menentukan letak lantai grup tersebut (Lantai 1 s.d. 10).</li>
                            <li><strong>Hologram Lempengan 3D (3D Slabs):</strong> Sistem akan otomatis menggambar tumpukan lantai gedung dalam bentuk lempengan 3D interaktif.</li>
                            <li><strong>Exploded View:</strong> Ketika salah satu lantai di-klik, lantai-lantai lainnya akan bergeser menjauh secara visual (exploded view), dan panel detail sensor (Inspector) di sisi kanan akan terbuka.</li>
                            <li><strong>Integrasi WebSocket:</strong> Setiap ada pembaruan data sensor, warna indikator zona di lempengan 3D dan data angka listrik di panel Inspector akan ter-update secara real-time.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Section 9: Gemini AI Chatbot -->
            <section id="gemini-ai" class="scroll-mt-24">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-sm font-bold">9</span>
                    Gemini AI Chatbot Integration
                </h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Asisten pintar berbasis AI yang tertanam di sudut kanan bawah dashboard untuk membantu menganalisis pola penggunaan daya, memprediksi biaya, dan memberikan rekomendasi penghematan energi.
                </p>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-4">
                    <h4 class="text-sm font-bold text-gray-800 mb-1">Rincian Teknis & Kuota:</h4>
                    <ul class="list-disc pl-5 space-y-2 text-sm text-gray-600">
                        <li><strong>Model:</strong> Menggunakan model stabil <code>gemini-2.5-flash</code> melalui Generative Language API Google.</li>
                        <li><strong>Batas Kuota:</strong> Mendapatkan kuota gratis sebesar **1.500 panggilan per hari** (menggantikan model v1beta pratinjau yang memiliki limit ketat 20 panggilan/hari).</li>
                        <li><strong>Konteks Telemetri:</strong> Setiap kali Anda mengobrol atau mengklik tombol "Analisis", sistem secara otomatis mengirimkan rangkuman data sensor terkini ke asisten Gemini agar analisis yang diberikan akurat sesuai kondisi riil.</li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
