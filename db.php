<?php
$host = "localhost";  // usually localhost
$username = "root";   // your MySQL username
$password = "";       // your MySQL password
$database = "attdb"; // the database name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
