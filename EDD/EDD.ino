#include <Adafruit_MPU6050.h>
#include <Adafruit_Sensor.h>
#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>

#define VIBRATION_PIN 26
#define RELAY_PIN 3

const char* ssid = "bawal kumonek";
const char* password = "cdbanluta24";

const char* serverName = "https://needlessly-accurate-pigeon.ngrok-free.app/Seismo/database.php";

Adafruit_MPU6050 mpu;

int vibrationSensorReading;
int vibrationThreshold = 3000;

unsigned int confidenceCycles = 0;
unsigned long lastDetectionTime = 0;
unsigned long delayPeriod_ms = 5000;
unsigned long lastDataSendTime = 0;
const unsigned long dataSendInterval = 5000; // Send data every 5 seconds

bool isVibrationMoved = false;
bool isMPUMoved = false;
bool relayStatus = false;
bool earthquakeDetected = false;

// Acceleration variables
float accelX, accelY, accelZ;
float baseAccelX = 0, baseAccelY = 0, baseAccelZ = 0;
float accelDiffX, accelDiffY, accelDiffZ;
float accelThreshold = 0.5;

void setup() {
  Serial.begin(115200);
  pinMode(VIBRATION_PIN, INPUT);
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);

  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi");

  if (!mpu.begin()) {
    Serial.println("Failed to find MPU6050 chip");
    while (1) delay(10);
  }

  mpu.setAccelerometerRange(MPU6050_RANGE_2_G);

  // Initialize baseline values
  sensors_event_t event;
  mpu.getAccelerometerSensor()->getEvent(&event);
  baseAccelX = event.acceleration.x;
  baseAccelY = event.acceleration.y;
  baseAccelZ = event.acceleration.z;

  Serial.println("Baseline acceleration values set.");
}

void sendToDatabase() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    
    // Prepare the data string
    String httpRequestData = "vibration_value=" + String(vibrationSensorReading) +
                           "&vibration_threshold=" + String(vibrationThreshold) +
                           "&accel_x=" + String(accelDiffX) +
                           "&accel_y=" + String(accelDiffY) +
                           "&accel_z=" + String(accelDiffZ) +
                           "&accel_threshold=" + String(accelThreshold) +
                           "&relay_status=" + String(relayStatus ? "ON" : "OFF") +
                           "&earthquake_detected=" + String(earthquakeDetected ? "YES" : "NO");

    // Begin HTTP POST request
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    // Send HTTP POST request
    int httpResponseCode = http.POST(httpRequestData);
    
    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
    } else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }
    
    http.end();
  }
}

void loop() {
  vibrationSensorReading = analogRead(VIBRATION_PIN);
  Serial.print("Vibration Sensor Reading: ");
  Serial.println(vibrationSensorReading);

  if (vibrationSensorReading >= vibrationThreshold) {
    isVibrationMoved = true;
    lastDetectionTime = millis();
    Serial.println("Vibration Detected!");
  } else {
    isVibrationMoved = false;
  }

  // Read accelerometer data
  sensors_event_t event;
  mpu.getAccelerometerSensor()->getEvent(&event);
  accelX = event.acceleration.x;
  accelY = event.acceleration.y;
  accelZ = event.acceleration.z;

  // Calculate differences from baseline
  accelDiffX = abs(accelX - baseAccelX) > accelThreshold ? accelX - baseAccelX : 0;
  accelDiffY = abs(accelY - baseAccelY) > accelThreshold ? accelY - baseAccelY : 0;
  accelDiffZ = abs(accelZ - baseAccelZ) > accelThreshold ? accelZ - baseAccelZ : 0;

  Serial.print("Accel Difference X: ");
  Serial.print(accelDiffX);
  Serial.print(" Y: ");
  Serial.print(accelDiffY);
  Serial.print(" Z: ");
  Serial.println(accelDiffZ);

  if (accelDiffX != 0 || accelDiffY != 0 || accelDiffZ != 0) {
    isMPUMoved = true;
    Serial.println("Significant movement detected by MPU6050!");
  } else {
    isMPUMoved = false;
  }

  if (isVibrationMoved && isMPUMoved) {
    confidenceCycles++;
    Serial.print("Confidence Cycles: ");
    Serial.println(confidenceCycles);
  }

  if (confidenceCycles >= 3) {
    Serial.println("Earthquake Detected! Triggering Relay...");
    digitalWrite(RELAY_PIN, HIGH);
    relayStatus = true;
    earthquakeDetected = true;
    delay(2000);
    digitalWrite(RELAY_PIN, LOW);
    relayStatus = false;
    confidenceCycles = 0;
    lastDetectionTime = millis();
  }

  if (millis() - lastDetectionTime > delayPeriod_ms) {
    confidenceCycles = 0;
    earthquakeDetected = false;  // Reset earthquake detection after delay period
  }

  // Send data to database every 5 seconds
  if (millis() - lastDataSendTime >= dataSendInterval) {
    sendToDatabase();
    lastDataSendTime = millis();
  }

  delay(3000);
}
