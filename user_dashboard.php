<?php
session_start();
require_once 'db.php'; // Make sure to include your DB connection file

// TEMPORARY: Set dummy user session if not already logged in (for testing)
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'registration_number' => 'REG123456',
        'name' => 'John Doe',
        'faculty' => 'Engineering',
        'course' => 'Computer Science',
        'email' => 'johndoe@example.com'
    ];
}

// Fetch username from session
$username = $_SESSION['user']['name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            height: 100vh;
            margin: 0;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding: 15px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4>Dashboard</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="user_dashboard.php" class="nav-link text-white">Home</a>
        </li>
        <li class="nav-item">
            <a href="face_recognition_dashboard.php" class="nav-link text-white">Face Recognition</a>
        </li>
        <li class="nav-item">
            <a href="logout.php" class="nav-link text-white">Logout</a>
        </li>
    </ul>
</div>

<!-- Main Content -->
<div class="flex-grow-1">
    <!-- Topbar -->
    <div class="topbar">
        <input type="text" class="form-control w-50" placeholder="Search ......">
        <div>
            <span class="badge bg-light text-dark">@ <?php echo htmlspecialchars($username); ?></span>
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="User" width="40" height="40" class="rounded-circle ms-2">
        </div>
    </div>

    <!-- Content -->
    <div class="content">
    <h3>User Records</h3>
<?php
if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Database connection not found.</div>";
} else {
    $result = $conn->query("SELECT * FROM attendance ORDER BY created_at DESC");

    if ($result && $result->num_rows > 0) {
        echo "<table class='table table-striped'>";
        echo "<thead>
                <tr>
                    <th>Registration No.</th>
                    <th>Name</th>
                    <th>Faculty</th>
                    <th>Course</th>
                    <th>Email</th>
                    <th>Time Created</th>
                </tr>
              </thead>
              <tbody>";

        while ($row = $result->fetch_assoc()) {
            $fullName = htmlspecialchars($row['name']);
            echo "<tr>
                    <td>" . htmlspecialchars($row['registration_number']) . "</td>
                    <td>$fullName</td>
                    <td>" . htmlspecialchars($row['faculty']) . "</td>
                    <td>" . htmlspecialchars($row['course']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['created_at']) . "</td>
                  </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p>No records found.</p>";
    }
}
?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
