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

$sql = "SELECT id, NAME, DEPARTMENT, LOCATION FROM info";
$result = $conn->query($sql);

$info = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $info[$row['id']] = [
            'name' => $row['NAME'],
            'department' => $row['DEPARTMENT'],
            'location' => $row['LOCATION'] 
        ];
    }
}

echo json_encode($info);

$conn->close();
?>
