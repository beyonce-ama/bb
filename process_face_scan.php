<?php
session_start();
require_once 'db.php';

// Set headers first to ensure proper content type
header("Content-Type: application/json");

// Clear all output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Error handling setup
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'face_scan_errors.log');

// Function to send JSON error response
function sendJsonError($message, $debugData = []) {
    http_response_code(400); // Bad Request
    $response = [
        'success' => false,
        'message' => $message,
        'debug' => $debugData
    ];
    
    // Clean any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($response);
    exit();
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonError('Invalid request method', ['method' => $_SERVER['REQUEST_METHOD']]);
}

// Get and validate JSON input
$jsonInput = file_get_contents('php://input');
if ($jsonInput === false) {
    sendJsonError('Failed to read input data');
}

$data = json_decode($jsonInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendJsonError('Invalid JSON input', ['json_error' => json_last_error_msg()]);
}

if (!isset($data['image'])) {
    sendJsonError('No image data received', ['received_data' => array_keys($data)]);
}

// Validate image data
$imageParts = explode(",", $data['image']);
if (count($imageParts) < 2 || strpos($imageParts[0], 'image/jpeg') === false) {
    sendJsonError('Invalid image format', ['image_parts' => count($imageParts)]);
}

// Create temp directory if needed
$tempDir = 'temp_scans';
if (!file_exists($tempDir)) {
    if (!mkdir($tempDir, 0777, true)) {
        sendJsonError('Failed to create temp directory', ['directory' => $tempDir]);
    }
}

// Save temporary image
$tempFile = $tempDir . '/scan_' . session_id() . '_' . time() . '.jpg';
$imageData = base64_decode($imageParts[1]);

if ($imageData === false) {
    sendJsonError('Failed to decode image data');
}

$bytesWritten = file_put_contents($tempFile, $imageData);
if ($bytesWritten === false) {
    sendJsonError('Failed to save temporary image', [
        'temp_file' => $tempFile,
        'file_size' => strlen($imageData)
    ]);
}

// Validate image file
try {
    $imageInfo = @getimagesize($tempFile);
    if ($imageInfo === false) {
        unlink($tempFile);
        sendJsonError('Invalid image file', ['file' => $tempFile]);
    }
} catch (Exception $e) {
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    sendJsonError('Image validation failed', ['error' => $e->getMessage()]);
}

// Execute Python script with full path and error reporting
$pythonScript = __DIR__ . '/recognize_scan.py';
$pythonExecutable = 'C:\\Users\\bea\\AppData\\Local\\Programs\\Python\\Python313\\python.exe';

// Debugging: Log the command
error_log("Executing: $pythonExecutable $pythonScript $tempFile");

$descriptors = [
    0 => ['pipe', 'r'], // stdin
    1 => ['pipe', 'w'], // stdout
    2 => ['pipe', 'w']  // stderr
];

$process = proc_open("$pythonExecutable $pythonScript " . escapeshellarg($tempFile), $descriptors, $pipes);

if (!is_resource($process)) {
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    sendJsonError('Failed to execute Python script', ['temp_file' => $tempFile]);
}

// Get output and errors
$output = stream_get_contents($pipes[1]);
$errors = stream_get_contents($pipes[2]);
$returnCode = proc_close($process);

// Clean up temp file
if (file_exists($tempFile)) {
    unlink($tempFile);
}

// Log execution details
error_log("Python output: " . $output);
error_log("Python errors: " . $errors);
error_log("Return code: " . $returnCode);

if ($returnCode !== 0) {
    sendJsonError("Python script failed with code $returnCode", [
        'output' => $output,
        'errors' => $errors,
        'command' => "$pythonExecutable $pythonScript $tempFile"
    ]);
}

// Clean the output by removing any non-JSON content
$jsonStart = strpos($output, '{');
$jsonEnd = strrpos($output, '}');
if ($jsonStart === false || $jsonEnd === false) {
    sendJsonError('No valid JSON found in Python output', [
        'raw_output' => $output,
        'python_errors' => $errors
    ]);
}

$cleanOutput = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
$result = json_decode($cleanOutput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendJsonError('Invalid JSON response from Python script', [
        'json_error' => json_last_error_msg(),
        'raw_output' => $output,
        'clean_output' => $cleanOutput,
        'python_errors' => $errors
    ]);
}

if (!$result['success']) {
    sendJsonError($result['message'] ?? 'Face recognition failed', [
        'python_result' => $result,
        'python_errors' => $errors
    ]);
}

// Verify employee exists
try {
    // Check if $conn exists and is connected
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection not established');
    }

    $stmt = $conn->prepare("SELECT * FROM employees WHERE registration_number = ?");
    $stmt->bind_param("s", $result['registration_number']);
    $stmt->execute();
    $resultSet = $stmt->get_result();
    $employee = $resultSet->fetch_assoc();
    
    if (!$employee) {
        sendJsonError('Employee not found in database', [
            'registration_number' => $result['registration_number']
        ]);
    }
} catch (Exception $e) {
    sendJsonError('Database error while fetching employee', [
        'error' => $e->getMessage(),
        'registration_number' => $result['registration_number']
    ]);
}

// Process attendance
$currentTime = date('Y-m-d H:i:s');
$today = date('Y-m-d');
$status = '';

try {
    // Check existing attendance for today
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE registration_number = ? AND DATE(time_in) = ?");
    $stmt->bind_param("ss", $employee['registration_number'], $today);
    $stmt->execute();
    $resultSet = $stmt->get_result();
    $attendance = $resultSet->fetch_assoc();
    
    if ($attendance) {
        // Update time_out if not already set
        if (!$attendance['time_out']) {
            $updateStmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE id = ?");
            $updateStmt->bind_param("si", $currentTime, $attendance['id']);
            $updateStmt->execute();
            $status = 'Time Out';
        } else {
            sendJsonError('Attendance already recorded for today (both time in and out)', [
                'existing_attendance' => $attendance
            ]);
        }
    } else {
        // Create new time in record
        $insertStmt = $conn->prepare("INSERT INTO attendance 
            (registration_number, name, faculty, course, email, time_in, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $fullName = $employee['first_name'] . ' ' . $employee['last_name'];
        $insertStmt->bind_param("ssssss", 
            $employee['registration_number'],
            $fullName,
            $employee['faculty'],
            $employee['course'],
            $employee['email'],
            $currentTime
        );
        $insertStmt->execute();
        $status = 'Time In';
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'name' => $employee['first_name'] . ' ' . $employee['last_name'],
        'registration_number' => $employee['registration_number'],
        'status' => $status,
        'time' => $currentTime
    ]);
    
} catch (Exception $e) {
    sendJsonError('Database error while recording attendance', [
        'error' => $e->getMessage(),
        'employee' => $employee
    ]);
}