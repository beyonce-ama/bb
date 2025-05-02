<?php
require 'db.php'; // your DB connection file

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_" . date("Y-m-d") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>";
echo "<tr>
        <th>Registration No.</th>
        <th>Name</th>
        <th>Faculty</th>
        <th>Course</th>
        <th>Email</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Total Hours</th>
      </tr>";

$result = $conn->query("SELECT * FROM attendance ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
    $timeIn = $row['time_in'];
    $timeOut = $row['time_out'];
    $totalHours = '';

    if ($timeIn && $timeOut) {
        $start = new DateTime($timeIn);
        $end = new DateTime($timeOut);
        $interval = $start->diff($end);
        $totalHours = $interval->format('%h:%I'); // outputs like "2:30"
    }

    echo "<tr>
            <td>{$row['registration_number']}</td>
            <td>{$row['name']}</td>
            <td>{$row['faculty']}</td>
            <td>{$row['course']}</td>
            <td>{$row['email']}</td>
            <td>{$timeIn}</td>
            <td>{$timeOut}</td>
            <td>{$totalHours}</td>
          </tr>";
}

echo "</table>";
?>
