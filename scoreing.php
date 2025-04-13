<?php
session_start();

header('Content-Type: application/json');


// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: ROsignin.html");
    exit();
}

// Retrieve user information from session
$user_info = $_SESSION['user_info'];

// Check if apprentice ID is provided
if (!isset($_GET['apprentice_id'])) {
    header("Location: ropage.php"); // Redirect to main page if apprentice ID is not provided
    exit();
}

$sql = "SELECT wd_date, wd_entry, is_fully_submitted FROM work_diary WHERE apprentice_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $apprentice_id);
$stmt->execute();
$result = $stmt->get_result();

$workDiary = [];
while ($row = $result->fetch_assoc()) {
    $workDiary[] = $row;
}

// JSON encode the data to send back to JavaScript
echo json_encode($workDiaryData);
?>
