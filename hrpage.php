<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    if (isset($_COOKIE['hr_info'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_info'] = json_decode($_COOKIE['hr_info'], true);
    } else {
        header("Location: hrsignin.html");
        exit();
    }
}

// Retrieve user information from session
$user_info = $_SESSION['user_info'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR/Admin Page</title>
    <link rel="stylesheet" href="hrpage.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.1.1/css/buttons.dataTables.min.css">
</head>
<style>
    .page {
        display: none;
    }

    .header h1 {
    margin: 0;
    font-size: 12px; /* Adjust font size */
    font-weight: bold;
}

    .header h2 {
    margin: 5px 0 0 0;
    font-size: 16px; /* Adjust font size */
    fou
}

    /* Logo Styling */
    .logo {
        display: flex;
        align-items: center;
    }

    .logo img {
        height: 60px; /* Adjust the height as needed */
        width: auto; /* Maintain aspect ratio */
    }

    .logo-text-container {
            flex: 1; /* Take up remaining space */
            display: flex;
            justify-content: center; /* Center text horizontally */
            align-items: center; /* Center text vertically */
            text-align: center;
        }

        .logo-text h2{
            font-family: Monospace;
            font-size: 25px;
            font-weight: 100;

        }

        .logo-text h1{
            font-family: Monospace;
            font-size: 20px;
            font-weight: bold;

        }


        .logo-text h1, .logo-text h2 {
            margin: 0; /* Remove default margins */
        }

        .finalize-all {
                float: right;
                margin-left: 10px;
            }

     /* Media query for smaller screens */
     @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: center;
            }

            .logo-text-container {
                margin-top: 10px;
            }

            .logout-button {
                margin-top: 10px;
            }

        }
</style>
<body>
    <!-- Header Section -->
    <header>
        <div class="logo">
            <img src="./Koch_Metro_Logo.png" alt="KMRL Logo">
        </div>
        <div class="logo-text-container">
            <div class="logo-text">
                <h1>HUMAN RESOURCES </h1>
                <h2>TRAINING & DEVELOPMENT DIVISION</h2>
            </div>
        </div>
        <div class="profile">
            <div class="dpicn">&#x1F935;</div>
            <div class="profile-dropdown">
                <h4 data-content="edit-admin">Edit Admin</h4>
                <h4 data-content="add-new-admin">Add New Admin</h4>
                <a href="hrlogout.php"><h4>Logout</h4></a>
            </div>
        </div>
    </header>
    
    <!-- Main Container -->
    <div class="main-container">
        <div class="navcontainer">
            <nav class="nav">
                <div class="nav-option">
                    <h3 data-content="search">Search</h3>
                </div>
                <div class="nav-option">
                    <h3 data-content="dashboard">Dashboard</h3>
                </div>
                <div class="nav-option">
                    <h3 data-content="add-new-apprentice">Apprentices</h3>
                    <div class="main-submenu">
                        <h4 data-content="add-new-apprentice">Add New</h4>
                        <h4 data-content="modify-apprentice">Modify</h4>
                        <h4 data-content="delete-apprentice">Delete</h4>
                    </div>
                </div>
                <div class="nav-option">
                    <h3 data-content="add-new-reporting-officer">Reporting Officer</h3>
                    <div class="main-submenu">
                        <h4 data-content="add-new-reporting-officer">Add New</h4>
                        <h4 data-content="modify-reporting-officer">Modify</h4>
                        <h4 data-content="delete-reporting-officer">Delete</h4>
                    </div>
                </div>
                <div class="nav-option">
                    <a href="hrlogout.php"><h3>Logout</h3></a>
                </div>
                <div class="nav-option">
                    <h3 data-content="export">Export</h3>
                </div>
                <div class="nav-option">
                    <h3 data-content="edit">Edit</h3>
                </div>

            </nav>
        </div>
        <div id="main" class="main">
<!-- Search Page Content -->
<div id="search-page" class="page">
        <div class="search-container">
            <div class="input-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" placeholder="Type location">
            </div>
            <div class="input-group">
                <label for="department">Department:</label>
                <input type="text" id="department" name="department" placeholder="Type department" disabled>
            </div>
            <div class="input-group">
                <label for="reporting-officer">Reporting Officer:</label>
                <input type="text" id="reporting-officer" name="reporting-officer" placeholder="Type reporting officer">
            </div>
            <div class="input-group">
                <label for="period">Period:</label>
                <input type="text" id="period" name="period" placeholder="Type period">
            </div>
            <button id="search-btn">Search</button>
        </div>
        
        <div class="results-container">
            <table id="results-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Marks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Search results will be populated here -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">
                            <!-- Finalize All button in the footer of the table -->
                            <button id="finalize-all-btn" class="finalize-all">Finalize All</button>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    
    <!-- Dashboard Page Content -->
<div id="dashboard-page" class="page">
    <h2>Apprentice Dashboard</h2>
    
    <!-- Results Table -->
    <div class="results-container">
        <table id="apprenticeTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Location</th>
                    <th>Department</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows will be populated dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.print.min.js"></script>


<!-- Export Page Content -->
<div id="export-page" class="page">
    <h2>Export</h2>
    <div class="filters">
        <!-- Add filters here if needed -->
    </div>
    <table id="exportTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Location</th>
                <th>Department</th>
                <th>Apprentice</th>
                <th>Period</th>
                <th>Marks</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be populated dynamically -->
        </tbody>
    </table>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.print.min.js"></script>



<!-- Edit Forms Page Content -->
<div id="edit-page" class="page">
    <h2>Edit Forms</h2>
    <div class="filters">
        <!-- Add filters here if needed -->
    </div>
    <table id="editTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Location</th>
                <th>Department</th>
                <th>Apprentice</th>
                <th>Period</th> <!-- New column for Period -->
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be populated dynamically -->
        </tbody>
    </table>
</div>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.print.min.js"></script>


<!-- Edit Admin Page -->
<div id="edit-admin-page" class="page">
    <h2>Edit Admin Details</h2>
    <form id="edit-admin-form">
        <div class="form-group">
            <label for="admin-name">Name:</label>
            <input type="text" id="admin-name" name="admin-name" placeholder="Enter name" required>
        </div>
        <div class="form-group">
            <label for="admin-email">Email:</label>
            <input type="email" id="admin-email" name="admin-email" placeholder="Enter email" required>
        </div>
        <div class="form-group">
            <label for="admin-password">Password:</label>
            <input type="password" id="admin-password" name="admin-password" placeholder="Enter new password">
        </div>
        <button type="submit">Update</button>
    </form>
</div>

<!--add new admin page-->
<div id="add-new-admin-page" class="page">
    <h2>Add New Admin</h2>
    <form id="add-admin-form">
        <div class="form-group">
            <label for="new-admin-name">Name:</label>
            <input type="text" id="new-admin-name" name="new-admin-name" placeholder="Enter name" required>
        </div>
        <div class="form-group">
            <label for="new-admin-email">Email:</label>
            <input type="email" id="new-admin-email" name="new-admin-email" placeholder="Enter email" required>
        </div>
        <div class="form-group">
            <label for="new-admin-password">Password:</label>
            <input type="password" id="new-admin-password" name="new-admin-password" placeholder="Enter password" required>
        </div>
        <button type="submit">Add Admin</button>
    </form>
</div>


<!-- Add New Apprentice Page -->
<div id="add-new-apprentice-page" class="page">
    <h2>Add New Apprentice</h2>
    <form>
        <div class="form-row">
            <div class="form-group">
                <label for="apprentice-start-date">Start Date:</label>
                <input type="date" id="apprentice-start-date" name="apprentice-start-date">
            </div>
            <div class="form-group">
                <label for="apprentice-end-date">End Date:</label>
                <input type="date" id="apprentice-end-date" name="apprentice-end-date">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="apprentice-id">Apprentice ID:</label>
                <input type="text" id="apprentice-id" name="apprentice-id" placeholder="Enter apprentice ID">
            </div>
            <div class="form-group">
                <label for="apprentice-name">Name:</label>
                <input type="text" id="apprentice-name" name="apprentice-name" placeholder="Enter name">
            </div>
            <div class="form-group">
                <label for="apprentice-phone">Phone Number:</label>
                <input type="text" id="apprentice-phone" name="apprentice-phone" placeholder="Enter phone number">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="apprentice-location">Location:</label>
                <input type="text" id="apprentice-location" name="apprentice-location" placeholder="Enter location">
            </div>
            <div class="form-group">
                <label for="apprentice-department">Department:</label>
                <input type="text" id="apprentice-department" name="apprentice-department" placeholder="Enter department" disabled>
            </div>
            <div class="form-group">
                <label for="apprentice-reporting-officer">Reporting Officer:</label>
                <input type="text" id="apprentice-reporting-officer" name="apprentice-reporting-officer" placeholder="Enter reporting officer">
            </div>
            <div class="form-group">
                <label for="apprentice-stream">Stream:</label>
                <input type="text" id="apprentice-stream" name="apprentice-stream" placeholder="Enter stream">
            </div>
        </div>
        <div class="login-info">
            <h3>Login Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="apprentice-email">APPR ID:</label>
                    <input type="text" id="apprentice-email" name="apprentice-email" placeholder="Enter APPR ID">
                </div>
                <div class="form-group">
                    <label for="apprentice-password">Password:</label>
                    <input type="password" id="apprentice-password" name="apprentice-password" placeholder="Enter password">
                </div>
            </div>
        </div>
        <button type="submit">Submit</button>
    </form>
</div>


 <!-- Modify Apprentice Page -->
<div id="modify-apprentice-page" class="page" style="display: none;">
    <h2>Modify Apprentice</h2>
    <form id="search-apprentice-form">
        <div class="form-group">
            <label for="search-apprentice-id">Apprentice ID:</label>
            <input type="text" id="search-apprentice-id" name="search-apprentice-id" placeholder="Enter Apprentice ID">
            <button type="button" id="search-apprentice-btn">Search</button>
        </div>
    </form>
    <div id="apprentice-details-container" style="display: none;">
        <form id="modify-apprentice-details-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="modify-apprentice-id">Apprentice ID:</label>
                    <input type="text" id="modify-apprentice-id" name="modify-apprentice-id">
                </div>
                <div class="form-group">
                    <label for="modify-apprentice-name">Name:</label>
                    <input type="text" id="modify-apprentice-name" name="modify-apprentice-name">
                </div>
                <div class="form-group">
                    <label for="modify-apprentice-phone">Phone Number:</label>
                    <input type="text" id="modify-apprentice-phone" name="modify-apprentice-phone">
                </div>
 		<div class="form-group">
                	<label for="modify-start-date">Start Date:</label>
                	<input type="text" id="modify-start-date" name="modify-start-date" >
            	</div>
	    	<div class="form-group">
                	<label for="modify-end-date">End Date:</label>
                	<input type="text" id="modify-end-date" name="modify-end-date">
            	</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="modify-apprentice-location">Location:</label>
                    <input type="text" id="modify-apprentice-location" name="modify-apprentice-location">
                </div>
                <div class="form-group">
                    <label for="modify-apprentice-department">Department:</label>
                    <input type="text" id="modify-apprentice-department" name="modify-apprentice-department" disabled>
                </div>
                <div class="form-group">
                    <label for="modify-apprentice-reporting-officer">Reporting Officer:</label>
                    <input type="text" id="modify-apprentice-reporting-officer" name="modify-apprentice-reporting-officer">
                </div>
                <div class="form-group">
                    <label for="modify-apprentice-stream">Stream:</label>
                    <input type="text" id="modify-apprentice-stream" name="modify-apprentice-stream">
                </div>
            </div>
            <h3>Login Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="modify-apprentice-email">APPR ID:</label>
                    <input type="text" id="modify-apprentice-email" name="modify-apprentice-email">
                </div>
                <div class="form-group">
                    <label for="modify-apprentice-password">Password:</label>
                    <input type="password" id="modify-apprentice-password" name="modify-apprentice-password" placeholder="Enter new password">
                    <input type="checkbox" id="show-password"> Show Password
                </div>
            </div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>


<!-- Delete Apprentice Page -->
<div id="delete-apprentice-page" class="page" style="display: none;">
    <h2>Delete Apprentice</h2>
    <form id="search-apprentice-form">
        <div class="form-group">
            <label for="search-delete-apprentice-id">Apprentice ID:</label>
            <input type="text" id="search-delete-apprentice-id" name="search-delete-apprentice-id" placeholder="Enter Apprentice ID">
            <button type="button" id="search-delete-apprentice-btn">Search</button>
        </div>
    </form>
    <div id="delete-apprentice-details-container" style="display: none;">
        <form id="delete-apprentice-details-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="delete-apprentice-id">Apprentice ID:</label>
                    <input type="text" id="delete-apprentice-id" name="delete-apprentice-id" readonly>
                </div>
                <div class="form-group">
                    <label for="delete-apprentice-name">Name:</label>
                    <input type="text" id="delete-apprentice-name" name="delete-apprentice-name" readonly>
                </div>
                <div class="form-group">
                    <label for="delete-apprentice-phone">Phone Number:</label>
                    <input type="text" id="delete-apprentice-phone" name="delete-apprentice-phone" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="delete-apprentice-location">Location:</label>
                    <input type="text" id="delete-apprentice-location" name="delete-apprentice-location" readonly>
                </div>
                <div class="form-group">
                    <label for="delete-apprentice-department">Department:</label>
                    <input type="text" id="delete-apprentice-department" name="delete-apprentice-department" readonly>
                </div>
                <div class="form-group">
                    <label for="delete-apprentice-reporting-officer">Reporting Officer:</label>
                    <input type="text" id="delete-apprentice-reporting-officer" name="delete-apprentice-reporting-officer" readonly>
                </div>
                <div class="form-group">
                    <label for="delete-apprentice-stream">Stream:</label>
                    <input type="text" id="delete-apprentice-stream" name="delete-apprentice-stream" readonly>
                </div>
            </div>
            <h3>Login Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="delete-apprentice-email">APPR ID:</label>
                    <input type="text" id="delete-apprentice-email" name="delete-apprentice-email" readonly>
                </div>
            </div>
            <button type="submit">Delete</button>
        </form>
    </div>
</div>


<!-- Add New Reporting Officer Page -->
<div id="add-new-reporting-officer-page" class="page" style="display: none;">
    <h2>Add New Reporting Officer</h2>
    <form>
        <div class="form-row">
            <div class="form-group">
                <label for="reporting-officer-name">Name:</label>
                <input type="text" id="reporting-officer-name" name="reporting-officer-name" placeholder="Enter name">
            </div>
            <div class="form-group">
                <label for="reporting-officer-location">Location:</label>
                <input type="text" id="reporting-officer-location" name="reporting-officer-location" placeholder="Enter location">
            </div>
            <div class="form-group">
                <label for="reporting-officer-department">Department:</label>
                <input type="text" id="reporting-officer-department" name="reporting-officer-department" placeholder="Enter department" disabled>
            </div>
        </div>
        <div class="login-info">
            <h3>Login Details</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="reporting-officer-email">Email:</label>
                    <input type="email" id="reporting-officer-email" name="reporting-officer-email" placeholder="Enter email">
                </div>
                <div class="form-group">
                    <label for="reporting-officer-password">Password:</label>
                    <input type="password" id="reporting-officer-password" name="reporting-officer-password" placeholder="Enter password">
                </div>
            </div>
        </div>
        <button type="submit">Submit</button>
    </form>
</div>



<!-- modify reporting officer -->
<div id="modify-reporting-officer-page" class="page" style="display: none;">
    <h2>Modify Reporting Officer</h2>
    <form id="search-form-modify-officer" class="form-group">
        <div class="form-row">
            <div class="form-group">
                <label for="location-search-modify-officer">Location:</label>
                <input type="text" id="location-search-modify-officer" name="location-search" placeholder="Enter location">
            </div>
            <div class="form-group">
                <label for="department-search-modify-officer">Department:</label>
                <input type="text" id="department-search-modify-officer" name="department-search" placeholder="Enter department" disabled>
            </div>
        </div>
        <div class="form-row form-row-right">
            <button type="button" class="search-button" id="search-button-modify-officer">Search</button>
        </div>
    </form>
    <table id="results-table-modify-officer" style="display:none; width: 100%; margin-top: 20px;">
        <thead>
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Department</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be added dynamically -->
        </tbody>
    </table>
    <div id="officer-details-container" style="display:none;">
        <form id="details-form-modify-officer">
            <div class="form-row">
                <input type="hidden" id="officer-id-modify-officer">
                <div class="form-group">
                    <label for="officer-name-modify-officer">Name:</label>
                    <input type="text" id="officer-name-modify-officer" name="officer-name" placeholder="Enter name">
                </div>
                <div class="form-group">
                    <label for="officer-location-modify-officer">Location:</label>
                    <input type="text" id="officer-location-modify-officer" name="officer-location" placeholder="Enter location">
                </div>
                <div class="form-group">
                    <label for="officer-department-modify-officer">Department:</label>
                    <input type="text" id="officer-department-modify-officer" name="officer-department" placeholder="Enter department" disabled>
                </div>
            </div>
            <div class="login-info">
                <h3>Login Info</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="officer-email-modify-officer">Email:</label>
                        <input type="email" id="officer-email-modify-officer" name="officer-email" placeholder="Enter email">
                    </div>
                    <div class="form-group">
                        <label for="officer-password-modify-officer">Password:</label>
                        <input type="text" id="officer-password-modify-officer" name="officer-password" placeholder="Enter new password">
                    </div>
                </div>
            </div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>



 <!-- Delete Reporting Officer Page -->
<div id="delete-reporting-officer-page" class="page">
    <h2>Delete Reporting Officer</h2>
    <form id="search-form-delete-officer" class="form-group">
        <div class="form-row">
            <div class="form-group">
                <label for="location-search-delete-officer">Location:</label>
                <input type="text" id="location-search-delete-officer" name="location-search" placeholder="Enter location">
            </div>
            <div class="form-group">
                <label for="department-search-delete-officer">Department:</label>
                <input type="text" id="department-search-delete-officer" name="department-search" placeholder="Enter department" disabled>
            </div>
        </div>
        <div class="form-row form-row-right">
            <button type="button" id="search-button-delete-officer">Search</button>
        </div>
    </form>
    <table id="results-table-delete-officer" style="display:none; width: 100%; margin-top: 20px;">
        <thead>
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Department</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be added dynamically -->
        </tbody>
    </table>
</div>



        </div>
    </div>

    
<script src="hrscript.js"></script>

<div id="spinner" class="spinner-container" style="display: none;">
<div class="spinner"></div>
</div>
</body>
</html>