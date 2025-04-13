<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "KMRl@$#$2024#";
$dbname = "apprentice";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

header('Content-Type: application/json');

// Log session data for debugging
error_log("Session Data: " . print_r($_SESSION, true));

if (isset($_SESSION['user_info'])) {
    $user_info = $_SESSION['user_info'];

    if (isset($user_info['id'])) {
        $admin_id = $user_info['id'];

        // Log admin_id for debugging
        error_log("Admin ID from session: " . $admin_id);

        $data_raw = file_get_contents('php://input');
        error_log("Raw input data: " . $data_raw);

        $data = json_decode($data_raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            die(json_encode(['error' => 'Invalid JSON input: ' . json_last_error_msg()]));
        }

        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);
        $password = !empty($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : null;

        // Fetch the current email to check for changes
        $current_email_query = "SELECT email FROM hrinfo WHERE id = ?";
        if ($stmt = $conn->prepare($current_email_query)) {
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_email_data = $result->fetch_assoc();
            $current_email = $current_email_data['email'];
            $stmt->close();
        } else {
            die(json_encode(['error' => 'SQL prepare statement failed: ' . $conn->error]));
        }

        // Update hrinfo table
        $sql = "UPDATE hrinfo SET name = ?, email = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $name, $email, $admin_id);
            if ($stmt->execute()) {
                $stmt->close();

                // Update hrlogin table if password is provided or email is changed
                if ($password || $email !== $current_email) {
                    $update_login_sql = "UPDATE hrlogin SET ";
                    $params = [];
                    $types = "";

                    if ($password) {
                        $update_login_sql .= "password = ?, ";
                        $params[] = $password;
                        $types .= "s";
                    }
                    if ($email !== $current_email) {
                        $update_login_sql .= "email = ?, ";
                        $params[] = $email;
                        $types .= "s";
                    }

                    // Remove the trailing comma and space
                    $update_login_sql = rtrim($update_login_sql, ", ") . " WHERE email = ?";
                    $params[] = $current_email;
                    $types .= "s";

                    if ($stmt = $conn->prepare($update_login_sql)) {
                        $stmt->bind_param($types, ...$params);
                        if ($stmt->execute()) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Error updating hrlogin: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['error' => 'SQL prepare statement failed: ' . $conn->error]);
                    }
                } else {
                    echo json_encode(['success' => true]);
                }
            } else {
                echo json_encode(['error' => 'Error updating admin details: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['error' => 'SQL prepare statement failed: ' . $conn->error]);
        }
    } else {
        error_log("ID not found in user_info");
        echo json_encode(['error' => 'ID not found in user_info']);
    }
} else {
    error_log("user_info not found in session");
    echo json_encode(['error' => 'user_info not found in session']);
}

$conn->close();
?>
