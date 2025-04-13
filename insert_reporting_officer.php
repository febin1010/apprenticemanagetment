<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

function log_error($message) {
    error_log($message . "\n", 3, 'C:/wamp64/www/kmrl/error_log');
}

try {
    log_error("Script started");

    $data = json_decode(file_get_contents('php://input'), true);
    log_error("Input data: " . json_encode($data));

    $requiredFields = ['reporting-officer-name', 'reporting-officer-location', 'reporting-officer-department', 'reporting-officer-email', 'reporting-officer-password'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $name = $data['reporting-officer-name'];
    $location = $data['reporting-officer-location']; // Added location field
    $department = $data['reporting-officer-department'];

    $email = $data['reporting-officer-email'];
    $password = password_hash($data['reporting-officer-password'], PASSWORD_DEFAULT);

    log_error("All required fields are present");

    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    log_error("Database connected successfully");

    // Check if email already exists
    //$sql = $conn->prepare('SELECT id FROM roinfo WHERE email = ?');
    //if (!$sql) {
    //    throw new Exception('Prepare statement failed: ' . $conn->error);
    //}
    //$sql->bind_param('s', $email);
    //$sql->execute();
    //$result = $sql->get_result();
    //if ($result->num_rows > 0) {
    //    throw new Exception('Email already exists');
    //}
    //log_error("Email does not exist, proceeding with insertion");

    $conn->begin_transaction();

    $sql = $conn->prepare('INSERT INTO roinfo (Name, location, Department, email) VALUES (?, ?, ?, ?)');
    if (!$sql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $sql->bind_param('ssss', $name, $location, $department, $email);
    $sql->execute();
    if ($sql->errno) {
        throw new Exception('Execute failed: ' . $sql->error);
    }
    log_error("Inserted into roinfo table");

    // Get the generated id from roinfo table
    $officer_id = $conn->insert_id;

    $sql = $conn->prepare('INSERT INTO rologin (officer_id, EMAIL, PASSWORD) VALUES (?, ?, ?)');
    if (!$sql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $sql->bind_param('iss', $officer_id, $email, $password);
    $sql->execute();
    if ($sql->errno) {
        throw new Exception('Execute failed: ' . $sql->error);
    }
    log_error("Inserted into rologin table");

    $conn->commit();
    log_error("Transaction committed");

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    if (isset($conn)) {
        $conn->rollback();
        log_error("Transaction rolled back");
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
        log_error("Database connection closed");
    }
}
?>
