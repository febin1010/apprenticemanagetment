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

$sql = "SELECT total_marks, period_id, apprentice_id FROM marks";
$result = $conn->query($sql);

$marks = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $marks[] = $row;
    }
}

echo json_encode($marks);

$conn->close();
?>
