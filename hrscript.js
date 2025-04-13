document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded and parsed");

    async function fetchData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log(`Data fetched from ${url}:`, data);
            return data;
        } catch (error) {
            console.error(`Failed to fetch data from ${url}:`, error);
            return null;
        }
    }
    
    async function fetchLocations() {
        const data = await fetchData('fetch_locations.php');
        return data || [];
    }
    
    async function fetchDepartments(location = "") {
        const url = location ? `fetch_departments.php?location=${encodeURIComponent(location)}` : 'fetch_departments.php';
        const data = await fetchData(url);
        return data || [];
    }
    
    async function fetchReportingOfficers() {
        const data = await fetchData('fetch_reporting_officers.php');
        return data || [];
    }
    
    async function fetchPeriods() {
        const data = await fetchData('fetch_periods.php');
        return data || [];
    }
    
    function closeAllLists(input, elmnt) {
        const x = document.getElementsByClassName('autocomplete-items');
        for (let i = 0; i < x.length; i++) {
            if (elmnt !== x[i] && elmnt !== input) {
                if (x[i].parentNode) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }
    }
    
    function createDropdown(data, input) {
        const dropdownContainer = document.createElement('DIV');
        dropdownContainer.setAttribute('class', 'autocomplete-items');
        data.forEach(item => {
            const itemDiv = document.createElement('DIV');
            itemDiv.classList.add('item');
            itemDiv.innerHTML = item;
            itemDiv.addEventListener('click', function () {
                input.value = item;
                const event = new Event('input', { bubbles: true });
                input.dispatchEvent(event);
                closeAllLists(input);
            });
            dropdownContainer.appendChild(itemDiv);
        });
        return dropdownContainer;
    }
    
    function autocomplete(input, fetchFunction) {
        let currentFocus;
    
        input.addEventListener('input', async function () {
            const val = this.value;
            closeAllLists(input);
            if (!val) return false;
    
            currentFocus = -1;
            const data = await fetchFunction(val);
            const filteredData = data.filter(item => item.toLowerCase().includes(val.toLowerCase()));
            const dropdownContainer = createDropdown(filteredData, input);
            dropdownContainer.setAttribute('id', this.id + '-autocomplete-list');
            this.parentNode.appendChild(dropdownContainer);
        });
    
        input.addEventListener('keydown', function (e) {
            let x = document.getElementById(this.id + '-autocomplete-list');
            if (x) x = x.getElementsByTagName('div');
            if (e.keyCode === 40) {
                currentFocus++;
                addActive(x);
            } else if (e.keyCode === 38) {
                currentFocus--;
                addActive(x);
            } else if (e.keyCode === 13) {
                e.preventDefault();
                if (currentFocus > -1) {
                    if (x) x[currentFocus]?.click();
                }
            }
        });
    
        function addActive(x) {
            if (!x) return false;
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            x[currentFocus].classList.add('autocomplete-active');
        }
    
        function removeActive(x) {
            for (let i = 0; i < x.length; i++) {
                x[i].classList.remove('autocomplete-active');
            }
        }
    
        document.addEventListener('click', function (e) {
            closeAllLists(input, e.target);
        });
    }
    function attachSubmenuEventListeners() {
        document.querySelectorAll('.nav-option h3, .main-submenu h4').forEach(item => {
            item.addEventListener('click', function() {
                const contentId = this.getAttribute('data-content');
                toggleContent(contentId);
            });
        });
    }

    function attachProfileDropdownEventListeners() {
        document.querySelectorAll('.profile-dropdown h4').forEach(item => {
            item.addEventListener('click', function() {
                const contentId = this.getAttribute('data-content');
                toggleContent(contentId);
            });
        });
    }

    function toggleContent(contentId) {
        document.querySelectorAll('.page').forEach(page => {
            page.style.display = 'none';
        });
        const targetPage = document.getElementById(contentId + '-page');
        if (targetPage) {
            targetPage.style.display = 'block';
        }
    }

    async function showSpinner() {
        document.getElementById('spinner').style.display = 'flex';
    }

    async function hideSpinner() {
        document.getElementById('spinner').style.display = 'none';
    }

    async function attachSearchEventListener() {
    const searchButton = document.getElementById('search-btn');
    searchButton.addEventListener('click', async function() {
        showSpinner();

        const department = document.getElementById('department').value;
        const reportingOfficer = document.getElementById('reporting-officer').value;
        const period = document.getElementById('period').value;

        const searchData = {
            department: department,
            'reporting-officer': reportingOfficer,
            period: period
        };

        console.log('Searching with data:', searchData);

        try {
            const queryString = new URLSearchParams(searchData).toString();
            console.log(`fetch_results.php?${queryString}`);
            const response = await fetch(`fetch_results.php?${queryString}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const results = await response.json();
            console.log('Search results:', results);

            // Save the results and period to sessionStorage
            sessionStorage.setItem('searchResults', JSON.stringify(results));
            sessionStorage.setItem('period', period);

            populateTable(results, period);

        } catch (error) {
            console.error('Error fetching search results:', error);
        } finally {
            hideSpinner();
        }
    });
}

 // Attach event listener for Finalize All button

    const finalizeAllBtn = document.getElementById('finalize-all-btn');
    if (finalizeAllBtn) {
        finalizeAllBtn.addEventListener('click', function() {
            console.log('Finalize All button clicked');
            
            // Trigger click event on all finalize buttons
            const finalizeButtons = document.querySelectorAll('.finalize-btn');

            finalizeButtons.forEach(button => {
                if (!button.disabled) {
                    console.log(`Clicking finalize button for APPR_ID_NO: ${button.getAttribute('data-id')}`);
                    button.click();
                } else {
                    console.log(`Finalize button for APPR_ID_NO: ${button.getAttribute('data-id')} is already disabled`);
                }
            });
        });
    } else {
        console.error('Finalize All button not found');
    }

function populateTable(results, period) {
    const tableBody = document.querySelector('#results-table tbody');
    tableBody.innerHTML = '';

    if (results.length > 0) {
        results.forEach(result => {
            const finalizeButtonColor = result.finalize === 1 ? 'red' : '';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${result.NAME}</td>
                <td>${result.total_marks}</td>
                <td>
                    <button class="finalize-btn" data-id="${result.APPR_ID_NO}" data-period="${period}" style="background-color: ${finalizeButtonColor}" ${result.finalize === 1 ? 'disabled' : ''}>Finalize</button>
                    <button class="view-btn" data-id="${result.APPR_ID_NO}" data-period="${period}">View</button>
                </td>
            `;
            tableBody.appendChild(row);
        });

        // Add event listeners for the finalize buttons
        document.querySelectorAll('.finalize-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const apprIdNo = this.getAttribute('data-id');
                const period = this.getAttribute('data-period');
                console.log(`Finalize button clicked for APPR_ID_NO: ${apprIdNo}, Period: ${period}`);

                try {
                    const updateResponse = await fetch('update_finalize.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            appr_id_no: apprIdNo,
                            wd_period: period
                        })
                    });

                    console.log(`Sent request to update_finalize.php with APPR_ID_NO: ${apprIdNo} and Period: ${period}`);

                    if (!updateResponse.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const updateResult = await updateResponse.json();
                    console.log('Update response:', updateResult);

                    if (updateResult.success) {
                        this.style.backgroundColor = 'red';
                        this.disabled = true;
                        console.log(`Successfully finalized for APPR_ID_NO: ${apprIdNo}`);
                    } else {
                        console.error('Failed to update finalize status:', updateResult.message);
                    }

                } catch (error) {
                    console.error('Error updating finalize status:', error);
                }
            });
        });

        // Add event listeners for the view buttons
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const apprIdNo = this.getAttribute('data-id');
                const period = this.getAttribute('data-period');
                console.log(`View button clicked for APPR_ID_NO: ${apprIdNo}, Period: ${period}`);

                // Redirect to the view page with the necessary parameters
                const viewUrl = `view_page.php?apprentice_id=${apprIdNo}&period_id=${period}`;
                window.location.href = viewUrl;
            });
        });

    } else {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="3">No results found</td>`;
        tableBody.appendChild(row);
    }
}

attachSearchEventListener();

//end date automation to 1 yaer after//
document.getElementById('apprentice-start-date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDate = new Date(startDate);
    endDate.setFullYear(startDate.getFullYear() + 1);

    endDate.setDate(endDate.getDate() - 1);

    const formattedEndDate = endDate.toISOString().split('T')[0];
    document.getElementById('apprentice-end-date').value = formattedEndDate;
});


const form = document.getElementById('add-new-apprentice-page');

form.addEventListener('submit', async function(event) {
    event.preventDefault();
    console.log("Form submission triggered");

    showSpinner(); // Show the spinner

    // Check if all fields are filled
    let isValid = true;
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
        if (input.value === '') {
            isValid = false;
            alert('Please fill all fields');
            console.log("Validation failed: empty field detected");
            hideSpinner(); // Hide the spinner if validation fails
            return false;
        }
    });

    if (!isValid) {
        console.log("Form validation failed");
        hideSpinner(); // Hide the spinner if validation fails
        return;
    }

    // Retrieve values
    const reportingOfficer = document.getElementById('apprentice-reporting-officer').value;
    const department = document.getElementById('apprentice-department').value;
    const location = document.getElementById('apprentice-location').value;

    console.log("Validation passed, checking reporting officer, department, and location");
    console.log("Reporting Officer:", reportingOfficer);
    console.log("Department:", department);
    console.log("Location:", location);

    try {
        // AJAX call to validate reporting officer and department
        const validateResponse = await fetch('validate-officer-department.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reporting_officer: reportingOfficer, department: department, location: location })
        });

        if (!validateResponse.ok) {
            throw new Error('Network response was not ok');
        }

        const validateData = await validateResponse.json();
        console.log("Response from validation API:", validateData);

        if (!validateData.valid) {
            alert('Reporting officer selected is not in the department and location selected.');
            console.log("Validation failed: reporting officer not in department and location");
            hideSpinner(); // Hide the spinner if validation fails
            return;
        }

        // If valid, prepare apprentice data
        const apprenticeData = {
            startDate: document.getElementById('apprentice-start-date').value,
            endDate: document.getElementById('apprentice-end-date').value,
            apprenticeId: document.getElementById('apprentice-id').value,
            name: document.getElementById('apprentice-name').value,
            phone: document.getElementById('apprentice-phone').value,
            location: location,
            department: department,
            reportingOfficer: reportingOfficer,
            stream: document.getElementById('apprentice-stream').value,
            email: document.getElementById('apprentice-email').value,
            password: document.getElementById('apprentice-password').value
        };

        console.log("Apprentice data prepared for submission:", apprenticeData);

        // AJAX call to insert apprentice data
        const insertResponse = await fetch('insert-apprentice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(apprenticeData)
        });

        if (!insertResponse.ok) {
            throw new Error('Network response was not ok');
        }

        const insertData = await insertResponse.json();
        console.log("Response from insert API:", insertData);

        if (insertData.success) {
            alert('Apprentice added successfully');
            // Clear the form fields by setting their values to empty strings
            inputs.forEach(input => input.value = '');
            console.log("Apprentice added successfully and form cleared");
        } else {
            alert('Error adding apprentice');
            console.log("Error adding apprentice:", insertData);
        }

    } catch (error) {
        console.error('Error:', error);
    } finally {
        hideSpinner(); // Hide the spinner after processing
    }
});



document.getElementById('search-apprentice-btn').addEventListener('click', async function() {
    const apprenticeId = document.getElementById('search-apprentice-id').value;
    
    if (apprenticeId) {
        showSpinner();
        
        try {
            const response = await fetch(`fetch_to_modify_apprentice.php?id=${apprenticeId}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const apprenticeData = await response.json();
            
            console.log('Fetched apprentice data:', apprenticeData);
            
            if (apprenticeData) {
                document.getElementById('modify-apprentice-id').value = apprenticeData.APPR_ID_NO || '';
                document.getElementById('modify-apprentice-name').value = apprenticeData.NAME || '';
                document.getElementById('modify-apprentice-phone').value = apprenticeData.PHONE_NO || '';
                document.getElementById('modify-apprentice-location').value = apprenticeData.location || '';
                document.getElementById('modify-apprentice-department').value = apprenticeData.DEPARTMENT || '';
                document.getElementById('modify-apprentice-reporting-officer').value = apprenticeData.REPORTING_OFFICER_NAME || '';
                document.getElementById('modify-apprentice-stream').value = apprenticeData.STREAM || '';
                document.getElementById('modify-apprentice-email').value = apprenticeData.EMAIL || '';
		        document.getElementById('modify-start-date').value = apprenticeData.start_date || '';
		        document.getElementById('modify-end-date').value = apprenticeData.end_date || '';

                document.getElementById('apprentice-details-container').style.display = 'block';
            } else {
                alert('No apprentice found with the given ID.');
            }
        } catch (error) {
            console.error('Error fetching apprentice data:', error);
            alert('An error occurred while fetching the apprentice data.');
        } finally {
            hideSpinner();
        }
    } else {
        alert('Please enter a valid Apprentice ID.');
    }
});
document.getElementById('modify-apprentice-details-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        apprenticeId: document.getElementById('modify-apprentice-id').value,
        name: document.getElementById('modify-apprentice-name').value,
        phone: document.getElementById('modify-apprentice-phone').value,
        location: document.getElementById('modify-apprentice-location').value,
        department: document.getElementById('modify-apprentice-department').value,
        reportingOfficer: document.getElementById('modify-apprentice-reporting-officer').value,
        stream: document.getElementById('modify-apprentice-stream').value,
        email: document.getElementById('modify-apprentice-email').value,
        password: document.getElementById('modify-apprentice-password').value,
        start_date: document.getElementById('modify-start-date').value,  // Include start date
        end_date: document.getElementById('modify-end-date').value        // Include end date
    };

    // Check reporting officer and department
    const isOfficerValid = await checkReportingOfficer(formData.reportingOfficer, formData.department, formData.location);
    if (!isOfficerValid) {
        alert('The selected reporting officer is not in the selected department or location.');
        return; // Stop the form submission
    }
    
    try {
        const response = await fetch('update_apprentice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Details updated successfully');
            // Reset the form
            document.getElementById('modify-apprentice-details-form').reset();
            // Scroll to the top of the page
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            alert('Error updating details: ' + data.error);
        }
    } catch (error) {
        console.error('Error updating apprentice details:', error);
        alert('An error occurred while updating the apprentice details.');
    }
});
        

async function checkReportingOfficer(reportingOfficer, department, location) {
    try {
        const response = await fetch('validate-officer-department.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reporting_officer: reportingOfficer, department: department, location: location })
        });

        const data = await response.json();
        return data.valid;
    } catch (error) {
        console.error('Error validating reporting officer:', error);
        alert('An error occurred while validating the reporting officer.');
        return false;
    }
}



    // Function to toggle password visibility
    document.getElementById('show-password').addEventListener('change', function() {
        const passwordField = document.getElementById('modify-apprentice-password');
        if (this.checked) {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    });


    document.getElementById('search-delete-apprentice-btn').addEventListener('click', function() {
        var apprenticeId = document.getElementById('search-delete-apprentice-id').value;
    
        if (!apprenticeId) {
            alert('Please enter an Apprentice ID.');
            return;
        }
    
        fetch('fetch_to_delete_apprentice.php?id=' + apprenticeId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('delete-apprentice-id').value = data.data.APPR_ID_NO;
                    document.getElementById('delete-apprentice-name').value = data.data.NAME;
                    document.getElementById('delete-apprentice-phone').value = data.data.PHONE_NO;
                    document.getElementById('delete-apprentice-location').value = data.data.location; // Add this line
                    document.getElementById('delete-apprentice-department').value = data.data.DEPARTMENT;
                    document.getElementById('delete-apprentice-reporting-officer').value = data.data.REPORTING_OFFICER_NAME;
                    document.getElementById('delete-apprentice-stream').value = data.data.STREAM;
                    document.getElementById('delete-apprentice-email').value = data.data.EMAIL;
    
                    document.getElementById('delete-apprentice-details-container').style.display = 'block';
                } else {
                    alert(data.message);
                    document.getElementById('delete-apprentice-details-container').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching the apprentice details.');
                document.getElementById('delete-apprentice-details-container').style.display = 'none';
            });
    });
    
    document.getElementById('delete-apprentice-details-form').addEventListener('submit', function(e) {
        e.preventDefault();
    
        if (!confirm('Are you sure you want to delete this apprentice?')) {
            return;
        }
    
        var apprenticeId = document.getElementById('delete-apprentice-id').value;
    
        fetch('delete_apprentice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: apprenticeId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Apprentice deleted successfully');
                document.getElementById('delete-apprentice-details-container').style.display = 'none';
                document.getElementById('search-apprentice-form').reset();
            } else {
                alert('Error deleting apprentice: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the apprentice.');
        });
    });
    

    document.getElementById('add-new-reporting-officer-page').addEventListener('submit', function(e) {
        e.preventDefault();
    
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
    
        // Client-side validation
        const requiredFields = ['reporting-officer-name', 'reporting-officer-location', 'reporting-officer-department', 'reporting-officer-email', 'reporting-officer-password'];
        for (let field of requiredFields) {
            if (!data[field]) {
                alert(`Please fill out the ${field.replace('reporting-officer-', '').replace('-', ' ')} field.`);
                return; // Stop submission if any field is empty
            }
        }
    
        fetch('insert_reporting_officer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Reporting officer added successfully');
                e.target.reset(); // Clear the form
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the reporting officer.');
        });
    });
    
    
    
    document.getElementById('search-button-modify-officer').addEventListener('click', function() {
        var location = document.getElementById('location-search-modify-officer').value;
        var department = document.getElementById('department-search-modify-officer').value;
        console.log('Searching for officers in department:', department, 'and location:', location);
        fetch('search_officers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ department: department, location: location }) // Include location in the request
        })
        .then(response => response.json())
        .then(data => {
            console.log('Received response from search_officers.php:', data);
            if (data.success) {
                var tableBody = document.querySelector('#results-table-modify-officer tbody');
                tableBody.innerHTML = '';
                data.officers.forEach(officer => {
                    var row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${officer.Name}</td>
                        <td>${officer.location}</td>
                        <td>${officer.Department}</td>
                        <td><button type="button" class="select-button" data-id="${officer.id}">Select</button></td>
                    `;
                    tableBody.appendChild(row);
                });
                document.getElementById('results-table-modify-officer').style.display = 'table';
                document.querySelectorAll('.select-button').forEach(button => {
                    button.addEventListener('click', function() {
                        var officerId = this.getAttribute('data-id');
                        console.log('Selected officer ID:', officerId);
                        fetchOfficerDetails(officerId);
                    });
                });
            } else {
                console.error('No officers found.');
            }
        })
        .catch(error => {
            console.error('An error occurred while searching officers:', error.message);
        });
    });
    
    function fetchOfficerDetails(officerId) {
        console.log('Fetching details for officer ID:', officerId);
        fetch('get_officer_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: officerId })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Received response from get_officer_details.php:', data);
            if (data.success) {
                document.getElementById('officer-name-modify-officer').value = data.officer.Name;
                document.getElementById('officer-location-modify-officer').value = data.officer.location; // Populate Location field
                document.getElementById('officer-department-modify-officer').value = data.officer.Department;
                document.getElementById('officer-email-modify-officer').value = data.officer.Email;
                document.getElementById('officer-id-modify-officer').value = officerId; // Store the ID in a hidden field
                console.log('Set hidden field officer ID:', officerId);
                document.getElementById('officer-details-container').style.display = 'block';
            } else {
                console.error('Officer details not found.');
            }
        })
        .catch(error => {
            console.error('An error occurred while fetching officer details:', error.message);
        });
    }
    
    document.getElementById('details-form-modify-officer').addEventListener('submit', function(event) {
        event.preventDefault();
        var officerId = document.getElementById('officer-id-modify-officer').value;
        console.log('Updating officer ID:', officerId);
        var officerDetails = {
            id: officerId,
            name: document.getElementById('officer-name-modify-officer').value,
            location: document.getElementById('officer-location-modify-officer').value,
            department: document.getElementById('officer-department-modify-officer').value,
            email: document.getElementById('officer-email-modify-officer').value,
            password: document.getElementById('officer-password-modify-officer').value
        };
        console.log('Officer details to update:', officerDetails);
        fetch('update_officer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(officerDetails)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Received response from update_officer.php:', data);
            if (data.success) {
                alert('Officer details updated successfully.');
                // Clear all fields
                document.getElementById('details-form-modify-officer').reset();
                document.getElementById('officer-id-modify-officer').value = ''; // Clear hidden field
                // Hide the officer details container
                document.getElementById('officer-details-container').style.display = 'none';
                // Hide the results table
                document.getElementById('results-table-modify-officer').style.display = 'none';
                // Scroll to the top of the page
                window.scrollTo(0, 0);
            } else if (data.error === 'Email already exists.') {
                alert('The email address already exists. Please use a different email.');
            } else {
                console.error('Failed to update officer details:', data.error);
            }
        })
        .catch(error => {
            console.error('An error occurred while updating officer details:', error.message);
        });
    });
    
    

    document.getElementById('search-button-delete-officer').addEventListener('click', function() {
        const location = document.getElementById('location-search-delete-officer').value;
        const department = document.getElementById('department-search-delete-officer').value;
    
        fetch(`/officer_delete.php?location=${location}&department=${department}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const officers = data.officers;
                const tbody = document.getElementById('results-table-delete-officer').getElementsByTagName('tbody')[0];
                tbody.innerHTML = '';
                officers.forEach(officer => {
                    const row = document.createElement('tr');
                    const nameCell = document.createElement('td');
                    const locationCell = document.createElement('td');
                    const departmentCell = document.createElement('td');
                    const actionCell = document.createElement('td');
                    const deleteButton = document.createElement('button');
    
                    nameCell.textContent = officer.Name;
                    locationCell.textContent = officer.location;
                    departmentCell.textContent = officer.Department;
                    deleteButton.textContent = 'Delete';
                    deleteButton.classList.add('delete-button');
                    deleteButton.setAttribute('data-id', officer.id);
    
                    actionCell.appendChild(deleteButton);
                    row.appendChild(nameCell);
                    row.appendChild(locationCell);
                    row.appendChild(departmentCell);
                    row.appendChild(actionCell);
                    tbody.appendChild(row);
                });
                document.getElementById('results-table-delete-officer').style.display = 'table';
            })
            .catch(error => {
                alert('Failed to fetch officers: ' + error.message);
            });
    });
    
    document.getElementById('results-table-delete-officer').addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('delete-button')) {
            const officerId = event.target.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this officer?')) {
                fetch(`/officer_delete.php`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${officerId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Officer deleted successfully');
                        document.getElementById('search-button-delete-officer').click(); // Refresh the results
                    } else {
                        alert('Failed to delete officer: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Failed to delete officer: ' + error.message);
                });
            }
        }
    });
    
    function fetchAdminDetails() {
        fetch('fetch_admin_details.php')
            .then(response => {
                console.log('Response status:', response.status); // Log response status
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(adminData => {
                console.log('Admin data:', adminData); // Log the entire adminData object
    
                if (adminData.error) {
                    console.error('Server error:', adminData.error);
                    return;
                }
    
                if (adminData.name !== undefined && adminData.email !== undefined) {
                    document.getElementById('admin-name').value = adminData.name;
                    document.getElementById('admin-email').value = adminData.email;
                } else {
                    console.error('Admin name or email is undefined');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error); // Log any fetch errors
            });
    }
    
    document.getElementById('edit-admin-form').addEventListener('submit', function(e) {
        e.preventDefault();
    
        const name = document.getElementById('admin-name').value;
        const email = document.getElementById('admin-email').value;
        const password = document.getElementById('admin-password').value;
    
        const data = {
            name: name,
            email: email,
            password: password
        };
    
        console.log('Data being sent:', data);
    
        fetch('update_admin_details.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Admin details updated successfully');
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error updating admin details:', error);
            alert('An error occurred while updating admin details.');
        });
    });
    

    const addAdminForm = document.getElementById('add-admin-form');

    addAdminForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const name = document.getElementById('new-admin-name').value;
        const email = document.getElementById('new-admin-email').value;
        const password = document.getElementById('new-admin-password').value;

        const data = {
            'new-admin-name': name,
            'new-admin-email': email,
            'new-admin-password': password
        };

        fetch('add_admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Admin added successfully');
                // Optionally reset the form after successful submission
                addAdminForm.reset();
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error adding admin:', error);
            alert('An error occurred while adding admin.');
        });
    });

    $(document).ready(async function() {
        async function fetchData() {
            try {
                const marksResponse = await fetch('api/marks.php');
                if (!marksResponse.ok) throw new Error('Error fetching marks');
                const marks = await marksResponse.json();
                console.log('Marks:', marks);
    
                const periodsResponse = await fetch('api/periods.php');
                if (!periodsResponse.ok) throw new Error('Error fetching periods');
                const periods = await periodsResponse.json();
                console.log('Periods:', periods);
    
                const infoResponse = await fetch('api/info.php');
                if (!infoResponse.ok) throw new Error('Error fetching info');
                const info = await infoResponse.json();
                console.log('Info:', info);
    
                const data = marks.map(mark => {
                    const period = periods[mark.period_id];
                    const apprentice = info[mark.apprentice_id];
                    if (!period || !apprentice) {
                        console.error('Missing data for mark:', mark);
                        return null;
                    }
                    return {
                        location: apprentice.location,  
                        department: apprentice.department,
                        apprentice: apprentice.name,
                        period: period,
                        marks: (mark.total_marks != "" ? mark.total_marks: 0)
                    };
                }).filter(Boolean);
    
                return data;
            } catch (error) {
                //console.error(error);
                return [];
            }
        }
    
        const tableData = await fetchData();
    
        const exportTable = $('#exportTable');
        if (exportTable.length > 0) {
            if ($.fn.DataTable.isDataTable('#exportTable')) {
                exportTable.DataTable().destroy();
            }
    
            if (tableData.length === 0) {
                console.log('No data available');
            } else {
                exportTable.DataTable({
                    dom: 'Bfrtip',
                    data: tableData,
                    columns: [
                        { data: 'location' },  
                        { data: 'department' },
                        { data: 'apprentice' },
                        { data: 'period' },
                        { data: 'marks' }
                    ],
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ]
                });
            }
        } else {
            console.error('Table #exportTable not found');
        }
    
        $('#period').on('keyup change', function() {
            const table = $('#exportTable').DataTable();
            table.column(3).search(this.value).draw();
        });
    });


    $(document).ready(async function() {
        async function fetchData() {
            try {
                const marksResponse = await fetch('api/marks.php');
                if (!marksResponse.ok) throw new Error('Error fetching marks');
                const marks = await marksResponse.json();
                console.log('Marks:', marks);
    
                const periodsResponse = await fetch('api/periods.php');
                if (!periodsResponse.ok) throw new Error('Error fetching periods');
                const periods = await periodsResponse.json();
                console.log('Periods:', periods);
    
                const infoResponse = await fetch('api/info.php');
                if (!infoResponse.ok) throw new Error('Error fetching info');
                const info = await infoResponse.json();
                console.log('Info:', info);
    
                const data = marks.map(mark => {
                    const period = periods[mark.period_id];
                    const apprentice = info[mark.apprentice_id];
    
                    if (!period) {
                        console.error('Missing period for period_id:', mark.period_id);
                    }
    
                    if (!apprentice) {
                        console.error('Missing apprentice for apprentice_id:', mark.apprentice_id);
                    }
    
                    if (!period || !apprentice) {
                        return null;
                    }
    
                    return {
                        location: apprentice.location,
                        department: apprentice.department,
                        apprentice: apprentice.name,
                        period: period, // Include period in the data
                        action: `<button class="edit-btn action-button" data-id="${mark.apprentice_id}" data-period="${mark.period_id}">Edit</button>`
                    };
                }).filter(Boolean);
    
                return data;
            } catch (error) {
                console.error(error);
                return [];
            }
        }
    
        const tableData = await fetchData();
    
        const editTable = $('#editTable');
        if (editTable.length > 0) {
            if ($.fn.DataTable.isDataTable('#editTable')) {
                editTable.DataTable().destroy();
            }
    
            if (tableData.length === 0) {
                console.log('No data available');
            } else {
                editTable.DataTable({
                    data: tableData,
                    columns: [
                        { data: 'location' },
                        { data: 'department' },
                        { data: 'apprentice' },
                        { data: 'period' }, // New column for Period
                        { data: 'action', orderable: false } // Disable sorting for action column
                    ]
                });
    
                // Attach event listener for edit buttons
                $('#editTable').on('click', '.edit-btn', function() {
                    const apprenticeId = $(this).data('id');
                    const periodId = $(this).data('period');
                    console.log('Edit button clicked for apprentice ID:', apprenticeId, 'Period ID:', periodId);
    
                    // Confirmation prompt before allowing editing
                    if (confirm("Are you sure you want to edit this entry?")) {
                        // Redirect to the edit page with both apprenticeId and periodId as query parameters
                        window.location.href = `edit_work_diary.php?id=${apprenticeId}&period=${periodId}`;
                    }
                });
            }
        } else {
            console.error('Table #editTable not found');
        }
    });
    

    $(document).ready(function() {
        // Function to fetch data based on filters
        async function fetchApprenticeData(filters = {}) {
            try {
                // Build the query string from filters
                const queryString = new URLSearchParams(filters).toString();
                const response = await fetch(`fetch_allcurrentappr.php?${queryString}`);
                if (!response.ok) throw new Error('Error fetching apprentices');
                const apprentices = await response.json();
                console.log('Apprentices:', apprentices);
    
                // Process the apprentices data
                const data = apprentices.map(apprentice => ({
                    id: apprentice.id,
                    name: apprentice.name,
                    location: apprentice.location,
                    department: apprentice.department,
                }));
    
                return data;
            } catch (error) {
                console.error('Failed to fetch data:', error);
                return [];
            }
        }
    
        // Initialize DataTable
        function initializeDataTable(data) {
            const table = $('#apprenticeTable');
            if ($.fn.DataTable.isDataTable('#apprenticeTable')) {
                table.DataTable().destroy();
            }
    
            table.DataTable({
                dom: 'Bfrtip',
                data: data,
                columns: [
                    { data: 'name', title: 'Name' },
                    { data: 'id', title: 'ID' },
                    { data: 'location', title: 'Location' },
                    { data: 'department', title: 'Department' },
                ],
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        }
    
        // Handle search button click
        $('#search-btn').on('click', async function() {
            const filters = {
                location: $('#location').val(),
                department: $('#department').val(),
                reporting_officer: $('#reporting-officer').val(),
                period: $('#period').val()
            };
    
            const tableData = await fetchApprenticeData(filters);
            initializeDataTable(tableData);
        });
    
        // Fetch and display initial data
        fetchApprenticeData().then(data => initializeDataTable(data));
    });
    

    
    
    

    async function initializePage() {
        const locations = await fetchLocations();
        const reportingOfficers = await fetchReportingOfficers();
        const periods = await fetchPeriods();
    
        const locationInput = document.getElementById('location');
        const departmentInput = document.getElementById('department');
    
        if (locationInput) {
            autocomplete(locationInput, () => Promise.resolve(locations));
            locationInput.addEventListener('input', async () => {
                const location = locationInput.value;
                if (location) {
                    const departments = await fetchDepartments(location);
                    departmentInput.disabled = false;
                    autocomplete(departmentInput, () => Promise.resolve(departments));
                } else {
                    departmentInput.value = '';
                    departmentInput.disabled = true;
                    closeAllLists(departmentInput);
                }
            });
        }
    
        // Autocomplete for the new reporting officer form
        const reportingOfficerLocationInput = document.getElementById('reporting-officer-location');
        const reportingOfficerDepartmentInput = document.getElementById('reporting-officer-department');
    
        if (reportingOfficerLocationInput) {
            autocomplete(reportingOfficerLocationInput, () => Promise.resolve(locations));
            reportingOfficerLocationInput.addEventListener('input', async () => {
                const location = reportingOfficerLocationInput.value;
                if (location) {
                    const departments = await fetchDepartments(location);
                    reportingOfficerDepartmentInput.disabled = false;
                    autocomplete(reportingOfficerDepartmentInput, () => Promise.resolve(departments));
                } else {
                    reportingOfficerDepartmentInput.value = '';
                    reportingOfficerDepartmentInput.disabled = true;
                    closeAllLists(reportingOfficerDepartmentInput);
                }
            });
        }
    
        // Autocomplete for the modify reporting officer form
        const modifyOfficerLocationInput = document.getElementById('location-search-modify-officer');
        const modifyOfficerDepartmentInput = document.getElementById('department-search-modify-officer');
        const officerDetailsLocationInput = document.getElementById('officer-location-modify-officer');
        const officerDetailsDepartmentInput = document.getElementById('officer-department-modify-officer');
    
        if (modifyOfficerLocationInput) {
            autocomplete(modifyOfficerLocationInput, () => Promise.resolve(locations));
            modifyOfficerLocationInput.addEventListener('input', async () => {
                const location = modifyOfficerLocationInput.value;
                if (location) {
                    const departments = await fetchDepartments(location);
                    modifyOfficerDepartmentInput.disabled = false;
                    autocomplete(modifyOfficerDepartmentInput, () => Promise.resolve(departments));
                } else {
                    modifyOfficerDepartmentInput.value = '';
                    modifyOfficerDepartmentInput.disabled = true;
                    closeAllLists(modifyOfficerDepartmentInput);
                }
            });
        }
    
        if (officerDetailsLocationInput) {
            autocomplete(officerDetailsLocationInput, () => Promise.resolve(locations));
            officerDetailsLocationInput.addEventListener('input', async () => {
                const location = officerDetailsLocationInput.value;
                if (location) {
                    const departments = await fetchDepartments(location);
                    officerDetailsDepartmentInput.disabled = false;
                    autocomplete(officerDetailsDepartmentInput, () => Promise.resolve(departments));
                } else {
                    officerDetailsDepartmentInput.value = '';
                    officerDetailsDepartmentInput.disabled = true;
                    closeAllLists(officerDetailsDepartmentInput);
                }
            });
        }
    
        const deleteOfficerLocationInput = document.getElementById('location-search-delete-officer');
        const deleteOfficerDepartmentInput = document.getElementById('department-search-delete-officer');
    
        if (deleteOfficerLocationInput) {
            autocomplete(deleteOfficerLocationInput, () => Promise.resolve(locations));
            deleteOfficerLocationInput.addEventListener('input', async () => {
                const location = deleteOfficerLocationInput.value;
                if (location) {
                    const departments = await fetchDepartments(location);
                    deleteOfficerDepartmentInput.disabled = false;
                    autocomplete(deleteOfficerDepartmentInput, () => Promise.resolve(departments));
                } else {
                    deleteOfficerDepartmentInput.value = '';
                    deleteOfficerDepartmentInput.disabled = true;
                    closeAllLists(deleteOfficerDepartmentInput);
                }
            });
        }

        // Autocomplete for the new apprentice form
    const apprenticeLocationInput = document.getElementById('apprentice-location');
    const apprenticeDepartmentInput = document.getElementById('apprentice-department');

    if (apprenticeLocationInput) {
        autocomplete(apprenticeLocationInput, () => Promise.resolve(locations));
        apprenticeLocationInput.addEventListener('input', async () => {
            const location = apprenticeLocationInput.value;
            if (location) {
                const departments = await fetchDepartments(location);
                apprenticeDepartmentInput.disabled = false;
                autocomplete(apprenticeDepartmentInput, () => Promise.resolve(departments));
            } else {
                apprenticeDepartmentInput.value = '';
                apprenticeDepartmentInput.disabled = true;
                closeAllLists(apprenticeDepartmentInput);
            }
        });
    }       

    // Autocomplete for the modify apprentice form
    const modifyApprenticeLocationInput = document.getElementById('modify-apprentice-location');
    const modifyApprenticeDepartmentInput = document.getElementById('modify-apprentice-department');

    if (modifyApprenticeLocationInput) {
        autocomplete(modifyApprenticeLocationInput, () => Promise.resolve(locations));
        modifyApprenticeLocationInput.addEventListener('input', async () => {
            const location = modifyApprenticeLocationInput.value;
            if (location) {
                const departments = await fetchDepartments(location);
                modifyApprenticeDepartmentInput.disabled = false;
                autocomplete(modifyApprenticeDepartmentInput, () => Promise.resolve(departments));
            } else {
                modifyApprenticeDepartmentInput.value = '';
                modifyApprenticeDepartmentInput.disabled = true;
                closeAllLists(modifyApprenticeDepartmentInput);
            }
        });
    }
    
    
        autocomplete(document.getElementById('reporting-officer'), () => Promise.resolve(reportingOfficers));
        autocomplete(document.getElementById('period'), () => Promise.resolve(periods));
        autocomplete(document.getElementById('apprentice-department'), fetchDepartments);
        autocomplete(document.getElementById('apprentice-reporting-officer'), () => Promise.resolve(reportingOfficers));
        autocomplete(document.getElementById('modify-apprentice-department'), fetchDepartments);
        autocomplete(document.getElementById('modify-apprentice-reporting-officer'), () => Promise.resolve(reportingOfficers));
        autocomplete(document.getElementById('reporting-officer-department'), fetchDepartments);
        autocomplete(document.getElementById('department-search-modify-officer'), fetchDepartments);
        autocomplete(document.getElementById('officer-department-modify-officer'), fetchDepartments);
        autocomplete(document.getElementById('department-search-delete-officer'), fetchDepartments);
    
        attachSubmenuEventListeners();
        attachProfileDropdownEventListeners();
        attachSearchEventListener();
        fetchAdminDetails();
    
        document.querySelectorAll('.page').forEach(page => {
            page.style.display = 'none';
        });
        document.getElementById('search-page').style.display = 'block';
    
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
            });
        });
    }
    
    initializePage();

    
});