<?php
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('Received data in search_officers.php: ' . print_r($data, true));
    
    $department = $data['department'];
    $location = isset($data['location']) ? $data['location'] : ''; // New: Location parameter

    $conn = new mysqli('localhost', 'root', 'KMRl@$#$2024#', 'apprentice');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Updated SQL query to include Location filtering
    $sql = $conn->prepare('SELECT id, Name, location, Department FROM roinfo WHERE Department LIKE ? AND Location LIKE ?');
    if (!$sql) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    $searchTerm = "%$department%";
    $locationTerm = "%$location%"; // New: Location search term
    $sql->bind_param('ss', $searchTerm, $locationTerm);
    $sql->execute();
    $result = $sql->get_result();
    $officers = [];
    while ($row = $result->fetch_assoc()) {
        $officers[] = $row;
    }

    error_log('Returning officers from search_officers.php: ' . print_r($officers, true));
    echo json_encode(['success' => true, 'officers' => $officers]);

} catch (Exception $e) {
    error_log('Error in search_officers.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
