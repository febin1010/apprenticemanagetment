<?php
// update_finalize.php

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Read input data
$input = json_decode(file_get_contents('php://input'), true);

$appr_id_no = $input['appr_id_no'];
$wd_period = $input['wd_period'];

error_log("Received request to finalize. APPR_ID_NO: $appr_id_no, WD_PERIOD: $wd_period");

// Validate input
if (empty($appr_id_no) || empty($wd_period)) {
    error_log("Invalid input: APPR_ID_NO or WD_PERIOD is missing");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#";
$dbname = "apprentice";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Fetch apprentice_id from info table using APPR_ID_NO
$id_query = $conn->prepare("SELECT id FROM info WHERE APPR_ID_NO = ?");
$id_query->bind_param("s", $appr_id_no);
$id_query->execute();
$id_result = $id_query->get_result();
$id_row = $id_result->fetch_assoc();
$apprentice_id = $id_row['id'];
$id_query->close();

error_log("Fetched apprentice_id: $apprentice_id for APPR_ID_NO: $appr_id_no");

if (!$apprentice_id) {
    error_log("Apprentice ID not found for APPR_ID_NO: $appr_id_no");
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Apprentice ID not found']);
    exit();
}

// Fetch period_id from periods table using wd_period
$period_id_query = $conn->prepare("SELECT id FROM periods WHERE wd_period = ?");
$period_id_query->bind_param("s", $wd_period);
$period_id_query->execute();
$period_id_result = $period_id_query->get_result();
$period_id_row = $period_id_result->fetch_assoc();
$period_id = $period_id_row['id'];
$period_id_query->close();

error_log("Fetched period_id: $period_id for WD_PERIOD: $wd_period");

if (!$period_id) {
    error_log("Period ID not found for WD_PERIOD: $wd_period");
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Period ID not found']);
    exit();
}

// Update marks table to set finalize = 1
$update_query = $conn->prepare("UPDATE marks SET finalize = 1 WHERE apprentice_id = ? AND period_id = ?");
$update_query->bind_param("ii", $apprentice_id, $period_id);

if ($update_query->execute()) {
    error_log("Successfully updated finalize status for apprentice_id: $apprentice_id, period_id: $period_id");
    echo json_encode(['success' => true]);

    // Extract end month and year from wd_period
    $period_parts = explode(' - ', $wd_period);
    $end_period = $period_parts[1];
    $end_period_parts = explode(' ', $end_period);
    $end_month = $end_period_parts[0];
    $end_year = $end_period_parts[1];

    // Create a DateTime object for the 20th of the end month
    $end_period_date = DateTime::createFromFormat('F Y', "$end_month $end_year");
    $end_period_date->setDate($end_period_date->format('Y'), $end_period_date->format('m'), 20);

    // Get current date
    $current_date = new DateTime();

    if ($current_date > $end_period_date) {
        error_log("Current date is past the 20th of the end period month");

        // Extract current month and year
        $current_month = $current_date->format('F');
        $current_year = $current_date->format('Y');

        // Determine next period
        $next_month = new DateTime("first day of next month");
        $next_month_name = $next_month->format('F');
        $next_year = $next_month->format('Y');

        // Create wd_period for next period
        $next_wd_period = "$current_month $current_year - $next_month_name $next_year";

        // Check if next period entry exists in the periods table
        $next_period_query = $conn->prepare("SELECT id FROM periods WHERE wd_period = ?");
        $next_period_query->bind_param("s", $next_wd_period);
        $next_period_query->execute();
        $next_period_result = $next_period_query->get_result();

        if ($next_period_result->num_rows == 0) {
            // Insert new period entry
            $insert_period_query = $conn->prepare("INSERT INTO periods (wd_period) VALUES (?)");
            $insert_period_query->bind_param("s", $next_wd_period);
            if ($insert_period_query->execute()) {
                error_log("Successfully inserted next period: $next_wd_period");
            } else {
                error_log("Failed to insert next period: $next_wd_period");
            }
            $insert_period_query->close();
        } else {
            error_log("Next period already exists: $next_wd_period");
        }
        $next_period_query->close();
    }

} else {
    error_log("Failed to update finalize status for apprentice_id: $apprentice_id, period_id: $period_id");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$update_query->close();
$conn->close();
?>
