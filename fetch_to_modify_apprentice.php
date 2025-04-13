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

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $apprentice_id = $_GET['id'];
    
    // Prepare and execute the SQL query to fetch apprentice details
    $stmt = $conn->prepare('
        SELECT 
            i.APPR_ID_NO, 
            i.NAME, 
            i.PHONE_NO, 
            i.location, 
            i.DEPARTMENT, 
            r.Name AS REPORTING_OFFICER_NAME, 
            i.STREAM, 
            l.EMAIL, 
	    i.start_date, i.end_date
        FROM 
            info i
        JOIN 
            roinfo r 
        ON 
            i.reporting_officer_id = r.id
        JOIN 
            login l 
        ON 
            i.APPR_ID_NO = l.APPR_ID_NO
        WHERE 
            i.APPR_ID_NO = ?
    ');

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param('s', $apprentice_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $apprentice_data = $result->fetch_assoc();
        echo json_encode($apprentice_data);
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID not provided']);
}

$conn->close();
?>
