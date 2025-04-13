<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#";
$dbname = "apprentice";

// Turn off direct error display
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log('Connection failed: ' . $conn->connect_error);
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

header('Content-Type: application/json');

// Log session data for debugging
error_log("Session Data: " . print_r($_SESSION, true));

// Check if user_info is set and is an array
if (isset($_SESSION['user_info']) && is_array($_SESSION['user_info'])) {
    $user_info = $_SESSION['user_info'];

    // Check if id is present in user_info
    if (isset($user_info['id'])) {
        $admin_id = $user_info['id'];

        // Log admin_id for debugging
        error_log("Admin ID from session: " . $admin_id);

        $sql = "SELECT name, email FROM hrinfo WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Log query result for debugging
            $adminData = $result->fetch_assoc();
            error_log("Query result: " . print_r($adminData, true));

            if ($adminData) {
                echo json_encode($adminData);
            } else {
                echo json_encode(['error' => 'Admin details not found']);
            }

            $stmt->close();
        } else {
            error_log('SQL prepare statement failed: ' . $conn->error);
            echo json_encode(['error' => 'SQL prepare statement failed: ' . $conn->error]);
        }
    } else {
        // Log when id is not found in user_info
        error_log("ID not found in user_info");
        echo json_encode(['error' => 'ID not found in user_info']);
    }
} else {
    // Log when user_info is not found in session
    error_log("user_info not found in session");
    echo json_encode(['error' => 'user_info not found in session']);
}

$conn->close();
?>
