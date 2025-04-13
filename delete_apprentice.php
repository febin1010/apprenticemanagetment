<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

function log_error($message) {
    error_log($message, 3, '/path/to/error.log');
}

try {
    // Read input data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (!isset($data['id'])) {
        throw new Exception('Invalid input');
    }

    $apprenticeId = $data['id'];

    // Database connection
    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Prepare and execute query
    $sql = $conn->prepare('DELETE FROM info WHERE APPR_ID_NO = ?');
    $sql->bind_param('s', $apprenticeId);
    $sql->execute();

    $sql = $conn->prepare('DELETE FROM login WHERE APPR_ID_NO = ?');
    $sql->bind_param('s', $apprenticeId);
    $sql->execute();

    // Check if row was deleted
    if ($sql->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('No apprentice found with the provided ID');
    }

    // Close connections
    $sql->close();
    $conn->close();
} catch (Exception $e) {
    log_error($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
