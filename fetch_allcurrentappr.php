<?php
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "localhost"; // Replace with your server name
$username = "root";        // Replace with your database username
$password = "KMRl@$#$2024#"; // Replace with your database password
$dbname = "apprentice";    // Replace with your database name

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get current date
$currentDate = date('Y-m-d');

// Prepare SQL query to fetch active apprentices (with end_date not met)
$query = "SELECT id, name, location, department 
          FROM info 
          WHERE end_date IS NULL OR end_date > ?";

// Initialize prepared statement
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare SQL statement: ' . $conn->error]);
    exit();
}

// Bind the current date parameter
$stmt->bind_param('s', $currentDate);

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Fetch data and format it for DataTables
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Close the statement and the database connection
$stmt->close();
$conn->close();

// Return data in JSON format
echo json_encode(['data' => $data]);
?>
