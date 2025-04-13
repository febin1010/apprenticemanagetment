<?php
$host = 'localhost';
$username = 'root';
$password = 'KMRl@$#$2024#';
$database = 'apprentice';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable detailed error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['new-admin-name']) && !empty($data['new-admin-email']) && !empty($data['new-admin-password'])) {
        $name = $conn->real_escape_string($data['new-admin-name']);
        $email = $conn->real_escape_string($data['new-admin-email']);
        $password = password_hash($data['new-admin-password'], PASSWORD_DEFAULT); // Hash password

        // Insert new admin into database
        $insert_query = "INSERT INTO hrinfo (Name, email) VALUES ('$name', '$email')";
        if ($conn->query($insert_query) === TRUE) {
            $admin_id = $conn->insert_id; // Get the ID of the newly inserted admin
            $login_query = "INSERT INTO hrlogin (EMAIL, PASSWORD) VALUES ('$email', '$password')";
            if ($conn->query($login_query) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Admin added successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error adding admin login details: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Error adding admin details: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Name, email, or password is missing']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
?>
