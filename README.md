# ⚡ Jamkrida Energy IoT Dashboard v2.1

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Docker](https://img.shields.io/badge/Docker-Production--Ready-blue.svg)](https://www.docker.com)
[![MQTT](https://img.shields.io/badge/MQTT-EMQX%20%7C%20Mosquitto%20%7C%20HiveMQ-orange.svg)](https://mqtt.org)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Platform pemantauan energi listrik real-time berbasis IoT yang dirancang khusus untuk memonitor parameter kelistrikan dari sensor PZEM-004T / ESP32. Dilengkapi dengan sistem alert anomali, monitoring budget bulanan, manajemen Over-the-Air (OTA) firmware, dan arsitektur backend yang kokoh berbasis Laravel 12 dan Docker.

---

## 🚀 Fitur Utama

- **Pemantauan Energi Real-Time**: Visualisasi data voltase (V), arus (A), daya aktif (W), faktor daya (PF), frekuensi (Hz), akumulasi energi (kWh), serta estimasi biaya listrik dalam Rupiah (IDR).
- **Notifikasi Alert & Deteksi Anomali**: Sistem peringatan instan (glassmorphism banner) pada dashboard jika terjadi kegagalan heartbeat (>5 menit), voltase tidak stabil, beban listrik puncak terlampaui, atau budget bulanan terlampaui.
- **Generator Kode ESP32 Dinamis (Provisioning)**: Menghasilkan template script C++ Arduino IDE secara otomatis berdasarkan kredensial WiFi dan broker MQTT (mendukung SSL/TLS `setInsecure()` untuk HiveMQ Cloud).
- **Update Firmware Over-The-Air (OTA)**: Mengunggah file biner firmware (.bin) dari dashboard dan mengirimkan perintah update ke mikrokontroler target via MQTT dengan pelacakan progress real-time.
- **Sistem Kalibrasi & Anggaran**: Pengaturan faktor kalibrasi tegangan, batas maksimal beban, target harian, dan budget bulanan per perangkat langsung dari antarmuka Web.
- **Dokumentasi API Interaktif**: Dokumentasi API modern berbasis OpenAPI menggunakan tema premium **Stoplight Elements** yang dapat digenerate secara lokal.
- **Multi-Server & Broker Autocrossover**: Mendukung MQTT Lokal (Mosquitto), Cloud Broker (HiveMQ Cloud), hingga Enterprise Broker (EMQX).
- **Keamanan Berbasis Peran (RBAC)**: Pembatasan ketat fungsionalitas sistem berdasarkan peran pengguna (**Admin** dan **User Biasa**).

---

## 🛠️ Stack Teknologi

- **Backend**: Laravel 12 (PHP 8.2+), Laravel Reverb (WebSockets), php-mqtt
- **Frontend**: Blade Templating, Vanilla CSS (Glassmorphism UI), Chart.js (Grafik Interaktif), AlpineJS, TailwindCSS, Laravel Echo
- **Database & Queue**: MySQL 8.0, Database Queue Driver
- **Infrastruktur & Broker**: Docker Compose, Eclipse Mosquitto, Nginx Reverse Proxy
- **Perangkat IoT (Hardware)**: ESP32 DevKit V1, PZEM-004T (V3.0) AC Energy Meter

---

## 🖥️ Panduan Jalankan di Lokal (Development)

Untuk pengembangan lokal tanpa Docker, pastikan Anda telah menginstal PHP 8.2+, Composer, Node.js (NPM), dan MySQL lokal terlebih dahulu.

### 1. Kloning & Persiapan Awal
```bash
git clone https://github.com/suppfaiz/iotdashboard-jjt.git
cd iotdashboard-jjt
composer install
npm install
```

### 2. Konfigurasi Environment
Salin file `.env` dan generates kunci aplikasi:
```bash
cp .env.example .env
php artisan key:generate
```
*Sesuaikan konfigurasi database (`DB_*`), Reverb (`REVERB_*`), dan broker MQTT (`MQTT_*`) di dalam file `.env` Anda.*

### 3. Migrasi & Seed Database
```bash
php artisan migrate --seed
```

### 4. Jalankan Layanan di Terminal Terpisah
Jalankan perintah-perintah berikut di terminal yang berbeda dari direktori root:

- **Laravel App Server**:
  ```bash
  php artisan serve
  ```
- **Vite Asset Compiler**:
  ```bash
  npm run dev
  ```
- **WebSocket Server (Reverb)**:
  ```bash
  php artisan reverb:start
  ```
- **MQTT Listener Daemon**:
  ```bash
  php artisan mqtt:listen
  ```

---

## 🐳 Panduan Deployment VPS (Docker - Production)

Deployment produksi sangat disederhanakan menggunakan installer script otomatis yang membundel Laravel, MySQL, phpMyAdmin, dan Eclipse Mosquitto MQTT Broker.

### 1. Jalankan Script Installer
```bash
chmod +x install.sh
./install.sh
```
*Script ini akan otomatis membuat jaringan docker, men-generate kredensial database & MQTT unik di `.env`, serta menyusun kontainer.*

### 2. Jalankan Kontainer di Latar Belakang
```bash
docker compose up -d --build
```

### 3. Generate Dokumentasi API (Opsional)
Untuk men-generate file dokumentasi API interaktif Stoplight Elements di dalam kontainer:
```bash
docker compose exec app php artisan scribe:generate
```

---

## 📜 Lisensi
Proyek ini dilisensikan di bawah Lisensi MIT. Hak Cipta © 2025 Jamkrida Energy.

