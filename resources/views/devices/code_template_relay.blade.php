// =============================================================
// IoT Dashboard — 4-Channel Relay Controller Provisioning Code
// Device ID: {{ $device->device_id }}
// Generated: {{ now()->format('Y-m-d H:i:s') }}
// =============================================================

#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
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
const char* mqtt_cmd_topic = "cmd/office-control";
const char* mqtt_status_topic = "telemetry/relay-status/{{ $device->device_id }}";
const char* mqtt_device_cmd = "cmd/{{ $device->device_id }}";
const char* device_id = "{{ $device->device_id }}";

// --- Relay GPIO Pins (active LOW) ---
// Change these to match your wiring
#define RELAY_CH1  25
#define RELAY_CH2  26
#define RELAY_CH3  27
#define RELAY_CH4  14

const int relayPins[] = { RELAY_CH1, RELAY_CH2, RELAY_CH3, RELAY_CH4 };
const int NUM_RELAYS = 4;

// Map appliance IDs to relay channels
// Update these strings to match the appliance_id values configured in your dashboard
String applianceMap[4] = {
  "{{ $device->device_id }}_ch1",  // Channel 1
  "{{ $device->device_id }}_ch2",  // Channel 2
  "{{ $device->device_id }}_ch3",  // Channel 3
  "{{ $device->device_id }}_ch4"   // Channel 4
};

int relayStates[4] = {0, 0, 0, 0};

#if {{ $mqtt_use_tls ?? 0 }}
WiFiClientSecure espClient;
#else
WiFiClient espClient;
#endif
PubSubClient client(espClient);

unsigned long lastReconnectAttempt = 0;
unsigned long lastStatusReport = 0;
const unsigned long STATUS_INTERVAL = 30000; // Report status every 30s
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

// ---- Set Relay State ----
void setRelay(int channel, int state) {
  if (channel < 0 || channel >= NUM_RELAYS) return;
  relayStates[channel] = state;
  // Active LOW relay: LOW = ON, HIGH = OFF
  digitalWrite(relayPins[channel], state ? LOW : HIGH);
  Serial.printf("[RELAY] Channel %d (%s) → %s\n",
                channel, applianceMap[channel].c_str(),
                state ? "ON" : "OFF");
}

// ---- Publish Current Relay Status ----
void publishStatus() {
  JsonDocument doc;
  doc["device_id"] = device_id;
  doc["rssi"] = WiFi.RSSI();
  doc["uptime"] = millis() / 1000;

  JsonArray channels = doc["channels"].to<JsonArray>();
  for (int i = 0; i < NUM_RELAYS; i++) {
    JsonObject ch = channels.add<JsonObject>();
    ch["id"] = applianceMap[i];
    ch["channel"] = i;
    ch["state"] = relayStates[i];
  }

  char buffer[512];
  serializeJson(doc, buffer);

  if (client.connected()) {
    client.publish(mqtt_status_topic, buffer);
    Serial.printf("[MQTT] Status published to %s\n", mqtt_status_topic);
  }
}

// ---- MQTT Callback ----
void mqttCallback(char* topic, byte* payload, unsigned int length) {
  String msg;
  for (unsigned int i = 0; i < length; i++) msg += (char)payload[i];
  Serial.printf("[MQTT] Received on %s: %s\n", topic, msg.c_str());

  JsonDocument doc;
  if (deserializeJson(doc, msg)) return;

  // Handle office-control relay commands
  if (String(topic) == String(mqtt_cmd_topic)) {
    String applianceId = doc["appliance"] | "";
    int state = doc["state"] | -1;
    if (state < 0) return;

    // Find matching channel
    for (int i = 0; i < NUM_RELAYS; i++) {
      if (applianceMap[i] == applianceId) {
        setRelay(i, state);
        publishStatus();
        return;
      }
    }
    Serial.printf("[RELAY] Unknown appliance: %s\n", applianceId.c_str());
  }

  // Handle device-specific commands (restart, OTA, etc.)
  if (String(topic) == String(mqtt_device_cmd)) {
    String action = doc["action"] | "";
    if (action == "restart") {
      Serial.println("[CMD] Restarting device...");
      ESP.restart();
    }
    if (action == "ota_update") {
      String url = doc["url"] | "";
      if (url.length() > 0) performOTA(url);
    }
    // Set all relays at once: {"action":"set_all","states":[1,0,1,0]}
    if (action == "set_all") {
      JsonArray states = doc["states"];
      for (int i = 0; i < min((int)states.size(), NUM_RELAYS); i++) {
        setRelay(i, states[i].as<int>());
      }
      publishStatus();
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
  String clientId = "relay_" + String(device_id);
  bool connected;
  if (strlen(mqtt_user) > 0) {
    connected = client.connect(clientId.c_str(), mqtt_user, mqtt_password);
  } else {
    connected = client.connect(clientId.c_str());
  }
  if (connected) {
    Serial.println("[MQTT] Connected!");
    client.subscribe(mqtt_cmd_topic);
    client.subscribe(mqtt_device_cmd);
    publishStatus(); // Report initial state on connect
  }
  return connected;
}

// ---- Setup ----
void setup() {
  Serial.begin(115200);
  Serial.println("\n========================================");
  Serial.println(" IoT Dashboard — 4CH Relay Controller");
  Serial.printf(" Device: %s\n", device_id);
  Serial.println("========================================");

  // Initialize relay GPIOs
  for (int i = 0; i < NUM_RELAYS; i++) {
    pinMode(relayPins[i], OUTPUT);
    digitalWrite(relayPins[i], HIGH); // Start with all relays OFF (active LOW)
  }

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

  // Periodic status report
  unsigned long now = millis();
  if (now - lastStatusReport >= STATUS_INTERVAL) {
    lastStatusReport = now;
    publishStatus();
  }
}
