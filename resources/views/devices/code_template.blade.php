#include <WiFi.h>
#include <PubSubClient.h>
#include <PZEM004Tv30.h>
#include <HTTPClient.h>
#include <HTTPUpdate.h>
#include <ArduinoJson.h>

// --- Config ---
const char* ssid = "{{ $wifi_ssid }}";
const char* password = "{{ $wifi_password }}";

const char* mqtt_server = "{{ $mqtt_host }}";
const int mqtt_port = {{ $mqtt_port }};
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

WiFiClient espClient;
PubSubClient client(espClient);

void setup_wifi() {
  delay(10);
  Serial.println();
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("WiFi connected");
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
        
        WiFiClient clientOTA;
        t_httpUpdate_return ret = httpUpdate.update(clientOTA, url);
        
        switch (ret) {
          case HTTP_UPDATE_FAILED:
            Serial.printf("HTTP_UPDATE_FAILED Error (%d): %s\n", httpUpdate.getLastError(), httpUpdate.getLastErrorString().c_str());
            break;
          case HTTP_UPDATE_NO_UPDATES:
            Serial.println("HTTP_UPDATE_NO_UPDATES");
            break;
          case HTTP_UPDATE_OK:
            Serial.println("HTTP_UPDATE_OK");
            break;
        }
      }
    }
  }
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    if (client.connect(device_id)) {
      Serial.println("connected");
      // Subscribe to command topic
      client.subscribe(mqtt_cmd_topic);
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  setup_wifi();
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(mqttCallback);
  
  // Custom initialization for PZEM if needed
  // Serial2.begin(9600, SERIAL_8N1, PZEM_RX_PIN, PZEM_TX_PIN);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  static unsigned long lastMsg = 0;
  unsigned long now = millis();
  
  // Publish telemetry every 5 seconds
  if (now - lastMsg > 5000) {
    lastMsg = now;
    
    // Read from PZEM-004T
    float voltage = pzem.voltage();
    float current = pzem.current();
    float power = pzem.power();
    float energy = pzem.energy();
    float frequency = pzem.frequency();
    float pf = pzem.pf();
    
    // Check if readings are valid
    if(isnan(voltage)){
        Serial.println("Error reading voltage");
        voltage = 0.0;
    } else if (isnan(current)) {
        Serial.println("Error reading current");
        current = 0.0;
    } else if (isnan(power)) {
        Serial.println("Error reading power");
        power = 0.0;
    } else if (isnan(energy)) {
        Serial.println("Error reading energy");
        energy = 0.0;
    }

    // Build JSON Payload
    String payload = "{";
    payload += "\"voltage\":" + String(voltage, 2) + ",";
    payload += "\"current\":" + String(current, 3) + ",";
    payload += "\"power\":" + String(power, 2) + ",";
    payload += "\"energy\":" + String(energy, 3);
    payload += "}";
    
    client.publish(mqtt_topic, payload.c_str());
    Serial.println("Published: " + payload);
  }
}
