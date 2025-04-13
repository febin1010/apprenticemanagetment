<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: signin.html");
    exit();
}

// Database connection
$host = 'localhost';
$username = 'root';
$password = 'KMRl@$#$2024#';
$database = 'apprentice';
$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Retrieve POST data
$appr_id_no = $_POST['appr_id'] ?? '';
$period_id = $_POST['month'] ?? '';  // Assuming 'month' contains period information

// Debugging: Output POST data for troubleshooting
if (empty($appr_id_no) || empty($period_id)) {
    die("Missing APPR_ID_NO or period_id. POST data: " . print_r($_POST, true));
}

// Extract period details
$period_query = $mysqli->prepare("SELECT wd_period FROM periods WHERE wd_period = ?");
$period_query->bind_param("s", $period_id);
$period_query->execute();
$period_result = $period_query->get_result();
$period_data = $period_result->fetch_assoc();
$period_query->close();

if (!$period_data) {
    die("Invalid period_id.");
}

$wd_period = $period_data['wd_period'];
$period_parts = explode(' - ', $wd_period);

if (count($period_parts) !== 2) {
    die("Invalid wd_period format. Expected format: 'Start Month Year - End Month Year'.");
}

$start_month_year = $period_parts[0];
$end_month_year = $period_parts[1];

try {
    $start_date = new DateTime("21 $start_month_year");
    $end_date = new DateTime("20 $end_month_year");
    $end_date->setDate($end_date->format('Y'), $end_date->format('m'), 20);
} catch (Exception $e) {
    die("Date parsing error: " . $e->getMessage());
}

$start_date_str = $start_date->format('Y-m-d');
$end_date_str = $end_date->format('Y-m-d');

// Retrieve work diary entries from POST
$work_diary_entries = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'date') === 0) {
        $date = $value;
        $id = str_replace('date', '', $key);
        $work_diary_entries[$id]['date'] = $date;
    } elseif (strpos($key, 'learning') === 0) {
        $id = str_replace('learning', '', $key);
        $work_diary_entries[$id]['learning'] = $value;
    }
}

// Update or insert work diary entries
foreach ($work_diary_entries as $key => $entry) {
    $date = $entry['date'];
    $learning = $entry['learning'];

    // Check if entry exists
    $check_query = $mysqli->prepare("
        SELECT id FROM work_diary_entries
        WHERE user_id = ? AND wd_date = ? AND wd_period = ?
    ");
    $check_query->bind_param("iss", $appr_id_no, $date, $wd_period);
    $check_query->execute();
    $check_result = $check_query->get_result();
    $existing_entry = $check_result->fetch_assoc();
    $check_query->close();

    if ($existing_entry) {
        // Update existing entry
        $update_query = $mysqli->prepare("
            UPDATE work_diary_entries
            SET wd_entry = ?, submitted_date = NOW(), is_fully_submitted = 1
            WHERE id = ?
        ");
        $update_query->bind_param("si", $learning, $existing_entry['id']);
        $update_query->execute();
        $update_query->close();
    } else {
        // Insert new entry
        $insert_query = $mysqli->prepare("
            INSERT INTO work_diary_entries (user_id, wd_date, wd_entry, wd_period, submitted_date, is_fully_submitted)
            VALUES (?, ?, ?, ?, NOW(), 1)
        ");
        $insert_query->bind_param("isss", $appr_id_no, $date, $learning, $wd_period);
        $insert_query->execute();
        $insert_query->close();
    }
}

$mysqli->close();

// Redirect to the edit page with the apprentice_id and period_id as query parameters
header("Location: hrpage.php");
exit();
?>
