<?php
$host = 'localhost';
$username = 'root';
$password = 'KMRl@$#$2024#';
$database = 'apprentice';

session_start();

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable detailed error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is already logged in via a cookie
if (isset($_COOKIE['ro_info'])) {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_info'] = json_decode($_COOKIE['ro_info'], true);
    header("Location: ropage.php");
    exit();
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);
        
        // Fetch all password entries from database for the given email
        $query = "SELECT PASSWORD FROM rologin WHERE EMAIL = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $password_valid = false;

            // Verify password against all stored passwords
            while ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['PASSWORD'])) {
                    $password_valid = true;
                    break;
                }
            }

            if ($password_valid) {
                // Password is correct, fetch all relevant user info
                $info_query = "SELECT id, Name, Department, Location,email FROM roinfo WHERE email = '$email'";
                $info_result = $conn->query($info_query);

                if ($info_result->num_rows > 0) {
                    $user_info = array();
                    while ($info_row = $info_result->fetch_assoc()) {
                        $user_info[] = $info_row;
                    }
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_info'] = $user_info;

                    setcookie("ro_info", json_encode($user_info), time() + 86400, "/");

                    header("Location: ropage.php");
                    exit();
                } else {
                    echo "User information not found.";
                    header("refresh:2; url=ROsignin.html");
                    exit();
                }
            } else {
                // Password is incorrect
                echo "Invalid password. Please try again.";
                header("refresh:2; url=ROsignin.html");
                exit();
            }
        } else {
            // Email not found in database
            echo "Email not found. Please check your email address.";
            header("refresh:2; url=ROsignin.html");
            exit();
        }
    } else {
        echo "Email or password is missing.";
        header("refresh:2; url=ROsignin.html");
        exit();
    }
}

$conn->close();
?>
