<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Retrieve user information from session
$user_info = $_SESSION['user_info'];

// Database connection parameters
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

// Retrieve start_date and end_date from query parameters
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

// Validate date parameters
if (!$start_date || !$end_date) {
    echo json_encode(['error' => 'Invalid date range']);
    exit();
}

// Retrieve departments and locations from query parameters
$departments = isset($_GET['departments']) ? json_decode($_GET['departments'], true) : [];
$locations = isset($_GET['locations']) ? json_decode($_GET['locations'], true) : [];

// Validate departments and locations
if (empty($departments) || empty($locations)) {
    echo json_encode(['error' => 'Invalid departments or locations']);
    exit();
}

// Format the wd_period string for the query
$wd_period = date('F Y', strtotime($start_date)) . ' - ' . date('F Y', strtotime($end_date));

// Fetch period ID
$period_query = $conn->prepare("SELECT id FROM periods WHERE wd_period = ?");
if (!$period_query) {
    echo json_encode(['error' => 'Failed to prepare statement for period']);
    exit();
}
$period_query->bind_param("s", $wd_period);
$period_query->execute();
$period_result = $period_query->get_result();
$period_data = $period_result->fetch_assoc();
$period_id = $period_data['id'] ?? null;
$period_query->close();

// Convert departments and locations to comma-separated strings for SQL IN clause
$departments_str = implode("','", array_map([$conn, 'real_escape_string'], $departments));
$locations_str = implode("','", array_map([$conn, 'real_escape_string'], $locations));

// SQL query to join info, marks, and work_diary_entries tables, and filter by end date
$repOfId = $user_info[0]['id'];
$sql = "
SELECT info.id, info.name, info.reporting_officer_id, marks.total_marks, 
       CASE 
           WHEN MAX(work_diary_entries.is_fully_submitted) = 1 THEN 'Yes'
           ELSE 'No'
       END as submitted
FROM info
LEFT JOIN marks ON info.id = marks.apprentice_id AND marks.period_id = ?
LEFT JOIN work_diary_entries ON info.id = work_diary_entries.user_id AND work_diary_entries.wd_period = ?
WHERE info.department IN ('$departments_str') 
AND info.location IN ('$locations_str') 
AND info.reporting_officer_id = ?
AND (info.end_date IS NULL OR info.end_date >= ? OR info.end_date BETWEEN ? AND ?)
GROUP BY info.id, info.name, marks.total_marks";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit();
}

$stmt->bind_param('isssss', $period_id, $wd_period, $repOfId, $end_date, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$apprentices = [];
while ($row = $result->fetch_assoc()) {
    $apprentices[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['apprentices' => $apprentices, 'period_id' => $period_id]);
?>
