<?php
// Database configuration for localhost
$servername = "localhost";
$username = "root";  // default username for localhost
$password = "";      // empty password
$dbname = "seismodb";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from POST request
$vibration_value = $_POST['vibration_value'];
$vibration_threshold = $_POST['vibration_threshold'];
$accel_x = $_POST['accel_x'];
$accel_y = $_POST['accel_y'];
$accel_z = $_POST['accel_z'];
$accel_threshold = $_POST['accel_threshold'];
$relay_status = $_POST['relay_status'];
$earthquake_detected = $_POST['earthquake_detected'];

// Prepare SQL statement
$sql = "INSERT INTO sensor_datas (
            vibration_value,
            vibration_threshold,
            accel_x,
            accel_y,
            accel_z,
            accel_threshold,
            relay_status,
            earthquake_detected,
            timestamp
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ddddddss",
    $vibration_value,
    $vibration_threshold,
    $accel_x,
    $accel_y,
    $accel_z,
    $accel_threshold,
    $relay_status,
    $earthquake_detected
);

if ($stmt->execute()) {
    echo "Data logged successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>