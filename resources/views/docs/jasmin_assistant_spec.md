# 🎙️ Spesifikasi Teknis: Jamkrida Assistant Smart Monitoring IoT Nodes (JASMIN)

Dokumen ini menjelaskan rancangan spesifikasi teknis dan blueprint implementasi untuk asisten kontrol suara privat **JASMIN** pada ekosistem Jamkrida IoT.

---

## 🏢 1. Arsitektur Hardware (Listen & Response Node)

Modul diletakkan pada masing-masing ruangan utama (misal: Lobby, Server Room, Workspace) dengan komponen IoT terdistribusi:

```
[Suara Pengguna]
       │
       ▼
┌──────────────┐      I2S Digital Audio      ┌──────────────┐
│  INMP441 Mic │ ──────────────────────────> │   ESP32-S3   │ ─── (Deteksi "Halo Jasmin")
└──────────────┘                             └──────────────┘
                                                     │
                                                     │ Wi-Fi (MQTT Broker)
                                                     ▼
                                             ┌──────────────┐
                                             │ Laravel App  │ ─── (Cocokkan Perintah / Intent)
                                             └──────────────┘
                                                     │
                                                     │ Respon Suara via MQTT
                                                     ▼
┌──────────────┐      I2S Audio Output       ┌──────────────┐
│ 8Ω/3W Speaker│ <────────────────────────── │  MAX98357A   │
└──────────────┘                             └──────────────┘
```

### Komponen Perangkat Keras:
1. **Microcontroller**: ESP32-S3 DevKit (Dual-core LX7 DSP, PSRAM 8MB).
2. **Audio Input**: Mikrofon Digital I2S INMP441 (Omnidirectional, kepekaan tinggi hingga 5 meter).
3. **Audio Output**: DAC & Amplifier I2S MAX98357A terintegrasi untuk mendrive speaker dinamis 8 Ohm 3 Watt secara langsung tanpa amplifier eksternal.

---

## 🗣️ 2. Alur Deteksi Kata Pemicu (Wake Word)

1. **Local Wake-word Engine (Offline)**: 
   ESP32-S3 diprogram menggunakan SDK **ESP-Skainet (WakeNet)** untuk terus-menerus memindai audio input secara lokal terhadap kata pemicu: **"Halo Jasmin"** atau **"Jasmin"**.
2. **Aktivasi Streaming**: 
   Setelah kata *"Jasmin"* terdeteksi:
   * Lampu LED indikator pada modul fisik akan menyala hijau.
   * Modul ESP32-S3 mulai merekam suara perintah lanjutan (maksimal 5 detik) dan mengirimkan data audio biner tersebut ke server internal via WebSockets atau MQTT.

---

## 🧠 3. Logika Pemrosesan Perintah (Intent Matcher)

Server internal memproses teks transkripsi Bahasa Indonesia menggunakan ekspresi reguler (Regex) untuk mengekstrak **Tindakan (Action)**, **Target (Device)**, dan **Lokasi (Room)**:

```javascript
// Contoh implementasi parser perintah suara di sisi client
function parseVoiceCommand(text, currentRoom = 'lobby') {
    const commandText = text.toLowerCase().trim();
    
    // Deteksi Tindakan (State: 1 = ON, 0 = OFF)
    let state = null;
    if (/(nyalakan|hidupkan|aktifkan|buka|on)/.test(commandText)) {
        state = 1;
    } else if (/(matikan|padamkan|nonaktifkan|tutup|off)/.test(commandText)) {
        state = 0;
    }

    if (state === null) return null; // Aksi tidak valid

    // Pemetaan kata kunci ke ID perangkat di database
    const deviceKeywords = {
        'ac server': 'ac_server_1',
        'ac': 'ac_default',
        'lampu lobby': 'lampu_lobby_1',
        'lampu': 'lampu_default'
    };

    let targetDevice = null;
    for (const [key, id] of Object.entries(deviceKeywords)) {
        if (commandText.includes(key)) {
            targetDevice = id;
            break;
        }
    }

    // Context-Aware Room Fallback: jika pengguna hanya berkata "nyalakan lampu" 
    // tanpa menyebutkan nama ruangan, asisten mengarahkan ke ruangan modul mic berada.
    if (targetDevice === 'lampu_default') {
        targetDevice = `lampu_${currentRoom}_1`;
    } else if (targetDevice === 'ac_default') {
        targetDevice = `ac_${currentRoom}_1`;
    }

    return targetDevice ? { deviceId: targetDevice, state } : null;
}
```

---

## 📣 4. Sintesis Suara Balasan (Text-to-Speech)

Konfirmasi respon balik dari asisten suara **JASMIN** disintesis menggunakan protokol audio yang ditarik secara dinamis dari API text-to-speech internal atau Web Speech Synthesis di browser dengan aksen wanita Bahasa Indonesia (`id-ID`).

### Contoh Skenario Respons:
* **Perintah Diterima**: *"Baik, Lampu Workspace sudah dinyalakan."*
* **Kesalahan Nama Alat**: *"Maaf, peralatan tersebut tidak terdaftar di sistem Jamkrida."*
* **Perintah Tidak Jelas**: *"Maaf, bisa tolong ulangi perintah Anda?"*

---

## 🔒 5. Keamanan & Privasi Lokal
Sistem asisten suara JASMIN berjalan seutuhnya di dalam jaringan LAN internal PT Jamkrida Jateng (`192.168.254.254`). Data rekaman suara **tidak dikirimkan** ke cloud publik di luar jaringan perusahaan, menjadikannya sangat aman dari penyadapan data operasional kantor.
