<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['user']['name']; // For topbar
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
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
      border-radius: 10px;
    }
    .sidebar a:hover {
      background: rgb(91, 79, 165);
    }
    .sidebar .active {
      background: rgb(91, 79, 165);
      color: white;
    }
    .settings {
      position: absolute;
      bottom: 20px;
      width: 80%;
      border-radius: 10px;
    }
    .settings a {
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

    .form-wrapper {
      max-width: 600px;
      background: #ffffff;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
      background-color: #f8f9fc;
    }

    .form-wrapper h3 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    .form-wrapper input {
      margin-bottom: 15px;
      border-radius: 8px;
      padding: 12px;
      border: 1px solid #ddd;
      font-size: 16px;
    }

    .form-wrapper input:focus {
      border-color: #5c6bc0;
      outline: none;
    }

    .btn-primary {
      padding: 12px;
      background-color: #5c6bc0;
      border-color: #5c6bc0;
      border-radius: 8px;
    }

    .btn-primary:hover {
      background-color: #4f5b97;
      border-color: #4f5b97;
    }

    .error-message {
      color: red;
      text-align: center;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .topbar img {
      border-radius: 50%;
    }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h4 class="mb-4">Attendance Ms</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_employee.php">Manage Employee</a>
    <a href="signup.php" class="active">Add User</a>
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
    <div class="content container">
      <h2 class="mb-4 text-center">Add User</h2>
      <div class="form-wrapper mx-auto shadow-sm">
        <?php
        if (isset($error)) {
            echo "<div class='error-message'>$error</div>";
        }
        ?>

        <form method="POST" action="auth.php">
          <input type="text" name="name" class="form-control w-100" placeholder="Full Name" required>
          <input type="email" name="email" class="form-control w-100" placeholder="Email Address" required>
          <input type="password" name="password" class="form-control w-100" placeholder="Password" required>
          <input type="hidden" name="role" value="admin">
          <button type="submit" name="signup" class="btn btn-primary w-100">Done</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
