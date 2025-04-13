<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#";
$dbname = "apprentice";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch periods from the database
$sql = "SELECT wd_period FROM periods";
$result = $conn->query($sql);

$periods = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $periods[] = $row['wd_period'];
    }
}

// Output periods as JSON
header('Content-Type: application/json');
echo json_encode($periods);

// Close connection
$conn->close();
?>
