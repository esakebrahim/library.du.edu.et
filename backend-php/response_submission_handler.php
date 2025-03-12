<?php
// response_submission_handler.php
include 'database.php'; // Include database connection
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback_id = $_POST['feedback_id'];
    $response = $_POST['response'];
    $librarian_id = $_SESSION['librarian_id'];
    // Get the student's ID from the feedback (assuming you have a student_id field in feedback table)
    $feedbackQuery = "SELECT student_id FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($feedbackQuery);
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = $result->fetch_assoc();
    $student_id = $feedback['student_id']; // Student's ID
    
    // Insert the librarian's response into the librarian_responses table
    $responseQuery = "INSERT INTO librarian_responses (feedback_id, librarian_id, response_text) 
                      VALUES (?, ?, ?)";
    $stmt = $conn->prepare($responseQuery);
     // Assume librarian's ID is 1 for now (you can adjust this to dynamic login)
    $stmt->bind_param("iis", $feedback_id, $librarian_id, $response);
    
    if ($stmt->execute()) {
        // Update the feedback status to 'responded'
        $updateQuery = "UPDATE feedback SET status = 'responded' WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $feedback_id);
        $stmt->execute();

        // Create a notification for the student
        $notificationQuery = "INSERT INTO notifications (user_id, message, status,type) 
                               VALUES (?, ?, 'unread','feedback')";
        $notificationMessage = "Your feedback has been responded to. Please check the response.";
        $stmt = $conn->prepare($notificationQuery);
        $stmt->bind_param("is", $student_id, $notificationMessage);
        $stmt->execute();

        echo "Response submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
