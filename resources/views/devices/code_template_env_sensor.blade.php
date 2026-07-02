// =============================================================
// IoT Dashboard — Environment Sensor (DHT22) Provisioning Code
// Device ID: {{ $device->device_id }}
// Generated: {{ now()->format('Y-m-d H:i:s') }}
// =============================================================

#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <HTTPClient.h>
#include <HTTPUpdate.h>
#include <LittleFS.h>
#include <time.h>
#if {{ $mqtt_use_tls ?? 0 }}
#include <WiFiClientSecure.h>
#endif

// --- WiFi Config ---
const char* ssid = "{{ $wifi_ssid }}";
const char* password = "{{ $wifi_password }}";

// --- MQTT Config ---
const char* mqtt_server = "{{ $mqtt_host }}";
const int mqtt_port = {{ $mqtt_port }};
const char* mqtt_user = "{{ $mqtt_user }}";
const char* mqtt_password = "{{ $mqtt_password }}";
const char* mqtt_topic = "{{ $device->mqtt_topic }}";
const char* mqtt_cmd_topic = "cmd/{{ $device->device_id }}";
const char* device_id = "{{ $device->device_id }}";

// --- DHT22 Sensor Config ---
#define DHT_PIN 4          // GPIO4 — change if wired differently
#define DHT_TYPE DHT22
DHT dht(DHT_PIN, DHT_TYPE);

#if {{ $mqtt_use_tls ?? 0 }}
WiFiClientSecure espClient;
#else
WiFiClient espClient;
#endif
PubSubClient client(espClient);

// Non-blocking timers
unsigned long lastReconnectAttempt = 0;
unsigned long lastReadTime = 0;
const unsigned long READ_INTERVAL = 5000; // 5 seconds

// OTA state
bool otaInProgress = false;

// ---- WiFi Setup ----
void setup_wifi() {
  delay(10);
  Serial.printf("\n[WiFi] Connecting to %s", ssid);
  WiFi.begin(ssid, password);
  int tries = 0;
  while (WiFi.status() != WL_CONNECTED && tries < 40) {
    delay(500);
    Serial.print(".");
    tries++;
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\n[WiFi] Connected — IP: %s  RSSI: %d dBm\n",
                  WiFi.localIP().toString().c_str(), WiFi.RSSI());
  } else {
    Serial.println("\n[WiFi] Connection FAILED. Will retry in loop.");
  }
}

// ---- MQTT Callback ----
void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String msg;
  for (unsigned int i = 0; i < length; i++) msg += (char)payload[i];
  Serial.printf("[MQTT] Received on %s: %s\n", topic, msg.c_str());

  JsonDocument doc;
  if (deserializeJson(doc, msg)) return;

  // Handle remote commands (restart, OTA, etc.)
  if (String(topic) == String(mqtt_cmd_topic)) {
    String action = doc["action"] | "";
    if (action == "restart") {
      Serial.println("[CMD] Restarting device...");
      ESP.restart();
    }
    if (action == "ota_update") {
      String url = doc["url"] | "";
      if (url.length() > 0) performOTA(url);
    }
  }
}

// ---- OTA Update ----
void performOTA(String url) {
  otaInProgress = true;
  Serial.printf("[OTA] Updating from: %s\n", url.c_str());

  WiFiClient otaClient;
  httpUpdate.onProgress([](int cur, int total) {
    int pct = (cur * 100) / total;
    Serial.printf("[OTA] Progress: %d%%\n", pct);
  });
  t_httpUpdate_return ret = httpUpdate.update(otaClient, url);
  switch (ret) {
    case HTTP_UPDATE_FAILED:
      Serial.printf("[OTA] FAILED (%d): %s\n",
                    httpUpdate.getLastError(),
                    httpUpdate.getLastErrorString().c_str());
      break;
    case HTTP_UPDATE_NO_UPDATES:
      Serial.println("[OTA] No updates available.");
      break;
    case HTTP_UPDATE_OK:
      Serial.println("[OTA] Success! Rebooting...");
      break;
  }
  otaInProgress = false;
}

// ---- MQTT Reconnect (non-blocking) ----
bool mqttReconnect() {
  String clientId = "env_" + String(device_id);
  bool connected;
  if (strlen(mqtt_user) > 0) {
    connected = client.connect(clientId.c_str(), mqtt_user, mqtt_password);
  } else {
    connected = client.connect(clientId.c_str());
  }
  if (connected) {
    Serial.println("[MQTT] Connected!");
    client.subscribe(mqtt_cmd_topic);
  }
  return connected;
}

// ---- Read & Publish Sensor Data ----
void readAndPublish() {
  float temp = dht.readTemperature();
  float humi = dht.readHumidity();

  if (isnan(temp) || isnan(humi)) {
    Serial.println("[DHT] Read failed — sensor not responding.");
    return;
  }

  Serial.printf("[DHT] Temp: %.1f°C  Humidity: %.1f%%\n", temp, humi);

  JsonDocument doc;
  doc["temperature"] = round(temp * 10.0) / 10.0;
  doc["humidity"]    = round(humi * 10.0) / 10.0;
  doc["rssi"]        = WiFi.RSSI();
  doc["uptime"]      = millis() / 1000;

  char buffer[256];
  serializeJson(doc, buffer);

  if (client.connected()) {
    client.publish(mqtt_topic, buffer);
    Serial.printf("[MQTT] Published to %s\n", mqtt_topic);
  }
}

// ---- Setup ----
void setup() {
  Serial.begin(115200);
  Serial.println("\n========================================");
  Serial.println(" IoT Dashboard — Environment Sensor");
  Serial.printf(" Device: %s\n", device_id);
  Serial.println("========================================");

  dht.begin();
  setup_wifi();

  // NTP time sync
  configTime(25200, 0, "pool.ntp.org", "time.nist.gov");

  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(mqttCallback);
  client.setBufferSize(1024);

  if (!LittleFS.begin(true)) {
    Serial.println("[FS] LittleFS mount failed.");
  }
}

// ---- Loop ----
void loop() {
  if (otaInProgress) return;

  // WiFi reconnect
  if (WiFi.status() != WL_CONNECTED) {
    setup_wifi();
  }

  // MQTT reconnect (non-blocking)
  if (!client.connected()) {
    unsigned long now = millis();
    if (now - lastReconnectAttempt > 5000) {
      lastReconnectAttempt = now;
      if (mqttReconnect()) {
        lastReconnectAttempt = 0;
      }
    }
  } else {
    client.loop();
  }

  // Read sensor at interval
  unsigned long now = millis();
  if (now - lastReadTime >= READ_INTERVAL) {
    lastReadTime = now;
    readAndPublish();
  }
}
