<?php
session_start();

header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection
$servername = "localhost"; // Your database server name
$username = "root";    // Your database username
$password = "KMRl@$#$2024#";    // Your database password
$dbname = "apprentice"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

$user_id = $_SESSION['user_info']['id']; // Assuming user ID is stored in session

// Check the end_date for the user
$check_end_date_query = "SELECT end_date FROM info WHERE id = ?";
$check_end_date_stmt = $conn->prepare($check_end_date_query);
if ($check_end_date_stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}
$check_end_date_stmt->bind_param('i', $user_id);
$check_end_date_stmt->execute();
$check_end_date_stmt->bind_result($end_date);
$check_end_date_stmt->fetch();
$check_end_date_stmt->close();

if ($end_date && strtotime($end_date) < time()) {
    echo json_encode(['success' => false, 'message' => 'Your end date is met. Please meet the HR.']);
    $conn->close();
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => ''];

if ($data && is_array($data)) {
    foreach ($data as $entry) {
        if (!isset($entry['date']) || !isset($entry['note']) || $entry['note'] !== 'Sunday') {
            $response['message'] = 'Invalid entry format';
            continue;
        }

        $date = $entry['date'];
        $note = $entry['note'];

        // Check if the Sunday entry already exists
        $query = "SELECT COUNT(*) as count FROM work_diary_entries WHERE user_id = ? AND wd_date = ? AND wd_entry = ?";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param('iss', $user_id, $date, $note);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
            exit();
        }
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            // Insert the new Sunday entry
            $insert_query = "INSERT INTO work_diary_entries (user_id, wd_date, wd_entry) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            if ($insert_stmt === false) {
                echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
                exit();
            }
            $insert_stmt->bind_param('iss', $user_id, $date, $note);
            if ($insert_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Data inserted successfully';
            } else {
                $response['success'] = false;
                $response['message'] = 'Error inserting data: ' . $insert_stmt->error;
                break;
            }
            $insert_stmt->close();
        } else {
            $response['success'] = true;
            $response['message'] = 'Data already exists';
        }

        $stmt->close();
    }
} else {
    $response['message'] = 'Invalid data format';
}

$conn->close();
echo json_encode($response);
?>
