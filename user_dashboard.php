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
  
        .content {
            padding: 20px;
        }


        .sidebar {
      height: 100vh;
      background: #272343;
      padding-top: 20px;
      position: relative;
    }
    .sidebar a {
      display: block;
      padding: 10px 20px;
      color: #ffffff;
      text-decoration: none;
      font-weight: 500;
    }
    .sidebar a:hover {
      background:rgb(91, 79, 165);
      border-radius: 10px;
    }
    .sidebar .active {
      background:rgb(91, 79, 165);
      color: white;
      border-radius: 10px;
    }
    .settings {
      position: absolute;
      bottom: 20px;
      width: 80%;
      border-radius: 10px;
    }
    .settings a{
      color: #ffffff !important;
    }
    .sidebar h4 {
      color: #ffffff !important;
    }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #ffffff;
            border-bottom: solid #bae8e8 1px;
        }

    </style>
</head>
<body>

<!-- Sidebar -->
 

<div class="sidebar p-3">
    <h4 class="mb-4 text-dark">Dashboard</h4>
    <a href="user_dashboard.php" class="active">Home</a>
    <a href="face_recognition_dashboard.php">Face Recognition</a>
    <div class="settings">
      <a href="logout.php" class="text-danger">Logout</a>
    </div>
  </div>


<!-- Main Content -->
<div class="flex-grow-1">
    <!-- Topbar -->
    <div class="topbar d-flex justify-content-end">
        
        <div>
            <span class="badge bg-light text-dark">@ <?php echo htmlspecialchars($username); ?></span>
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="User" width="40" height="40" class="rounded-circle ms-2">
        </div>
    </div>

    <!-- Content -->
    <div class="content container ">
    <h3>User Records</h3>

    <form method="get" class="mb-3">
<div class="d-flex">
<input type="text" name="search" class="form-control w-50 mx-2" placeholder="Search..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
<button type="submit" class="btn btn-outline-success">Search</button>
</div>
</form>

<?php
if (!isset($conn)) {
    echo "<div class='alert alert-danger'>Database connection not found.</div>";
} else {
$search = $_GET['search'] ?? '';
$searchSafe = $conn->real_escape_string($search);

if (!empty($searchSafe)) {
    $sql = "SELECT * FROM attendance 
            WHERE registration_number LIKE '%$searchSafe%' 
               OR name LIKE '%$searchSafe%'
               OR faculty LIKE '%$searchSafe%'
               OR course LIKE '%$searchSafe%'
               OR email LIKE '%$searchSafe%'
            ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM attendance ORDER BY created_at DESC";
}

$result = $conn->query($sql);


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
