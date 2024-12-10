<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seismodb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the latest sensor data
$sql = "SELECT * FROM sensor_datas ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);
$latest_data = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-control" content="no-cache">
    <title>Seismo: Seismic Monitoring and Alert System for Earthquake Preparedness at Olongapo City National High School</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body>
    <button id="notify-btn" style="opacity: 0;">Test Notification</button>
    <div class="logo-sub-container">
        <img src="assets/img/logo.png" alt="">
        <div class="text-cont">
            <h1 class="main-heading">Seismo:</h1>
            <p class="heading-sub">Seismic Monitoring and Alert System for Earthquake Preparedness at Olongapo City National High School</p>
        </div>
    </div>
    <div class="main-container-body">
        <div class="whole-wrap">
            <div class="earthquake-status-card" id="earthquakeStatus" style="display: <?php echo ($latest_data['earthquake_detected'] == 'YES' ? 'flex' : 'none'); ?>">
                <div class="card-text">
                    <h2 class="earthquake-stat-head">Earthquake Detected</h2>
                    <p class="stat-subtitle">Detected at <span class="time"><?php echo date('h:iA', strtotime($latest_data['timestamp'])); ?></span></p>
                </div>
                <span class="material-symbols-outlined">error</span>
            </div>
            <h4 class="section-name">Sensor Readings</h4>
            <div class="sensor-readings-wrapper">
                <div class="sensor-reading-card">
                    <div class="sensor-name-logo">
                        <span class="material-symbols-outlined">vibration</span>
                        <h3 class="sensor-name">Vibration Sensor</h3>
                    </div>
                    <p class="vibration-digiread" id="vibrationValue"><?php echo $latest_data['vibration_value']; ?></p>
                    <p class="subtitle-type">Digital Reading</p>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" id="vibrationBar" style="width: <?php
                                                                                        echo ($latest_data['vibration_threshold'] != 0
                                                                                            ? ($latest_data['vibration_value'] / $latest_data['vibration_threshold'] * 100)
                                                                                            : 0);
                                                                                        ?>%;"></div>
                    </div>
                    <p class="max-threshold">Max Threshold: <span class="thresholdVal"><?php echo $latest_data['vibration_threshold']; ?></span></p>
                </div>
                <div class="sensor-reading-card">
                    <div class="sensor-name-logo">
                        <span class="material-symbols-outlined">animation</span>
                        <h3 class="sensor-name">MPU6050 Sensor</h3>
                    </div>
                    <p class="accel-name">Acceleration Values:</p>
                    <div class="accel-coordinates-container">
                        <p class="coordinate">X:</p>
                        <p class="acceleration-g"><?php echo $latest_data['accel_x']; ?>g</p>
                        <div class="progress-bar-accel">
                            <div class="progress-bar-fill" id="accelXBar" style="width: <?php
                                                                                        echo ($latest_data['accel_threshold'] != 0
                                                                                            ? (abs($latest_data['accel_x']) / $latest_data['accel_threshold'] * 100)
                                                                                            : 0);
                                                                                        ?>%;"></div>

                        </div>
                    </div>
                    <div class="accel-coordinates-container">
                        <p class="coordinate">Y:</p>
                        <p class="acceleration-g"><?php echo $latest_data['accel_y']; ?>g</p>
                        <div class="progress-bar-accel">
                            <div class="progress-bar-fill" id="accelYBar" style="width: <?php
                                                                                        echo ($latest_data['accel_threshold'] != 0
                                                                                            ? (abs($latest_data['accel_y']) / $latest_data['accel_threshold'] * 100)
                                                                                            : 0);
                                                                                        ?>%;"></div>

                        </div>
                    </div>
                    <div class="accel-coordinates-container">
                        <p class="coordinate">Z:</p>
                        <p class="acceleration-g"><?php echo $latest_data['accel_z']; ?>g</p>
                        <div class="progress-bar-accel">
                            <div class="progress-bar-fill" id="accelZBar" style="width: <?php
                                                                                        echo ($latest_data['accel_threshold'] != 0
                                                                                            ? (abs($latest_data['accel_z']) / $latest_data['accel_threshold'] * 100)
                                                                                            : 0);
                                                                                        ?>%;"></div>

                        </div>
                    </div>
                    <p class="max-threshold">Max Threshold: <span class="thresholdValMPU"><?php echo $latest_data['accel_threshold']; ?>g</span></p>
                </div>
            </div>
            <h4 class="section-name">Condition Checking</h4>
            <div class="condition-wrapper">
                <div class="condition-section">
                    <div class="sensor-condition-wrapper">
                        <span class="material-symbols-outlined">vibration</span>
                        <p class="sensor-condition-name">Vibration Sensor</p>
                    </div>
                    <p class="condition-status" id="vibrationStatus">
                        <?php echo ($latest_data['vibration_value'] >= $latest_data['vibration_threshold'] ? 'HIGH' : 'LOW'); ?>
                    </p>
                </div>
                <div class="condition-section">
                    <div class="sensor-condition-wrapper">
                        <span class="material-symbols-outlined">animation</span>
                        <p class="sensor-condition-name">MPU6050 Accel. and Gyroscope</p>
                    </div>
                    <p class="condition-status" id="mpuStatus">
                        <?php
                        $mpu_high = abs($latest_data['accel_x']) > $latest_data['accel_threshold'] ||
                            abs($latest_data['accel_y']) > $latest_data['accel_threshold'] ||
                            abs($latest_data['accel_z']) > $latest_data['accel_threshold'];
                        echo ($mpu_high ? 'HIGH' : 'LOW');
                        ?>
                    </p>
                </div>
                <hr>
                <div class="condition-section">
                    <div class="sensor-condition-wrapper">
                        <span class="material-symbols-outlined">e911_emergency</span>
                        <p class="sensor-condition-name">Siren (Relay Module)</p>
                    </div>
                    <p class="condition-status" id="relayStatus"><?php echo $latest_data['relay_status']; ?></p>
                </div>
            </div>
        </div>
    </div>
    <script src="js/notifier.js"></script>
    <script src="js/data-updater.js"></script>
</body>

</html>