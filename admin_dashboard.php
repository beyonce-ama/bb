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


// Get unique dates (for daily filter)
$datesResult = $conn->query("SELECT DISTINCT DATE(created_at) as date FROM attendance ORDER BY date DESC");
$availableDates = [];
while ($row = $datesResult->fetch_assoc()) {
    $availableDates[] = $row['date'];
}

// Get unique months (for monthly filter)
$monthsResult = $conn->query("SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as month FROM attendance ORDER BY month DESC");
$availableMonths = [];
while ($row = $monthsResult->fetch_assoc()) {
    $availableMonths[] = $row['month'];
}

?>


<?php
 
$registerSuccess = isset($_SESSION['register_success']) ? $_SESSION['register_success'] : null;
if ($registerSuccess) {
    unset($_SESSION['register_success']); // Optional: remove it after showing once
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
      background-color: #ffffff;
 
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
      height: 60px;
      background-color: #ffffff;
      border-bottom: solid #bae8e8 1px;
      padding: 10px 20px;
     
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
  
  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h4 class="mb-4">Attendance Ms</h4>
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="manage_employee.php">Manage Employee</a>
    <a href="signup.php">Add User</a>
    <div class="settings">
      <a href="logout.php" class="text-danger">Logout</a>
    </div>
  </div>

  <!-- Main -->
  <div class="flex-grow-1">
    <!-- Topbar -->
    <div class="topbar d-flex justify-content-end">

      <div>
        <span class="badge bg-light text-dark">@ <?php echo htmlspecialchars($username); ?></span>
        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="User" width="40" height="40" class="rounded-circle ms-2">
      </div>
    </div>

    <!-- Content -->
    <div class="content container-fluid px-5">
      
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

      <div class="d-flex mb-3 justify-content-between">

      <div class="d-flex">
      <form method="GET" class="d-flex mb-3">
  <input type="text" name="search" class="form-control mx-2" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
  <button type="submit" class="btn btn-outline-success">Search</button>
</form>

      </div>
      
      <div class="d-flex">
      <form method="GET" class="d-flex mb-3 align-items-end" id="filterForm">
  <select name="filter_type" id="filterType" class="form-select w-auto me-2">
    <option value="day" <?= (($_GET['filter_type'] ?? '') == 'day') ? 'selected' : '' ?>>Daily</option>
    <option value="month" <?= (($_GET['filter_type'] ?? '') == 'month') ? 'selected' : '' ?>>Monthly</option>
  </select>

  <!-- Daily dropdown -->
  <select name="filter_date" id="dailySelect" class="form-select w-auto me-2">
    <option value="">Select a Date</option>
    <?php foreach ($availableDates as $date): ?>
      <option value="<?= $date ?>" <?= (($_GET['filter_date'] ?? '') == $date) ? 'selected' : '' ?>>
        <?= date('F d, Y', strtotime($date)) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- Monthly dropdown -->
  <select name="filter_month" id="monthlySelect" class="form-select w-auto me-2" style="display:none;">
    <option value="">Select a Month</option>
    <?php foreach ($availableMonths as $month): ?>
      <option value="<?= $month ?>" <?= (($_GET['filter_month'] ?? '') == $month) ? 'selected' : '' ?>>
        <?= date('F Y', strtotime($month . '-01')) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit" class="btn btn-success w-auto">Filter</button>
</form>


<script>
document.getElementById('filterType').addEventListener('change', function () {
  const daySelect = document.getElementById('dailySelect');
  const monthSelect = document.getElementById('monthlySelect');

  if (this.value === 'day') {
    daySelect.style.display = 'block';
    monthSelect.style.display = 'none';
  } else {
    daySelect.style.display = 'none';
    monthSelect.style.display = 'block';
  }
});

// Run on page load to match initial state
window.addEventListener('DOMContentLoaded', function () {
  const filterType = document.getElementById('filterType').value;
  document.getElementById('filterType').dispatchEvent(new Event('change'));
});
</script>


 

            <form method="post" action="export_excel.php">
      <button type="submit" class="btn btn-secondary   w-auto mx-2">
        📤 Export to Excel
      </button>
    </form>
      </div>


      </div>

      <div class="mb-3">
        <a href="manage_employee.php" class="btn btn-primary btn-add">
          <i class="bi bi-plus"></i> Add Employee
        </a>
      </div>

      <?php
 
$whereClause = "WHERE 1"; // default where

// Filter by date/month
if (isset($_GET['filter_type'])) {
    $filterType = $_GET['filter_type'];

    if ($filterType === 'day' && !empty($_GET['filter_date'])) {
        $date = $_GET['filter_date'];
        $whereClause .= " AND DATE(created_at) = '$date'";
    }

    if ($filterType === 'month' && !empty($_GET['filter_month'])) {
        $month = $_GET['filter_month'];
        $whereClause .= " AND DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    }
}

// Search condition
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $whereClause .= " AND (
        registration_number LIKE '%$search%' OR
        name LIKE '%$search%' OR
        faculty LIKE '%$search%' OR
        course LIKE '%$search%' OR
        email LIKE '%$search%'
    )";
}






$query = "SELECT * FROM attendance $whereClause ORDER BY created_at DESC";
$result = $conn->query($query);



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

<div id="successModal" class="shadow-sm border-1" style="display:none; position: fixed; bottom: 20px; right: 20px; border-radius: 10px; background: rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; text-align: center;">
    <h3 style="color: green;">Account Created Successfully</h3>
    <p>You can now log in using your credentials.</p>
    <button onclick="document.getElementById('successModal').style.display='none'" style="margin-top: 20px;" class="btn btn-light text-danger">Close</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php if ($registerSuccess): ?>
<script>
  window.onload = function() {
    document.getElementById('successModal').style.display = 'flex';
  }
</script>
<?php endif; ?>
</html>
