<?php
$logFile = 'C:\Users\hp\Pictures\Camera Roll/logfile.log'; // Change to your desired log file path

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}
// Connect to database
$conn = new mysqli("localhost", "root", "", "esak"); // Change connection settings accordingly

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to update reservations that have expired
$sql = "UPDATE reservation
        SET status = 'canceled'
        WHERE expiration_date < NOW() AND status = 'active'";

// Execute the query
if ($conn->query($sql) === TRUE) {
    $affectedRows = $conn->affected_rows;
    if ($affectedRows > 0) {
        echo "$affectedRows expired reservations have been canceled successfully.";
        logMessage("$affectedRows expired reservations canceled.");
    } else {
        echo "No expired reservations to cancel.";
    }
} else {
    echo "Error updating reservations: " . $conn->error;
}

// Close connection
$conn->close();
?>