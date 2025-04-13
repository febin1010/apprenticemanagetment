<?php
$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#";
$dbname = "apprentice";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$location = isset($_GET['location']) ? $_GET['location'] : '';

$sql = "SELECT department FROM department WHERE location = '" . $conn->real_escape_string($location) . "'";
$result = $conn->query($sql);

$departments = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $departments[] = $row['department'];
    }
}

echo json_encode($departments);

$conn->close();
?>
