<?php
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('Received data in get_officer_details.php: ' . print_r($data, true));
    
    $id = $data['id'];

    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Updated SQL query to fetch Location
    $sql = $conn->prepare('SELECT Name, location, Department, Email FROM roinfo WHERE id = ?');
    if (!$sql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $sql->bind_param('i', $id);
    $sql->execute();
    $result = $sql->get_result();
    if ($row = $result->fetch_assoc()) {
        error_log('Returning officer details from get_officer_details.php: ' . print_r($row, true));
        echo json_encode(['success' => true, 'officer' => $row]);
    } else {
        throw new Exception('Officer not found');
    }

} catch (Exception $e) {
    error_log('Error in get_officer_details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
