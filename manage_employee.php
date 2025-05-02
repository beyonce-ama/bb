<?php
session_start();

// Redirect if not admin
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "attdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$username = $_SESSION['user']['name']; 
// Handle adding employee
if (isset($_POST['add_employee'])) {
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $email = $_POST['email'];
  $faculty = $_POST['faculty'];
  $course = $_POST['course'];
  $registration_number = $_POST['registration_number'];
  $created_at = date('Y-m-d H:i:s');

  // Prepare SQL with placeholders for all photos
  $stmt = $conn->prepare("INSERT INTO employees 
      (first_name, last_name, email, faculty, course, registration_number, 
       photo, photo1, photo2, photo3, photo4, created_at) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  
  // Bind parameters
  $stmt->bind_param("ssssssbbbbbs", 
      $first_name, $last_name, $email, $faculty, $course, $registration_number,
      $photo, $photo1, $photo2, $photo3, $photo4, $created_at);

  // Process each image
  $photos = array_fill(0, 5, null);
  if (!empty($_POST['captured_images'])) {
      $images = json_decode($_POST['captured_images']);
      foreach ($images as $index => $imageData) {
          if ($index >= 5) break; // Only store 5 images
          
          $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
          $photos[$index] = base64_decode($imageData);
      }
  }

  // Assign photos to variables for binding
  $photo = $photos[0];
  $photo1 = $photos[1];
  $photo2 = $photos[2];
  $photo3 = $photos[3];
  $photo4 = $photos[4];

  // Send long data for each photo
  $stmt->send_long_data(6, $photo);
  $stmt->send_long_data(7, $photo1);
  $stmt->send_long_data(8, $photo2);
  $stmt->send_long_data(9, $photo3);
  $stmt->send_long_data(10, $photo4);

  // Execute the statement
  if ($stmt->execute()) {
      // Insert into users table (for login)
      $full_name = $first_name . ' ' . $last_name;
      $default_password = password_hash('123456', PASSWORD_DEFAULT);
      $role = 'employee';
      $stmt2 = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
      $stmt2->bind_param("ssss", $full_name, $email, $default_password, $role);
      $stmt2->execute();
      $stmt2->close();

      echo "<script>alert('Employee and Account created successfully!'); window.location.href='manage_employee.php';</script>";
  } else {
      echo "<script>alert('Error creating employee: " . $conn->error . "');</script>";
  }
  $stmt->close();
}
// Handle delete employee
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    // Get email first (to delete from users table too)
    $result = $conn->query("SELECT email FROM employees WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $conn->query("DELETE FROM users WHERE email = '$email'");
    }

    $conn->query("DELETE FROM employees WHERE id = $id");

    echo "<script>alert('Employee deleted successfully!'); window.location.href='manage_employee.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Employees</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      overflow-x: hidden;
    }
    .sidebar {
      height: 100vh;
      background: #272343;
      padding-top: 20px;
      position: fixed;
    }
    .sidebar a {
      display: block;
      padding: 10px 20px;
      color: #ffffff;
      text-decoration: none;
    }
    .sidebar a:hover {
      background: rgb(91, 79, 165);
      border-radius: 10px;
    }
    .sidebar .active {
      background: rgb(91, 79, 165);
      color: white;
      border-radius: 10px;
    }
    .sidebar h4 {
      color: #ffffff !important;
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
    .topbar {
      height: 60px;
      padding: 10px 20px;
      background-color: #ffffff;
      border-bottom: solid #bae8e8 1px;
      display: flex;
      align-items: center;
      justify-content: space-between;
     
    }
    .content {
      margin-left: 230px;
      padding: 20px;
    }
    video {
      border: 2px solid #007bff;
      border-radius: 5px;
      margin-bottom: 10px;
      max-width: 100%;
    }
    #captured-photo img {
      margin: 5px;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    .face-guide {
      position: absolute;
      top: 60px;
      left: 25%;
      transform: translateX(-50%);
      width: 300px;
      height: 320px;
      border: 3px solid #28a745;
      z-index: 10;
      pointer-events: none;
    }
    .table-responsive {
      overflow-x: auto;
    }
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }
      .content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body onload="openCamera()">

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar p-3">
    <h4 class="mb-4">Attendance Ms</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_employee.php" class="active">Manage Employee</a>
    <a href="signup.php">Add User</a>
    <div class="settings">
      <a href="logout.php" class="text-danger">Logout</a>
    </div>
  </div>

  <div class="flex-grow-1">
    <div class="topbar justify-content-end d-flex">
      <div>
        <span class="badge bg-light text-dark">@ <?php echo htmlspecialchars($username); ?></span>
        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="User" width="40" height="40" class="rounded-circle ms-2">
      </div>
    </div>

    <div class="content">
      <h2 class="mt-4">Manage Employees</h2>

      <button class="btn btn-primary mb-3" onclick="showAddForm()">Add Employee</button>

      <div id="addEmployeeForm" style="display:none;">
        <form method="POST" enctype="multipart/form-data">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
              </div>
              <div class="form-group mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
              </div>
              <div class="form-group mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label>Faculty</label>
                <input type="text" name="faculty" class="form-control" required>
              </div>
              <div class="form-group mb-3">
                <label>Course</label>
                <input type="text" name="course" class="form-control" required>
              </div>
              <div class="form-group mb-3">
                <label>Registration Number</label>
                <input type="text" name="registration_number" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="form-group mb-3">
            <label>Capture 5 Photos for Face Recognition</label>
            <div class="position-relative">
              <div class="face-guide"></div>
              <video id="video" width="640" height="480" autoplay></video>
              <button type="button" onclick="captureImage()" class="btn btn-success mb-2">Capture Photo</button>
              <canvas id="canvas" style="display:none;"></canvas>
              <div id="captured-photo" class="d-flex flex-wrap"></div>
              <div id="capture-status" class="text-muted"></div>
            </div>
            <input type="hidden" name="captured_images" id="captured_images">
          </div>

          <button type="submit" name="add_employee" class="btn btn-success">Submit</button>
          <button type="button" onclick="hideAddForm()" class="btn btn-secondary">Back</button>
        </form>
      </div>

      <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search employees..." class="form-control mb-3" style="max-width: 300px;">

      <div class="table-responsive shadow-sm p-3">
        <table class="table table-light table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
              <th>Faculty</th>
              <th>Course</th>
              <th>Registration Number</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="employeeTable">
            <?php
            $result = $conn->query("SELECT * FROM employees ORDER BY id DESC");
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
            <tr>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td><?= htmlspecialchars($row['first_name']) ?></td>
              <td><?= htmlspecialchars($row['last_name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['faculty']) ?></td>
              <td><?= htmlspecialchars($row['course']) ?></td>
              <td><?= htmlspecialchars($row['registration_number']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="10">No employees found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
let capturedImages = [];

function showAddForm() {
    document.getElementById('addEmployeeForm').style.display = 'block';
}

function hideAddForm() {
    document.getElementById('addEmployeeForm').style.display = 'none';
}

function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const rows = document.getElementById('employeeTable').getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        let rowText = rows[i].innerText.toLowerCase();
        rows[i].style.display = rowText.indexOf(filter) > -1 ? "" : "none";
    }
}

function openCamera() {
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            width: 640, 
            height: 480,
            facingMode: 'user' 
        } 
    })
    .then(stream => {
        document.getElementById('video').srcObject = stream;
    })
    .catch(err => {
        console.error('Error accessing camera: ', err);
        alert('Please allow camera access.');
    });
}

function captureImage() {
    if (capturedImages.length >= 5) {
        alert('You have already captured 5 photos.');
        return;
    }

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedPhotoDiv = document.getElementById('captured-photo');
    const statusDiv = document.getElementById('capture-status');

    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Check face alignment (simple version - would need face detection for better)
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const faceDetected = checkFaceAlignment(imageData); // Simple check
    
    if (!faceDetected) {
        statusDiv.innerHTML = '<div class="alert alert-warning">Please align your face in the green box</div>';
        return;
    }

    // Get image as JPEG
    const imageDataURL = canvas.toDataURL('image/jpeg', 0.8);
    
    // Add to captured images array
    capturedImages.push(imageDataURL);
    
    // Display the captured image
    capturedPhotoDiv.innerHTML += `
        <div class="position-relative" style="width: 120px;">
            <img src="${imageDataURL}" width="120" class="img-thumbnail">
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                ${capturedImages.length}
            </span>
        </div>
    `;
    
    // Update the hidden input with all captured images as JSON
    document.getElementById('captured_images').value = JSON.stringify(capturedImages);
    
    // Update status
    statusDiv.innerHTML = `<div class="alert alert-success">Captured photo ${capturedImages.length} of 5</div>`;
    
    // If we have 5 photos, suggest to submit
    if (capturedImages.length === 5) {
        statusDiv.innerHTML += '<div class="alert alert-info mt-2">All photos captured! You can now submit the form.</div>';
    }
}

// Simple face alignment check (would need proper face detection for accuracy)
function checkFaceAlignment(imageData) {
    // In a real implementation, you would use face detection here
    // This is just a placeholder that always returns true
    return true;
    
    /* Example of what you might do with a face detection library:
    const faceLocations = faceapi.detectAllFaces(imageData);
    if (faceLocations.length === 0) return false;
    
    const face = faceLocations[0];
    // Check if face is centered and properly sized
    const centerX = face.x + face.width/2;
    const centerY = face.y + face.height/2;
    const guide = document.querySelector('.face-guide').getBoundingClientRect();
    
    return (
        centerX > guide.left && centerX < guide.right &&
        centerY > guide.top && centerY < guide.bottom &&
        face.width > guide.width * 0.6 && face.width < guide.width * 0.9
    );
    */
}
</script>
</body>
</html>