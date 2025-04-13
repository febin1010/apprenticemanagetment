<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#";
$dbname = "apprentice";

$conn = new mysqli($servername, $username, $password, $dbname);

// Enable error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json');
error_log(print_r($_GET, true));


try {
    // Validate and sanitize GET parameters
    $department = isset($_GET['department']) ? $conn->real_escape_string($_GET['department']) : null;
    $reporting_officer = isset($_GET['reporting-officer']) ? $conn->real_escape_string($_GET['reporting-officer']) : null;
    $period = isset($_GET['period']) ? $conn->real_escape_string($_GET['period']) : null;

    if (!$department || !$reporting_officer || !$period) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters', 'received' => $_GET]);
        exit();
    }

    // Fetch period_id based on the period name
    $period_query = $conn->prepare("SELECT id FROM periods WHERE wd_period = ?");
    $period_query->bind_param("s", $period);
    $period_query->execute();
    $period_result = $period_query->get_result();

    if ($period_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid period']);
        exit();
    }

    $period_id = $period_result->fetch_assoc()['id'];

    // Fetch apprentices based on department, reporting officer, and period
    $query = "
        SELECT i.NAME, m.total_marks, i.APPR_ID_NO,m.finalize
        FROM info i
        JOIN marks m ON i.id = m.apprentice_id
        JOIN roinfo r ON i.reporting_officer_id = r.id
        WHERE i.DEPARTMENT = ? AND r.Name = ? AND m.period_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $department, $reporting_officer, $period_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $apprentices = [];
    while ($row = $result->fetch_assoc()) {
        $apprentices[] = $row;
    }

    echo json_encode($apprentices);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
