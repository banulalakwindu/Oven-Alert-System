#include <WiFi.h>
#include <WebServer.h>
#include <Wire.h>
#include "RTClib.h"
#include "DHT.h"
#include <HardwareSerial.h>
#include <freertos/FreeRTOS.h>
#include <freertos/task.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

WebServer server(80);

RTC_DS3231 rtc;

const int monitoredPin = 4;
const int ledPin = 2;

String timestamps[100];
int timestampIndex = 0;

int previousState = LOW;

#define DHTPIN 5
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);

String phoneNumber = "YOUR_PHONE_NUMBER";
String message = "Temperature Alert!";
HardwareSerial gsmSerial(2);

String getCurrentTimestamp() {
  DateTime now = rtc.now();
  char timeStr[25];
  snprintf(timeStr, sizeof(timeStr), "%02d/%02d/%04d %02d:%02d:%02d",
           now.day(), now.month(), now.year(),
           now.hour(), now.minute(), now.second());
  return String(timeStr);
}

void handleLogs() {
  String response = "[";
  for (int i = 0; i < timestampIndex; i++) {
    response += "\"" + timestamps[i] + "\"";
    if (i < timestampIndex - 1) {
      response += ", ";
    }
  }
  response += "]";

  timestampIndex = 0;

  server.send(200, "application/json", response);
}

void handleTemperature() {
  float temp = dht.readTemperature();
  String timestamp = getCurrentTimestamp();

  if (isnan(temp)) {
    server.send(500, "application/json", "{\"error\":\"Failed to read from DHT sensor\"}");
  } else {
    String response = "{\"temperature\":" + String(temp) + ", \"time\":\"" + timestamp + "\"}";
    server.send(200, "application/json", response);
  }
}

void handleCutoff() {
  digitalWrite(ledPin, LOW);
  server.send(200, "application/json", "{\"status\":\"LED turned off\"}");
}

void handlePowerOn() {
  digitalWrite(ledPin, HIGH);
  server.send(200, "application/json", "{\"status\":\"LED turned on\"}");
}

void monitorTemperatureTask(void *pvParameters) {
  const float tempThreshold = 40.0;
  const int checkInterval = 10000;

  while (true) {
    float temp = dht.readTemperature();

    if (!isnan(temp) && temp > tempThreshold) {
      gsmSerial.begin(9600, SERIAL_8N1, 16, 17);
      delay(3000);

      Serial.println("Initializing GSM module...");
      sendATCommand("AT");

      Serial.println("Checking network registration...");
      if (sendATCommand("AT+CREG?").indexOf("0,1") == -1) {
        Serial.println("Error: Not registered on network.");
      } else {
        Serial.println("Sending SMS...");
        sendATCommand("AT+CMGF=1");
        sendSMS(phoneNumber, message);

        Serial.println("Waiting for SMS to be sent...");
        delay(3000);

        Serial.println("Making a call...");
        makeCall(phoneNumber);
      }

      gsmSerial.end();
    }

    vTaskDelay(checkInterval / portTICK_PERIOD_MS);
  }
}

// Task to send SMS
void sendSMSTask(void *pvParameters) {
  gsmSerial.begin(9600, SERIAL_8N1, 16, 17);
  delay(3000);

  Serial.println("Initializing GSM module...");
  sendATCommand("AT");

  Serial.println("Checking network registration...");
  if (sendATCommand("AT+CREG?").indexOf("0,1") == -1) {
    Serial.println("Error: Not registered on network.");
    vTaskDelete(NULL);
  }

  Serial.println("Sending SMS...");
  sendATCommand("AT+CMGF=1");
  sendSMS(phoneNumber, message);

  Serial.println("Waiting for SMS to be sent...");
  delay(3000);

  Serial.println("Making a call...");
  makeCall(phoneNumber);

  vTaskDelete(NULL);
}

String sendATCommand(String command) {
  gsmSerial.println(command);
  delay(1000);
  String response = "";
  while (gsmSerial.available()) {
    char c = gsmSerial.read();
    response += c;
    Serial.print(c);
  }
  return response;
}

void sendSMS(String number, String message) {
  sendATCommand("AT+CMGS=\"" + number + "\"");
  delay(1000);
  gsmSerial.print(message);
  delay(1000);
  gsmSerial.write(26);
  delay(5000);
  while (gsmSerial.available()) {
    Serial.write(gsmSerial.read());
  }
}

void makeCall(String number) {
  String response = sendATCommand("ATD" + number + ";");
  Serial.println("Call started...");
  delay(40000);
  sendATCommand("ATH");
  Serial.println("Call ended.");
}

void setup() {
  Serial.begin(115200);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  pinMode(monitoredPin, INPUT);

  pinMode(ledPin, OUTPUT);
  digitalWrite(ledPin, HIGH);

  if (!rtc.begin()) {
    Serial.println("Couldn't find RTC");
    while (1);
  }

  if (rtc.lostPower()) {
    Serial.println("RTC lost power, let's set the time!");
    rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
  }

  dht.begin();

  server.on("/logs", handleLogs);
  server.on("/temperature", handleTemperature);
  server.on("/cutoff", handleCutoff);
  server.on("/poweron", handlePowerOn);
  server.begin();

  xTaskCreatePinnedToCore(monitorTemperatureTask, "MonitorTemperatureTask", 4096, NULL, 1, NULL, 1);
}

void loop() {
  server.handleClient();

  int currentState = digitalRead(monitoredPin);


  if (currentState == HIGH && previousState == LOW) {
    if (timestampIndex < 100) {
      timestamps[timestampIndex] = getCurrentTimestamp();
      timestampIndex++;
    }
  }
  previousState = currentState;
}
