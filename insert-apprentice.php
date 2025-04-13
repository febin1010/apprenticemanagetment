<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// Define a function to log errors to a file
function log_error($message) {
    error_log($message . "\n", 3, 'C:\wamp64\www\kmrl\error_log');
}

try {
    log_error("Script started");

    $data = json_decode(file_get_contents('php://input'), true);
    log_error("Input data: " . json_encode($data));

    // Check if all required data is present
    $requiredFields = [
        'apprenticeId', 'name', 'phone', 'location', 'department', 'reportingOfficer', 
        'stream', 'email', 'password', 'startDate', 'endDate'
    ];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $apprenticeID = $data['apprenticeId'];
    $name = $data['name'];
    $phone = $data['phone'];
    $location = $data['location'];
    $department = $data['department'];
    $reportingOfficer = $data['reportingOfficer'];
    $stream = $data['stream'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $start_date = $data['startDate'];
    $end_date = $data['endDate'];

    log_error("All required fields are present");

    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    log_error("Database connected successfully");

    // Get reporting officer ID
    $sql = $conn->prepare('SELECT id FROM roinfo WHERE Name = ? AND Department = ? AND location = ?');
    if (!$sql) {
        throw new Exception('Prepare statement failed (select): ' . $conn->error);
    }
    $sql->bind_param('sss', $reportingOfficer, $department, $location);
    $sql->execute();
    $result = $sql->get_result();
    if (!$result) {
        throw new Exception('Execute failed (select): ' . $sql->error);
    }
    $row = $result->fetch_assoc();

    if (!$row) {
        throw new Exception('Reporting officer not found');
    }

    $reportingOfficerID = $row['id'];
    log_error("Reporting officer ID retrieved: $reportingOfficerID");

    // Begin transaction
    $conn->begin_transaction();

    // Insert into info table
    $sql = $conn->prepare('INSERT INTO info (APPR_ID_NO, NAME, PHONE_NO, location, DEPARTMENT, reporting_officer_id, STREAM, EMAIL, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$sql) {
        throw new Exception('Prepare statement failed (insert into info): ' . $conn->error);
    }
    $sql->bind_param('ssssssssss', $apprenticeID, $name, $phone, $location, $department, $reportingOfficerID, $stream, $email, $start_date, $end_date);
    $sql->execute();
    if ($sql->errno) {
        throw new Exception('Execute failed (insert into info): ' . $sql->error);
    }
    log_error("Inserted into info table");

    // Insert into login table
    $sql = $conn->prepare('INSERT INTO login (EMAIL, PASSWORD, APPR_ID_NO) VALUES (?, ?, ?)');
    if (!$sql) {
        throw new Exception('Prepare statement failed (insert into login): ' . $conn->error);
    }
    $sql->bind_param('sss', $email, $password, $apprenticeID);
    $sql->execute();
    if ($sql->errno) {
        throw new Exception('Execute failed (insert into login): ' . $sql->error);
    }
    log_error("Inserted into login table");

    // Commit transaction
    $conn->commit();
    log_error("Transaction committed");

    // Return success response
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
        log_error("Transaction rolled back");
    }
    // Return error response
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    // Close database connection
    if (isset($conn)) {
        $conn->close();
        log_error("Database connection closed");
    }
}
?>
