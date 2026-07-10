#include <WiFi.h>
#include <PubSubClient.h>
#include <PZEM004Tv30.h>
#include <HTTPClient.h>
#include <HTTPUpdate.h>
#include <ArduinoJson.h>
#include <LittleFS.h>
#include <time.h>
#if {{ $mqtt_use_tls ?? 0 }}
#include <WiFiClientSecure.h>
#endif

// --- Config ---
const char* ssid = "{{ $wifi_ssid }}";
const char* password = "{{ $wifi_password }}";

const char* mqtt_server = "{{ $mqtt_host }}";
const int mqtt_port = {{ $mqtt_port }};
const char* mqtt_user = "{{ $mqtt_user }}";
const char* mqtt_password = "{{ $mqtt_password }}";
const char* mqtt_topic = "{{ $device->mqtt_topic }}";
const char* mqtt_cmd_topic = "cmd/{{ $device->device_id }}";
const char* device_id = "{{ $device->device_id }}";

// PZEM-004T Pins (Hardware Serial 2 on ESP32 is usually GPIO 16 (RX), 17 (TX))
#define PZEM_RX_PIN 16
#define PZEM_TX_PIN 17

#if !defined(PZEM_RX_PIN) && !defined(PZEM_TX_PIN)
#define PZEM_RX_PIN 16
#define PZEM_TX_PIN 17
#endif

#if !defined(PZEM_SERIAL)
#define PZEM_SERIAL Serial2
#endif

PZEM004Tv30 pzem(PZEM_SERIAL, PZEM_RX_PIN, PZEM_TX_PIN);

#if {{ $mqtt_use_tls ?? 0 }}
WiFiClientSecure espClient;
#else
WiFiClient espClient;
#endif
PubSubClient client(espClient);

// For non-blocking reconnection
unsigned long lastReconnectAttempt = 0;

// OTA Progress Tracking State
int last_progress_publish = -10;

void update_progress(int cur, int total) {
  int pct = (cur * 100) / total;
  if (pct - last_progress_publish >= 10 || pct == 100) {
    last_progress_publish = pct;
    String p = "{\"progress\":" + String(pct) + ",\"status\":\"downloading\"}";
    String ota_topic = "telemetry/ota_status/" + String(device_id);
    client.publish(ota_topic.c_str(), p.c_str());
    Serial.printf("OTA Progress: %d%%\n", pct);
  }
}

// NTP sync
bool syncNTP() {
  Serial.println("Syncing time via NTP...");
  configTime(0, 0, "pool.ntp.org", "time.nist.gov");
  int retry = 0;
  time_t nowTime = 0;
  while (nowTime < 1000000000 && retry < 10) {
    delay(500);
    time(&nowTime);
    retry++;
  }
  if (nowTime >= 1000000000) {
    Serial.printf("Time synced. Current Unix time: %ld\n", (long)nowTime);
    return true;
  }
  Serial.println("NTP Sync Failed");
  return false;
}

// Offline telemetry logging to LittleFS
void log_offline(float v, float a, float w, float kwh) {
  // Check free space to prevent storage exhaustion
  size_t freeSpace = LittleFS.totalBytes() - LittleFS.usedBytes();
  if (freeSpace < 16384) { // less than 16KB free space
    Serial.println("[FS] LittleFS free space is critically low! Removing old logs to free up space...");
    LittleFS.remove("/offline_log.json");
  }

  File file = LittleFS.open("/offline_log.json", "a");
  if (!file) {
    Serial.println("Failed to open offline_log.json for appending");
    return;
  }
  unsigned long uptime = millis() / 1000;
  String line = "{\"v\":" + String(v, 2) + 
                ",\"a\":" + String(a, 3) + 
                ",\"w\":" + String(w, 2) + 
                ",\"kwh\":" + String(kwh, 4) + 
                ",\"uptime\":" + String(uptime) + "}\n";
  file.print(line);
  file.close();
  Serial.println("Saved offline telemetry: " + line);
}

// Upload offline logs on reconnection
void upload_offline_logs() {
  if (!LittleFS.exists("/offline_log.json")) {
    return;
  }
  
  if (!syncNTP()) {
    Serial.println("NTP Sync failed. Will retry historical upload on next connection check.");
    return;
  }
  
  File file = LittleFS.open("/offline_log.json", "r");
  if (!file) {
    Serial.println("Failed to open offline log for reading");
    return;
  }
  
  time_t ntp_now;
  time(&ntp_now);
  unsigned long uptime_now = millis() / 1000;
  
  Serial.println("Uploading historical telemetry logs...");
  
  while (file.available()) {
    String line = file.readStringUntil('\n');
    line.trim();
    if (line.length() == 0) continue;
    
    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, line);
    if (!error) {
      float v = doc["v"];
      float a = doc["a"];
      float w = doc["w"];
      float kwh = doc["kwh"];
      unsigned long entry_uptime = doc["uptime"];
      
      // Calculate historical timestamp
      long entry_timestamp = (long)ntp_now - (long)(uptime_now - entry_uptime);
      
      String histPayload = "{";
      histPayload += "\"voltage\":" + String(v, 2) + ",";
      histPayload += "\"current\":" + String(a, 3) + ",";
      histPayload += "\"power\":" + String(w, 2) + ",";
      histPayload += "\"energy\":" + String(kwh, 4) + ",";
      histPayload += "\"timestamp\":" + String(entry_timestamp);
      histPayload += "}";
      
      String histTopic = "telemetry/historical/" + String(device_id);
      client.publish(histTopic.c_str(), histPayload.c_str());
      Serial.println("Uploaded historical entry: " + histPayload);
      
      delay(50); // slight delay to prevent MQTT broker congestion
    }
  }
  
  file.close();
  LittleFS.remove("/offline_log.json");
  Serial.println("Offline log cleared from LittleFS.");
}

void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);
  
  WiFi.mode(WIFI_STA);
  WiFi.setAutoReconnect(true); // Enable native ESP32 auto-reconnect
  WiFi.begin(ssid, password);
  
  // Non-blocking connect with timeout on boot
  int retries = 0;
  while (WiFi.status() != WL_CONNECTED && retries < 20) {
    delay(500);
    Serial.print(".");
    retries++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nWiFi connection timed out. Starting in offline buffer mode.");
  }
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Message arrived [");
  Serial.print(topic);
  Serial.print("] ");
  
  String msg = "";
  for (unsigned int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }
  Serial.println(msg);

  // Check if it's our command topic
  if (String(topic) == String(mqtt_cmd_topic)) {
    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, msg);
    if (!error) {
      if (doc["cmd"] == "update_firmware") {
        String url = doc["url"].as<String>();
        Serial.println("OTA Update Triggered! URL: " + url);
        
        // Notify dashboard OTA has started
        last_progress_publish = 0;
        String startPayload = "{\"progress\":0,\"status\":\"started\"}";
        client.publish(("telemetry/ota_status/" + String(device_id)).c_str(), startPayload.c_str());
        
        WiFiClient clientOTA;
        t_httpUpdate_return ret = httpUpdate.update(clientOTA, url);
        
        switch (ret) {
          case HTTP_UPDATE_FAILED:
            Serial.printf("HTTP_UPDATE_FAILED Error (%d): %s\n", httpUpdate.getLastError(), httpUpdate.getLastErrorString().c_str());
            {
              String failPayload = "{\"progress\":" + String(last_progress_publish >= 0 ? last_progress_publish : 0) + ",\"status\":\"failed\",\"message\":\"" + String(httpUpdate.getLastErrorString().c_str()) + "\"}";
              client.publish(("telemetry/ota_status/" + String(device_id)).c_str(), failPayload.c_str());
            }
            break;
          case HTTP_UPDATE_NO_UPDATES:
            Serial.println("HTTP_UPDATE_NO_UPDATES");
            {
              String noPayload = "{\"progress\":0,\"status\":\"failed\",\"message\":\"No updates available.\"}";
              client.publish(("telemetry/ota_status/" + String(device_id)).c_str(), noPayload.c_str());
            }
            break;
          case HTTP_UPDATE_OK:
            Serial.println("HTTP_UPDATE_OK");
            {
              String okPayload = "{\"progress\":100,\"status\":\"completed\"}";
              client.publish(("telemetry/ota_status/" + String(device_id)).c_str(), okPayload.c_str());
            }
            break;
        }
      } else if (doc["cmd"] == "reset_energy") {
        Serial.println("Resetting energy count...");
        pzem.resetEnergy();
      } else if (doc["cmd"] == "restart") {
        Serial.println("Restart command received! Rebooting ESP32...");
        delay(500);
        ESP.restart();
      }
    }
  }
}

bool reconnect() {
  if (WiFi.status() != WL_CONNECTED) {
    static unsigned long lastWiFiAttempt = 0;
    static int wifiRetryCount = 0;
    if (millis() - lastWiFiAttempt > 15000) {
      lastWiFiAttempt = millis();
      wifiRetryCount++;
      Serial.printf("WiFi disconnected (Attempt %d). Reconnecting...\n", wifiRetryCount);
      
      // Reset WiFi driver or reboot if failed consistently for ~5 minutes (20 attempts)
      if (wifiRetryCount >= 20) {
        Serial.println("WiFi connection failed persistently. Restarting ESP32 as ultimate fail-safe...");
        delay(1000);
        ESP.restart();
      }
      
      WiFi.disconnect();
      delay(100);
      WiFi.begin(ssid, password);
    }
    return false;
  }

  if (millis() - lastReconnectAttempt > 5000) {
    lastReconnectAttempt = millis();
    Serial.print("Attempting MQTT connection...");
    bool connected = false;
    if (strlen(mqtt_user) > 0) {
      connected = client.connect(device_id, mqtt_user, mqtt_password);
    } else {
      connected = client.connect(device_id);
    }

    if (connected) {
      Serial.println("connected");
      client.subscribe(mqtt_cmd_topic);
      // Upload any offline logs saved on LittleFS
      upload_offline_logs();
      return true;
    } else {
      Serial.print("failed, rc=");
      Serial.println(client.state());
    }
  }
  return false;
}

void setup() {
  Serial.begin(115200);
  
  // Initialize LittleFS
  if (!LittleFS.begin(true)) {
    Serial.println("LittleFS Mount Failed");
  } else {
    size_t freeSpace = LittleFS.totalBytes() - LittleFS.usedBytes();
    Serial.printf("[FS] LittleFS free space at boot: %u bytes\n", freeSpace);
    if (freeSpace < 16384) {
      Serial.println("[FS] Storage space is full or low at boot! Performing auto-cleanup...");
      LittleFS.remove("/offline_log.json");
      size_t freeSpaceAfter = LittleFS.totalBytes() - LittleFS.usedBytes();
      if (freeSpaceAfter < 16384) {
        Serial.println("[FS] Storage still critical. Formatting LittleFS...");
        LittleFS.format();
        Serial.println("[FS] LittleFS Formatted successfully.");
      } else {
        Serial.println("[FS] Storage cleared successfully by removing log file.");
      }
    }
  }
  
  #if {{ $mqtt_use_tls ?? 0 }}
  espClient.setInsecure(); // Skip SSL certificate verification for ease of use
  #endif

  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(mqttCallback);
  
  // Register OTA update progress callback
  httpUpdate.onProgress(update_progress);
}

void loop() {
  bool isConnected = client.connected();
  if (!isConnected) {
    isConnected = reconnect();
  }
  
  if (isConnected) {
    client.loop();
  }

  static unsigned long lastMsg = 0;
  unsigned long now = millis();
  
  // Telemetry publish interval (2000ms if connected, 10000ms if disconnected to conserve space)
  unsigned long publishInterval = isConnected ? 2000 : 10000;
  
  if (now - lastMsg > publishInterval) {
    lastMsg = now;
    
    // Read from PZEM-004T
    float voltage = pzem.voltage();
    float current = pzem.current();
    float power = pzem.power();
    float energy = pzem.energy();
    
    // Check if readings are valid
    if (isnan(voltage)) voltage = 0.0;
    if (isnan(current)) current = 0.0;
    if (isnan(power))   power = 0.0;
    if (isnan(energy))  energy = 0.0;

    // Build JSON Payload
    String payload = "{";
    payload += "\"voltage\":" + String(voltage, 2) + ",";
    payload += "\"current\":" + String(current, 3) + ",";
    payload += "\"power\":" + String(power, 2) + ",";
    payload += "\"energy\":" + String(energy, 3) + ",";
    payload += "\"ip\":\"" + WiFi.localIP().toString() + "\",";
    payload += "\"rssi\":" + String(WiFi.RSSI()) + ",";
    payload += "\"heap\":" + String(ESP.getFreeHeap());
    payload += "}";
    
    if (isConnected) {
      if (client.publish(mqtt_topic, payload.c_str())) {
        Serial.println("Published: " + payload);
      } else {
        Serial.println("Publish failed. Saving to LittleFS offline buffer...");
        log_offline(voltage, current, power, energy);
      }
    } else {
      Serial.println("MQTT Offline. Saving to LittleFS offline buffer...");
      log_offline(voltage, current, power, energy);
    }
  }
}
