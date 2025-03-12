<?php
session_start();
include '../../../backend-php/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../../vendor/autoload.php'; // Adjust the path based on your project structure

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fine_id = $_POST['fine_id'];

    // Get fine details
    $fineQuery = "SELECT f.reason, f.book_id, f.user_id, u.email, u.name, b.title 
                  FROM payments f 
                  JOIN users u ON f.user_id = u.id
                  JOIN books b ON f.book_id = b.id
                  WHERE f.id = ?";
    $stmt = $conn->prepare($fineQuery);
    $stmt->bind_param("i", $fine_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false) {
        die("Error fetching fine details: " . $stmt->error);
    }
    $fineData = $result->fetch_assoc();
    

    if ($fineData) {
        $reason = $fineData['reason'];
        $book_id = $fineData['book_id'];
        $user_id = $fineData['user_id'];
        $user_email = $fineData['email'];
        $user_name = $fineData['name'];
        $book_title = $fineData['title'];

        // If the reason is "lost", update book status
        if ($reason == "lost") {
            $updateBookQuery = "UPDATE books SET status = 'lost' WHERE id = ?";
            $stmt = $conn->prepare($updateBookQuery);
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
        } else if ($reason == "overdue" || $reason == "damaged") {
            // Check if the book was returned
            $checkReturnQuery = "SELECT b.status AS book_status, br.status AS borrow_status
                                 FROM books b
                                 JOIN borrow_requests br ON br.book_id = b.id AND br.user_id = ?
                                 WHERE b.id = ? AND b.status = 'available' AND br.status = 'return_accept'";
            $stmt = $conn->prepare($checkReturnQuery);
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
            $returnResult = $stmt->get_result();

            if ($returnResult->num_rows === 0) {
                $message = "Error: The book must be returned before confirming the fine!";
            }
        }

        // If no error, confirm fine as paid
        if (!isset($message)) {
            $updateFineQuery = "UPDATE payments SET status = 'paid', payment_date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($updateFineQuery);
            $stmt->bind_param("i", $fine_id);
            $stmt->execute();

            // Send in-app notification
            $notificationMessage = "Your fine for the book '$book_title' due to '$reason' has been successfully paid.";
            $insertNotificationQuery = "INSERT INTO notifications (user_id, message, created_at, status) VALUES (?, ?, NOW(),'unread')";
            $stmt = $conn->prepare($insertNotificationQuery);
            $stmt->bind_param("is", $user_id, $notificationMessage);
            $stmt->execute();

            // Send email notification
            $mail = new PHPMailer(true);
            try {
                // SMTP settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'bekamgbdaw@gmail.com'; // Change to your email
                $mail->Password = 'iznb palr txzi wfqe'; // Use an app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                // Email details
                $mail->setFrom('bekamgbdaw@gmail.com', 'Library Management'); // Your library email
                $mail->addAddress($user_email, $user_name);
                $mail->Subject = "Library Fine Payment Confirmation";
                $mail->Body = "Dear $user_name,\n\nYour fine for the book '$book_title' due to '$reason' has been successfully paid.\n\nThank you for using our library services.\n\nBest regards,\nLibrary Management";
                $mail->send();

                $message = "Fine status updated successfully! Notification sent via email and in-app.";
            } catch (Exception $e) {
                $message = "Fine status updated, but email notification failed. Error: " . $mail->ErrorInfo;
            }
        }
    }
}
// Fetch all fines
$fineListQuery = "SELECT p.id as payment_id, p.user_id as student_id, u.name as student_name, 
                         b.title, p.reason, p.amount, p.status, p.payment_date as due_date 
                  FROM payments p
                  JOIN users u ON p.user_id = u.id
                  JOIN books b ON p.book_id = b.id";

$result = $conn->query($fineListQuery);

if (!$result) {
    die("Error fetching fines: " . $conn->error);
}
$conn->close();



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Fines</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .fine-amount {
            font-weight: bold;
            color: #E53935;
        }
        .reason {
            font-style: italic;
        }
        .status {
            font-weight: bold;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .btn {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .error {
            color: red;
            text-align: center;
            font-weight: bold;
        }
        .success {
            color: green;
            text-align: center;
            font-weight: bold;
        }
        .back-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .back-btn i {
            margin-right: 5px;
        }
        .back-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <button class="back-btn" onclick="window.location.href='dashboard.php';">
        <i class="fas fa-arrow-left"></i> Back
    </button>
    
    <h2>Confirm Fines</h2>
    
    <?php if (isset($message)) { echo "<p class='" . (strpos($message, 'Error') !== false ? "error" : "success") . "'>$message</p>"; } ?>
    
    <table>
        <tr>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Book</th>
            <th>Reason</th>
            <th>Amount (Birr)</th>
            <th>Status</th>
            <th>Due Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td class="reason"><?= ucfirst(htmlspecialchars($row['reason'])) ?></td>
                <td class="fine-amount"><?= htmlspecialchars($row['amount']) ?></td>
                <td class="status"><?= $row['status'] == 'pending' ? "Unpaid" : "Paid" ?></td>
                <td><?= $row['due_date'] ? htmlspecialchars($row['due_date']) : "N/A" ?></td>
                <td>
                    <?php if ($row['status'] == 'pending') { ?>
                        <form action="confirm_fine.php" method="POST">
                            <input type="hidden" name="fine_id" value="<?= $row['payment_id'] ?>">
                            <button type="submit" class="btn">Confirm as Paid</button>
                        </form>
                    <?php } else { ?>
                        <button class="btn" disabled>Paid</button>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>

