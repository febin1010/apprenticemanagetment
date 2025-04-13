<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// Define a function to log errors to a file
function log_error($message) {
    error_log($message, 3, '/path/to/error.log');
}

try {
    // Read input data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (!isset($data['reporting_officer']) || !isset($data['department']) || !isset($data['location'])) {
        throw new Exception('Invalid input');
    }

    $reportingOfficer = $data['reporting_officer'];
    $department = $data['department'];
    $location = $data['location'];

    // Database connection
    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Prepare and execute query
    $sql = $conn->prepare('SELECT 1 FROM roinfo WHERE Name = ? AND Department = ? AND location = ?');
    $sql->bind_param('sss', $reportingOfficer, $department, $location);
    $sql->execute();
    $result = $sql->get_result();

    // Check result
    $isValid = $result->num_rows > 0;

    // Close connections
    $sql->close();
    $conn->close();

    // Return JSON response
    echo json_encode(['valid' => $isValid]);

} catch (Exception $e) {
    log_error($e->getMessage());
    // Return error in JSON format
    echo json_encode(['valid' => false, 'error' => $e->getMessage()]);
}
?>
