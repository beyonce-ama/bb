<?php
session_start();
$conn = new mysqli("localhost", "root", "", "attdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the employee ID
if (!isset($_GET['id'])) {
    echo "No ID specified.";
    exit();
}

$id = intval($_GET['id']);

// Handle form submission (update employee)
if (isset($_POST['update_employee'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $faculty = $_POST['faculty'];
    $course = $_POST['course'];
    $registration_number = $_POST['registration_number'];

    $stmt = $conn->prepare("UPDATE employees SET first_name=?, last_name=?, email=?, faculty=?, course=?, registration_number=? WHERE id=?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $faculty, $course, $registration_number, $id);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Employee updated successfully!'); window.location.href='manage_employee.php';</script>";
    exit();
}

// Fetch employee data
$result = $conn->query("SELECT * FROM employees WHERE id = $id");
if ($result->num_rows == 0) {
    echo "Employee not found.";
    exit();
}
$employee = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<h2>Edit Employee</h2>

<form method="POST">
    <div class="form-group mb-2">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
    </div>
    <div class="form-group mb-2">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($employee['last_name']) ?>" required>
    </div>
    <div class="form-group mb-2">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" required>
    </div>
    <div class="form-group mb-2">
        <label>Faculty</label>
        <input type="text" name="faculty" class="form-control" value="<?= htmlspecialchars($employee['faculty']) ?>" required>
    </div>
    <div class="form-group mb-2">
        <label>Course</label>
        <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($employee['course']) ?>" required>
    </div>
    <div class="form-group mb-3">
        <label>Registration Number</label>
        <input type="text" name="registration_number" class="form-control" value="<?= htmlspecialchars($employee['registration_number']) ?>" required>
    </div>

    <button type="submit" name="update_employee" class="btn btn-success">Update</button>
    <a href="manage_employee.php" class="btn btn-secondary">Cancel</a>
</form>

</body>
</html>
