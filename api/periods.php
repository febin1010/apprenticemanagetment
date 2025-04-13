<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#"; 
$dbname = "apprentice";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$sql = "SELECT id, wd_period FROM periods";
$result = $conn->query($sql);

$periods = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $periods[$row['id']] = $row['wd_period']; 
    }
}

echo json_encode($periods);

$conn->close();
?>
