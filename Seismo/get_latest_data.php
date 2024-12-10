<?php
// get_latest_data.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seismodb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM sensor_datas ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

echo json_encode($data);

$conn->close();
?>