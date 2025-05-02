<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}
$username = $_SESSION['user']['name']; // Get the admin's name

// Connect to the database
$conn = new mysqli("localhost", "root", "", "attdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Poppins', sans-serif;
    }
    .sidebar {
      height: 100vh;
      background: #ffffff;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      padding-top: 20px;
      position: relative;
    }
    .sidebar a {
      display: block;
      padding: 10px 20px;
      color: #333;
      text-decoration: none;
      font-weight: 500;
    }
    .sidebar a:hover {
      background: #e9ecef;
    }
    .sidebar .active {
      background: #6f42c1;
      color: white;
      border-radius: 10px;
    }
    .topbar {
      height: 60px;
      background: #fff;
      padding: 10px 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .content {
      padding: 20px;
    }
    .overview-box {
      background: #eef2ff;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      margin-bottom: 30px;
    }
    .overview-box h4 {
      margin-top: 10px;
    }
    .btn-add {
      float: right;
      margin-bottom: 10px;
    }
    .settings {
      position: absolute;
      bottom: 20px;
      width: 100%;
    }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h4 class="mb-4">Attendance Ms</h4>
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="manage_employee.php">Manage Employee</a>
    <div class="settings">
      <a href="#">Settings</a>
      <a href="logout.php" class="text-danger">Logout</a>
    </div>
  </div>

  <!-- Main -->
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
    <div class="content container-fluid">
      
      <!-- Today's Date Added Here -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Overview</h3>
        <div class="text-muted">
          📅 Today's Date: <?php echo date('F d, Y'); ?>
        </div>
      </div>

      <div class="overview-box">
        <?php
        // Count total employees
        $countResult = $conn->query("SELECT COUNT(*) AS total FROM employees");
        $countRow = $countResult->fetch_assoc();
        $totalEmployees = $countRow['total'];
        ?>
        <h1><?php echo $totalEmployees; ?></h1>
        <h4>Registered Employees</h4>
      </div>

      <div class="d-flex mb-3">
        <select class="form-select w-auto me-2">
          <option selected>Day</option>
          <option>Week</option>
          <option>Month</option>
        </select>
        <input type="date" class="form-control w-auto me-2">
        <input type="date" class="form-control w-auto me-2">
        <button class="btn btn-success">Filter</button>
      </div>

      <div class="mb-3">
        <a href="manage_employee.php" class="btn btn-primary btn-add">
          <i class="bi bi-plus"></i> Add Employee
        </a>
      </div>

      <?php
$result = $conn->query("SELECT * FROM attendance ORDER BY created_at DESC");

echo "<table class='table'>";
echo "<thead><tr><th>Registration No.</th><th>Name</th><th>Faculty</th><th>Course</th><th>Email</th><th>Time In</th><th>Time Out</th></tr></thead><tbody>";

while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['registration_number']}</td>
            <td>{$row['name']}</td>
            <td>{$row['faculty']}</td>
            <td>{$row['course']}</td>
            <td>{$row['email']}</td>
            <td>{$row['time_in']}</td>
            <td>{$row['time_out']}</td>
          </tr>";
}
echo "</tbody></table>";
?>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
