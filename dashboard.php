<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PUP Login System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #700000, #b44545);
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(45deg, #700000, #b44545);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header-content {   
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
        }

        .pup-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            transition: all 0.3s ease;
            position: absolute;
            left: -150px;
            top: 70%;
            transform: translateY(-50%);
        }

        .header h1 {
            font-size: 2.5em;
            margin: 0;
            margin: 0;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #700000;
        }

        .user-info h3 {
            color: #700000;
            margin-bottom: 10px;
        }

        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            min-width: 150px;
            text-align: center;
        }

        .btn-primary {
            background: #700000;
            color: white;
        }

        .btn-primary:hover {
            background: #8B0000;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .search-box {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            width: 300px;
        }

        .search-box:focus {
            outline: none;
            border-color: #700000;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #700000;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .loading {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #666;
            font-style: italic;
        }

        /* Responsive Design */
        /* Responsive Design */
        @media (max-width: 768px) {
            .pup-logo {
                position: static;
                transform: none;
                width: 40px;
                height: 40px;
                display: block;
                margin: 0 auto 10px auto;
            }

            .header-content {
                display: block;
            }

            .header h1 {
                font-size: 2em;
            }
            
            .controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }

            .btn {
                text-align: center;
            }

            th, td {
                padding: 10px 8px;
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .pup-logo {
                width: 30px;
                height: 30px;
            }

            .header h1 {
                font-size: 1.8em;
            }

            .header p {
                font-size: 1em;
            }
        }

        @media (max-width: 480px) {
            .pup-logo {
                width: 30px;
                height: 30px;
            }

            .header h1 {
                font-size: 1.8em;
            }

            .header p {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="header-content">
                <img src="img/PUPLogo.png" alt="PUP Logo" class="pup-logo" onerror="this.style.display='none'; console.log('Logo not found: pup-logo.png')">
                <h1>PUP Dashboard</h1>
            </div>
            <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</p>
        </div>
        
        <div class="content">
            <div class="user-info">
                <h3>Your Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <p><strong>Faculty ID:</strong> <?php echo htmlspecialchars($_SESSION['faculty_id']); ?></p>
            </div>

            <div class="controls">
                <button class="btn btn-primary" onclick="refreshTable()">Refresh Data</button>
                <button class="btn btn-success" onclick="exportToCSV()">Export to CSV</button>
                <button class="btn btn-success" onclick="window.location.href='course_selection.php'">Edit Grading Sheet</button>
                <button class="btn btn-success" onclick="window.location.href='add_students.php'">Manage Students</button>
                <button class="btn btn-success" onclick="window.location.href='reports.php'">Academic Reports</button>
                <button class="btn btn-danger" onclick="logout()">Logout</button>
                <input type="text" class="search-box" id="searchBox" placeholder="Search users..." onkeyup="searchTable()">
            </div>

            <div class="table-container">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Faculty ID</th>
                            <th>Registration Date</th>
                            <th>Last Login</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="6" class="loading">Loading users...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let usersData = [];

        // Load users on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });

        async function loadUsers() {
            try {
                const response = await fetch('users_api.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    usersData = data.users;
                    displayUsers(usersData);
                } else {
                    showError(data.message || 'Failed to load users');
                }
            } catch (error) {
                showError('Error loading users: ' + error.message);
            }
        }

        function displayUsers(users) {
            const tableBody = document.getElementById('tableBody');
            
            if (users.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="no-data">No users found</td></tr>';
                return;
            }

            const rows = users.map(user => `
                <tr>
                    <td>${user.id}</td>
                    <td>${escapeHtml(user.full_name)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${escapeHtml(user.faculty_id)}</td>
                    <td>${formatDate(user.created_at)}</td>
                    <td>${user.last_login ? formatDate(user.last_login) : 'Never'}</td>
                </tr>
            `).join('');

            tableBody.innerHTML = rows;
        }

        function searchTable() {
            const searchTerm = document.getElementById('searchBox').value.toLowerCase();
            
            if (searchTerm === '') {
                displayUsers(usersData);
                return;
            }

            const filteredUsers = usersData.filter(user => 
                user.full_name.toLowerCase().includes(searchTerm) ||
                user.email.toLowerCase().includes(searchTerm) ||
                user.faculty_id.toLowerCase().includes(searchTerm)
            );

            displayUsers(filteredUsers);
        }

        function refreshTable() {
            document.getElementById('tableBody').innerHTML = '<tr><td colspan="6" class="loading">Loading users...</td></tr>';
            loadUsers();
        }

        async function exportToCSV() {
            try {
                const response = await fetch('export_csv.php', {
                    method: 'GET'
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'pup_users_' + new Date().toISOString().split('T')[0] + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    alert('CSV file downloaded successfully!');
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                alert('Error exporting CSV: ' + error.message);
            }
        }

        async function logout() {
            if (confirm('Are you sure you want to logout?')) {
                try {
                    const response = await fetch('auth_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ action: 'logout' })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        window.location.href = 'index.html';
                    } else {
                        alert('Logout failed: ' + data.message);
                    }
                } catch (error) {
                    alert('Error during logout: ' + error.message);
                }
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showError(message) {
            document.getElementById('tableBody').innerHTML = 
                `<tr><td colspan="6" class="no-data" style="color: #dc3545;">Error: ${escapeHtml(message)}</td></tr>`;
        }
    </script>
</body>
</html>