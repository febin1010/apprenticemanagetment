<?php
session_start();
header('Content-Type: application/json');

// Start output buffering
ob_start();

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to handle output cleanly
function cleanOutputAndRespond($response) {
    ob_end_clean(); // Clear the buffer to prevent any unwanted output
    echo json_encode($response);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    cleanOutputAndRespond(['success' => false, 'message' => 'Unauthorized access.']);
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cleanOutputAndRespond(['success' => false, 'message' => 'Invalid request method.']);
}

// Get the input data from the request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['apprentice_id']) || !isset($input['period_id'])) {
    cleanOutputAndRespond(['success' => false, 'message' => 'Missing required parameters.']);
}

$apprenticeId = $input['apprentice_id'];
$periodId = $input['period_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#";
$dbname = "apprentice";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    cleanOutputAndRespond(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
}

// Fetch wd_period from periods table using period_id
$sql = "SELECT wd_period FROM periods WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $periodId);

if ($stmt->execute()) {
    $stmt->bind_result($wdPeriod);
    if ($stmt->fetch()) {
        // Update the is_fully_submitted flag in the work_diary_entries table based on user_id and wd_period
        $stmt->close();

        $updateSql = "UPDATE work_diary_entries SET is_fully_submitted = 0 WHERE user_id = ? AND wd_period = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('is', $apprenticeId, $wdPeriod);

        if ($updateStmt->execute()) {
            cleanOutputAndRespond(['success' => true, 'message' => 'Apprentice entries rejected successfully.']);
        } else {
            cleanOutputAndRespond(['success' => false, 'message' => 'Failed to reject apprentice entries: ' . $updateStmt->error]);
        }

        $updateStmt->close();
    } else {
        cleanOutputAndRespond(['success' => false, 'message' => 'Period not found.']);
    }
} else {
    cleanOutputAndRespond(['success' => false, 'message' => 'Failed to fetch period: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

// Flush the buffer and end it
ob_end_clean();
echo json_encode(['success' => true, 'message' => 'Process completed.']);
exit();
?>
