<?php
session_start();

header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection
$servername = "localhost"; 
$username = "root";    
$password = "KMRl@$#$2024#";    
$dbname = "apprentice";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Read input data
$data = json_decode(file_get_contents('php://input'), true);

// Initialize response
$response = ['success' => false, 'message' => ''];

if ($data) {
    $is_fully_submitted = $data['is_fully_submitted'];
    $wd_period = $data['wd_period'];
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];

    // Check the end_date for the user
    $user_id = $_SESSION['user_info']['id']; // Assuming user ID is stored in session
    $check_end_date_query = "SELECT end_date FROM info WHERE id = ?";
    $check_end_date_stmt = $conn->prepare($check_end_date_query);
    if ($check_end_date_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }
    $check_end_date_stmt->bind_param('i', $user_id);
    $check_end_date_stmt->execute();
    $check_end_date_stmt->bind_result($user_end_date);
    $check_end_date_stmt->fetch();
    $check_end_date_stmt->close();

    if ($user_end_date && strtotime($user_end_date) < time()) {
        echo json_encode(['success' => false, 'message' => 'Your end date is met. Please meet the HR.']);
        $conn->close();
        exit();
    }

    // Update is_fully_submitted and wd_period
    $update_query = "UPDATE work_diary_entries SET is_fully_submitted = ?, wd_period = ? WHERE user_id = ? AND wd_date BETWEEN ? AND ?";
    $update_stmt = $conn->prepare($update_query);

    if ($update_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }

    $update_stmt->bind_param('isiss', $is_fully_submitted, $wd_period, $user_id, $start_date, $end_date);

    if ($update_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Database updated successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'Error updating database: ' . $update_stmt->error;
    }

    $update_stmt->close();
} else {
    $response['message'] = 'Invalid data format';
}

$conn->close();
echo json_encode($response);
?>

