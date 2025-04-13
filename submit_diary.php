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

// Function to save note
function saveNote($conn, $date, $entry) {
    $stmt = $conn->prepare("INSERT INTO work_diary_entries (wd_date, wd_entry, is_fully_submitted) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE wd_entry = VALUES(wd_entry), is_fully_submitted = 1");
    $stmt->bind_param("ss", $date, $entry);
    $stmt->execute();
    $stmt->close();
}

// Function to update submission status and period
function updateSubmissionStatus($conn, $period) {
    $stmt = $conn->prepare("UPDATE work_diary_entries SET wd_period = ? WHERE is_fully_submitted = 1");
    $stmt->bind_param("s", $period);
    $stmt->execute();
    $stmt->close();
}

$data = json_decode(file_get_contents('php://input'), true);

// Calculate the current period
$now = new DateTime();
$month = $now->format('m');
$year = $now->format('Y');
if ($now->format('d') > 20) {
    $month = ($month % 12) + 1;
    if ($month == 1) {
        $year += 1;
    }
}
$period = sprintf("%02d-%d", $month, $year);

// Save all entries
foreach ($_POST as $key => $value) {
    if (strpos($key, 'date') !== false) {
        $timestamp = str_replace('date', '', $key);
        $date = $_POST["date{$timestamp}"];
        $entry = $_POST["learning{$timestamp}"];
        saveNote($conn, $date, $entry);
    }
}

// Update submission status and period
updateSubmissionStatus($conn, $period);

$conn->close();

echo json_encode(['success' => true]);
?>
