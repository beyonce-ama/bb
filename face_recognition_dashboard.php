<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.php';

// Get user name from session
$username = $_SESSION['user']['name'];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Face Recognition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        video {
            border: 2px solid #007bff;
            border-radius: 5px;
            width: 100%;
            max-width: 600px;
            height: auto;
        }
        canvas {
            display: none;
        }
        .loading-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .face-guide {
            position: absolute;
            border: 3px dashed #28a745;
            width: 70%;
            height: 70%;
            top: 15%;
            left: 15%;
            pointer-events: none;
            display: none;
        }
        .video-container {
            position: relative;
        }
        .error-details {
            font-size: 0.8em;
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <h4>Face Recognition</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="user_dashboard.php" class="nav-link text-white">Home</a>
        </li>
        <li class="nav-item">
            <a href="face_recognition_dashboard.php" class="nav-link text-white active">Face Recognition</a>
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
        <h5>Face Recognition Dashboard</h5>
        <div>
            <span class="badge bg-light text-dark">@ <?php echo htmlspecialchars($username); ?></span>
            <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="User" width="40" height="40" class="rounded-circle ms-2">
        </div>
    </div>

    <!-- Content -->
    <div class="content container-fluid">
        <h3>Welcome, <?php echo htmlspecialchars($username); ?>!</h3>
        <p>Click the button below to mark your attendance using facial recognition.</p>
        <hr>
        <h4>Face Recognition (Time In/Out)</h4>

        <div class="row">
            <div class="col-md-6">
                <div class="video-container">
                    <video id="video" autoplay playsinline></video>
                    <div class="face-guide" id="faceGuide"></div>
                    <canvas id="canvas"></canvas>
                </div>
                <button id="scanButton" class="btn btn-primary mt-3">Scan Face</button>
                <div id="scanStatus" class="mt-2"></div>
            </div>
            <div class="col-md-6">
                <div id="attendanceResult" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const scanButton = document.getElementById('scanButton');
    const resultDiv = document.getElementById('attendanceResult');
    const statusDiv = document.getElementById('scanStatus');
    const faceGuide = document.getElementById('faceGuide');
    
    // Create canvas context with optimization
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    
    // Track if camera is accessible
    let cameraAccessible = false;
    
    // Show face guide when camera is active
    video.addEventListener('playing', () => {
        faceGuide.style.display = 'block';
        cameraAccessible = true;
    });
    
    // Hide face guide if camera stops
    video.addEventListener('pause', () => {
        faceGuide.style.display = 'none';
    });

    // Initialize camera
    function initCamera() {
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: 'user' 
            } 
        })
        .then(stream => {
            video.srcObject = stream;
            return video.play();
        })
        .catch(err => {
            console.error("Camera error:", err);
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <strong>Camera Error:</strong> ${err.message || 'Could not access camera'}
                    <div class="error-details">Make sure you've granted camera permissions and your camera is working properly.</div>
                </div>
            `;
            scanButton.disabled = true;
            cameraAccessible = false;
        });
    }

    // Initialize camera on page load
    initCamera();

    scanButton.addEventListener('click', async () => {
        if (!cameraAccessible) {
            statusDiv.innerHTML = `
                <div class="alert alert-warning">
                    Camera not accessible. Please allow camera permissions and refresh the page.
                </div>
            `;
            return;
        }

        // Set canvas dimensions
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Capture frame
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert to data URL
        const imageDataURL = canvas.toDataURL('image/jpeg', 0.8);
        
        // Validate image data before sending
        if (!imageDataURL.startsWith('data:image/jpeg;base64,')) {
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    Invalid image format captured. Please try again.
                </div>
            `;
            return;
        }
        
        // Show loading state
        scanButton.disabled = true;
        scanButton.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Processing...
        `;
        
        statusDiv.innerHTML = `
            <div class="alert alert-info">
                <div class="loading-spinner"></div>
                Processing face recognition...
            </div>
        `;

        try {
            const response = await fetch('process_face_scan.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ image: imageDataURL })
            });
            
            // First check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                throw new Error(`Server returned invalid format: ${text.substring(0, 100)}`);
            }
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `Server responded with ${response.status}`);
            }
            
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5>Attendance Recorded</h5>
                        <p>Name: ${data.name}</p>
                        <p>Registration: ${data.registration_number}</p>
                        <p>Status: ${data.status}</p>
                        <p>Time: ${data.time}</p>
                    </div>
                `;
                
                // Show success notification
                Swal.fire({
                    icon: 'success',
                    title: 'Attendance Recorded',
                    text: `${data.status} at ${data.time}`,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                throw new Error(data.message || 'Face recognition failed');
            }
        } catch (error) {
            console.error("Full error details:", error);
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h5>Error</h5>
                    <p>${error.message}</p>
                    <div class="error-details">
                        <button class="btn btn-sm btn-link" onclick="console.error('Full error:', ${JSON.stringify(error.message)})">
                            Show Technical Details
                        </button>
                    </div>
                </div>
            `;
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message,
            });
        } finally {
            // Reset button state
            scanButton.disabled = false;
            scanButton.textContent = 'Scan Face';
            statusDiv.innerHTML = '';
        }
    });
});
</script>
</body>
</html>