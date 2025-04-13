<?php
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

// Get the department parameter from the GET request
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Prepare the SQL query to select distinct reporting officer names
$sql = "SELECT DISTINCT Name FROM roinfo";

if ($department) {
    $sql .= " WHERE Department = '" . $conn->real_escape_string($department) . "'";
}

// Execute the query
$result = $conn->query($sql);

// Initialize an array to store the reporting officers
$reporting_officers = array();

if ($result->num_rows > 0) {
    // Fetch the results and store them in the array
    while ($row = $result->fetch_assoc()) {
        $reporting_officers[] = $row['Name'];
    }
}

// Output the reporting officers as a JSON array
echo json_encode($reporting_officers);

// Close the database connection
$conn->close();
?>
