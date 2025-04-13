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
if (isset($_COOKIE['hr_info'])) {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_info'] = $_COOKIE['hr_info'];
    header("Location: hrpage.php");
    exit();
}


// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $conn->real_escape_string($_POST['password']);
        
        // Fetch hashed password from database
        $query = "SELECT PASSWORD FROM hrlogin WHERE EMAIL = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $stored_hashed_password = $row['PASSWORD'];

            // Verify password
            if (password_verify($password, $stored_hashed_password)) {
                // Password is correct, fetch additional user info
                $info_query = "SELECT  id,Name,email FROM hrinfo WHERE email = '$email'";
                $info_result = $conn->query($info_query);
                if ($info_result->num_rows == 1) {
                    $info_row = $info_result->fetch_assoc();
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_info'] = $info_row;
                    $_SESSION['admin_id'] = $info_row['id']; // Store admin ID in session


                    setcookie("hr_info", json_encode($info_row), time() + 86400, "/");


                    header("Location: hrpage.php");
                    exit();
                } else {
                    echo "User information not found.";
                    header("refresh:2; url=hrsignin.html");
                    exit();
                }
            } else {
                // Password is incorrect
                echo "Invalid password. Please try again.";
                header("refresh:2; url=hrsignin.html");
                exit();
            }
        } else {
            // Email not found in database
            echo "Email not found. Please check your email address.";
            header("refresh:2; url=hrsignin.html");
            exit();
        }
    } else {
        echo "Email or password is missing.";
        header("refresh:2; url=hrsignin.html");
        exit();
    }
}

$conn->close();
?>
