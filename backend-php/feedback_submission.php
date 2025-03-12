<?php
// feedback_submission_handler.php
include 'database.php'; // Include database connection

// Start the session (assuming you are using sessions to track logged-in users)
session_start();

// Check if student_id is available (you could have logged-in student in session)
if (!isset($_SESSION['teacher_id'])) {
    echo "You must be logged in to submit feedback.";
    exit;
}

// Retrieve the student_id from the session (assuming it's stored there)
$student_id = $_SESSION['teacher_id'];
$student_name = $_POST['student_name']; // optional
$feedback_text = $_POST['feedback_text'];



// Check if the student_id exists in the users table
$sql_check_student = "SELECT id FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_check_student);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Invalid student ID. Please ensure you are logged in.";
    exit;
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO feedback (student_id, feedback_text) VALUES (?, ?)");
$stmt->bind_param("is", $student_id,$feedback_text);

// Execute the statement
if ($stmt->execute()) {
    echo "Feedback submitted successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
