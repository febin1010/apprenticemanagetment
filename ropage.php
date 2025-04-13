<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    if (isset($_COOKIE['ro_info'])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_info'] = json_decode($_COOKIE['ro_info'], true);
    } else {
        header("Location: ROsignin.html");
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
    <link rel="stylesheet" href="ropage.css">
    <title>Apprentices Work Diary</title>
</head>
<style>
    .logo {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .logo img {
        height: 60px;
        width: auto;
        margin-right: 10px;
    }

    .logo-text-container {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .logo-text h1, .logo-text h2 {
        margin: 0;
    }

    .logo-text h2 {
        font-family: Monospace;
        font-size: 25px;
        font-weight: 100;
    }

    .logo-text h1 {
        font-family: Monospace;
        font-size: 20px;
        font-weight: bold;
    }

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

    .header-buttons {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .header-buttons form,
    .header-buttons button {
        margin-top: 10px;
        background-color: #5c6bc0; /* Green background */
        color: white; /* White text */
        border: none; /* Remove border */
        cursor: pointer; /* Pointer cursor on hover */
    }


    .header-buttons form:hover,
    .header-buttons button:hover {
        background-color: #45a049; /* Darker green on hover */
    }


        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
        }

        .input-group input {
            width: calc(100% - 40px);
            padding: 10px;
            margin-right: 5px;
        }

        .input-group .toggle-password {
            position: absolute;
            margin-left: -30px;
            cursor: pointer;
        }

        .modal-content button {
            padding: 10px 20px;
            background-color: #5c6bc0; /* Green background */
            color: white; /* White text */
            border: none; /* Remove border */
            cursor: pointer; /* Pointer cursor on hover */
        }

        .modal-content button:hover {
            background-color: #5c6bc0; /* Darker green on hover */
        }
</style>
<body>
<div class="header">
    <div class="logo">
        <img src="./Koch_Metro_Logo.png" alt="KMRL Logo">
    </div>
    <div class="logo-text-container">
        <div class="logo-text">
            <h1>HUMAN RESOURCES</h1>
            <h2>TRAINING & DEVELOPMENT DIVISION</h2>
        </div>
    </div>
    <div class="header-buttons">
    <form action="rologout.php" method="post" class="logout-button">
        <input type="submit" value="Logout">
    </form>
    <button class="change-password-button" id="changePasswordBtn">Change Password</button>
</div>
</div>

<!-- The Modal -->
<div id="changePasswordModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Change Password</h2>
        <form id="changePasswordForm">
            <div class="input-group">
                <label for="current-password">Current Password:</label>
                <input type="password" id="current-password" name="current-password" required>
                <span class="toggle-password" onclick="togglePassword('current-password')">👁️</span>
            </div>
            <div class="input-group">
                <label for="new-password">New Password:</label>
                <input type="password" id="new-password" name="new-password" required>
                <span class="toggle-password" onclick="togglePassword('new-password')">👁️</span>
            </div>
            <div class="input-group">
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
                <span class="toggle-password" onclick="togglePassword('confirm-password')">👁️</span>
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</div>

<div class="form-container">
    <div class="user-info">
        <h2>Welcome, <?php echo htmlspecialchars($user_info[0]['Name']); ?></h2>
    </div>

    <div class="period-selector">
        <button id="prev-period-button">Previous Period</button>
        <span id="period-label"></span>
        <button id="next-period-button">Next Period</button>
    </div>

    <div class="apprentices-tables">
        <div class="apprentices-table submitted-apprentices">
            <h3>Apprentices Who Submitted</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Marks</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="submitted-apprentices-table-body">
                    <!-- Rows will be generated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div class="apprentices-table not-submitted-apprentices">
            <h3>Apprentices Who Did Not Submit</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Marks</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="not-submitted-apprentices-table-body">
                    <!-- Rows will be generated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <div id="loading-spinner" class="spinner" style="display: none;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submittedTableBody = document.getElementById('submitted-apprentices-table-body');
    const notSubmittedTableBody = document.getElementById('not-submitted-apprentices-table-body');
    const prevPeriodButton = document.getElementById('prev-period-button');
    const nextPeriodButton = document.getElementById('next-period-button');
    const periodLabel = document.getElementById('period-label');
    const loadingSpinner = document.getElementById('loading-spinner');
    const modal = document.getElementById('changePasswordModal');
    const btn = document.getElementById('changePasswordBtn');
    const span = document.getElementsByClassName('close')[0];
    const form = document.getElementById('changePasswordForm');

    btn.onclick = function() {
        modal.style.display = 'block';
    }

    span.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    form.addEventListener('submit', function(event) {
    event.preventDefault();
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (newPassword !== confirmPassword) {
        alert('New password and confirm password do not match.');
        return;
    }

    // Perform AJAX request to change_password.php
    fetch('rochange_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            currentPassword: currentPassword,
            newPassword: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password changed successfully.');
            // Clear the input fields
            document.getElementById('current-password').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
            modal.style.display = 'none';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});


    let currentDate = new Date();
    let startDate = getStartDate(currentDate);
    let endDate = getEndDate(startDate);
    let periodId = null;

    function getStartDate(date) {
        let month = date.getMonth();
        let year = date.getFullYear();
        if (date.getDate() < 21) {
            month--;
            if (month < 0) {
                month = 11;
                year--;
            }
        }
        return new Date(year, month, 21);
    }

    function getEndDate(startDate) {
        let endMonth = startDate.getMonth() + 1;
        let endYear = startDate.getFullYear();
        if (endMonth > 11) {
            endMonth = 0;
            endYear++;
        }
        return new Date(endYear, endMonth, 20);
    }

    function formatPeriodLabel(startDate, endDate) {
        const startOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        const endOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        return `${startDate.toLocaleDateString(undefined, startOptions)} - ${endDate.toLocaleDateString(undefined, endOptions)}`;
    }

    function fetchApprentices(startDate, endDate) {
    loadingSpinner.style.display = 'block'; // Show the spinner

    // Retrieve departments and locations from user info
    const userDepartments = <?php echo json_encode(array_map(function($info) { return $info['Department']; }, $user_info)); ?>;
    const userLocations = <?php echo json_encode(array_map(function($info) { return $info['Location']; }, $user_info)); ?>;

    // Encode the parameters
    const departmentsParam = encodeURIComponent(JSON.stringify(userDepartments));
    const locationsParam = encodeURIComponent(JSON.stringify(userLocations));
    const startDateParam = encodeURIComponent(startDate.toISOString().split('T')[0]);
    const endDateParam = encodeURIComponent(endDate.toISOString().split('T')[0]);

    const fetchUrl = `fetch_apprentices.php?start_date=${startDateParam}&end_date=${endDateParam}&departments=${departmentsParam}&locations=${locationsParam}`;

    console.log('Fetch URL:', fetchUrl); // Debugging: Log the fetch URL

    fetch(fetchUrl)
        .then(response => response.json())
        .then(data => {
            loadingSpinner.style.display = 'none'; // Hide the spinner
            console.log('Fetched apprentices data:', data);
            if (data.error) {
                console.error('Error:', data.error);
                return;
            } 

            periodId = data.period_id;
            submittedTableBody.innerHTML = '';
            notSubmittedTableBody.innerHTML = '';

            data.apprentices.forEach(apprentice => {
            const row = document.createElement('tr');
            row.innerHTML = `
                    <td>${apprentice.name}</td>
                    <td>${apprentice.total_marks}</td>
                    <td>${apprentice.submitted}</td>
                    <td>
                        <button class="score-button" data-id="${apprentice.id}">Evaluate</button>
                        <button class="reject-button" data-id="${apprentice.id}">Reject</button>
                    </td>
                    `;
                if (apprentice.submitted === 'Yes') {
                    submittedTableBody.appendChild(row);
                } else {
                    notSubmittedTableBody.appendChild(row);
                }
            });

            document.querySelectorAll('.score-button').forEach(button => {
                button.addEventListener('click', function() {
                    const apprenticeId = this.getAttribute('data-id');
                    console.log('Score button clicked, apprentice ID:', apprenticeId);

                    // Add one day to startDate and endDate
                    const adjustedStartDate = new Date(startDate);
                    adjustedStartDate.setDate(adjustedStartDate.getDate() + 1);

                    const adjustedEndDate = new Date(endDate);
                    adjustedEndDate.setDate(adjustedEndDate.getDate() + 1);

                    const startDateStr = adjustedStartDate.toISOString().split('T')[0];
                    const endDateStr = adjustedEndDate.toISOString().split('T')[0];

                    window.location.href = `work_diary.php?apprentice_id=${apprenticeId}&period_id=${periodId}&start_date=${startDateStr}&end_date=${endDateStr}`;
                });
            });

            document.querySelectorAll('.reject-button').forEach(button => {
            button.addEventListener('click', function() {
                const apprenticeId = this.getAttribute('data-id');
                console.log('Reject button clicked, apprentice ID:', apprenticeId);

                // Ask for confirmation before proceeding
                const confirmReject = confirm('Are you sure you want to reject this apprentice\'s entries for this period?');

                if (confirmReject) {
                    // Proceed with the rejection if confirmed
                    fetch('reject_apprentice.php', {
                        method: 'POST',
                        headers:{
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            apprentice_id: apprenticeId,
                            period_id: periodId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Apprentice rejected successfully.');
                            window.location.reload(); 
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                } else {
                    console.log('Rejection cancelled.');
                }
            });
        });


        })
        .catch(error => {
            loadingSpinner.style.display = 'none'; // Hide the spinner
            console.error('Error fetching apprentices data:', error);
        });
}


    function updatePeriod() {
        periodLabel.textContent = formatPeriodLabel(startDate, endDate);
        fetchApprentices(startDate, endDate);
    }

    prevPeriodButton.addEventListener('click', function() {
        endDate = new Date(startDate);
        endDate.setDate(20);
        startDate.setMonth(startDate.getMonth() - 1);
        startDate.setDate(21);
        updatePeriod();
    });

    nextPeriodButton.addEventListener('click', function() {
        startDate = new Date(endDate);
        startDate.setDate(21);
        endDate.setMonth(endDate.getMonth() + 1);
        endDate.setDate(20);
        updatePeriod();
    });

    updatePeriod();
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>
</body>
</html>
