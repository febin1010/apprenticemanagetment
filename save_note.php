<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

function log_error($message) {
    error_log($message . "\n", 3, 'C:/wamp64/www/kmrl/error_log');
}

try {
    log_error("Script started");

    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }

    $rawPostData = file_get_contents('php://input');
    file_put_contents('data_log.txt', "Raw POST Data:\n" . $rawPostData . "\n", FILE_APPEND);

    $data = json_decode($rawPostData, true);

    if ($data === null) {
        $data = $_POST;
    }

    file_put_contents('data_log.txt', "Decoded Data:\n" . print_r($data, true) . "\n", FILE_APPEND);

    if (!isset($data['wd_entry']) && !isset($data['note'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        file_put_contents('data_log.txt', "Error: Invalid input. Missing wd_entry or note.\n", FILE_APPEND);
        exit();
    }

    $note = isset($data['wd_entry']) ? $data['wd_entry'] : $data['note'];
    $date = isset($data['wd_date']) ? $data['wd_date'] : (isset($data['date']) ? $data['date'] : null);
    $entry_id = isset($data['id']) ? $data['id'] : (isset($data['entry_id']) ? $data['entry_id'] : null);
    $user_id = $_SESSION['user_info']['id'];

    file_put_contents('data_log.txt', "Extracted Variables:\nNote: $note, Date: $date, Entry ID: $entry_id, User ID: $user_id\n", FILE_APPEND);

    $servername = "localhost";
    $username = "root";
    $password = "KMRl@$#$2024#";
    $dbname = "apprentice";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        file_put_contents('data_log.txt', "Error: Database connection failed. " . $conn->connect_error . "\n", FILE_APPEND);
        exit();
    }

    file_put_contents('data_log.txt', "Database connection established\n", FILE_APPEND);

    $stmt = $conn->prepare("SELECT end_date FROM info WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed']);
        file_put_contents('data_log.txt', "Error: Prepare statement failed. " . $conn->error . "\n", FILE_APPEND);
        exit();
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($end_date);
    $stmt->fetch();
    $stmt->close();


    $today = new DateTime();
    $endDate = DateTime::createFromFormat('Y-m-d', $end_date);

    if ($endDate && $endDate < $today) {
        echo json_encode(['success' => false, 'message' => 'Your end date is met. Please meet the HR.']);
        file_put_contents('data_log.txt', "Error: End date has passed for user ID: $user_id\n", FILE_APPEND);
        $conn->close();
        exit();
    }


    file_put_contents('data_log.txt', "End date check passed\n", FILE_APPEND);

    if ($entry_id) {
        $stmt = $conn->prepare("UPDATE work_diary_entries SET wd_entry = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $note, $entry_id, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO work_diary_entries (user_id, wd_date, wd_entry) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $date, $note);
    }

    file_put_contents('data_log.txt', "SQL statement prepared\n", FILE_APPEND);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        file_put_contents('data_log.txt', "Success: Note saved successfully.\n", FILE_APPEND);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving note']);
        file_put_contents('data_log.txt', "SQL Error: {$stmt->error}\n", FILE_APPEND);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    log_error("Exception: " . $e->getMessage());
    if (isset($conn)) {
        $conn->rollback();
        log_error("Transaction rolled back");
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
        log_error("Database connection closed");
    }
}
?>
