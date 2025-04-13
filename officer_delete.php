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

// Handle search request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['department']) && isset($_GET['location'])) {
    $department = $conn->real_escape_string($_GET['department']);
    $location = $conn->real_escape_string($_GET['location']);
    $sql = "SELECT id, Name, location, Department FROM roinfo WHERE Department LIKE '%$department%' AND location LIKE '%$location%'";
    $result = $conn->query($sql);

    $officers = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $officers[] = $row;
        }
        
    }
    echo json_encode(['officers' => $officers]);
    exit();
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $data);
    if (isset($data['id'])) {
        $officerId = $conn->real_escape_string($data['id']);
        
        // Delete from rologin table first
        $sql1 = "DELETE FROM rologin WHERE officer_id = $officerId";
        $result1 = $conn->query($sql1);

        // Delete from roinfo table
        $sql2 = "DELETE FROM roinfo WHERE id = $officerId";
        $result2 = $conn->query($sql2);

        if ($result1 === TRUE && $result2 === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }
    exit();
}

$conn->close();
?>
