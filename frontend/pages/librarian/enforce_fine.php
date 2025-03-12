<?php
session_start();
include '../../../backend-php/database.php';

if (!isset($_SESSION['librarian_id'])) {
    die("Access denied. Please log in as a librarian.");
}

$librarian_id = $_SESSION['librarian_id'];

// Initialize message variables for each form
$lost_message = "";
$damaged_message = "";

// Process form submission if received
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'], $_POST['book_id'], $_POST['reason'], $_POST['amount'], $_POST['book_type'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $reason = $_POST['reason'];
    $amount = $_POST['amount'];
    $book_type = $_POST['book_type'];

    // Check if a payment record already exists for this combination
    $checkStmt = $conn->prepare("SELECT id FROM payments WHERE user_id = ? AND book_id = ? AND reason = ?");
    $checkStmt->bind_param("iis", $user_id, $book_id, $reason);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        if ($book_type == 'lost') {
            $lost_message = "A fine for this lost book has already been enforced for this student.";
        } else {
            $damaged_message = "A fine for this damaged book has already been enforced for this student.";
        }
    } else {
        // Insert the new payment record
        $stmt = $conn->prepare("INSERT INTO payments (user_id, book_id, amount, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iids", $user_id, $book_id, $amount, $reason);
        if ($stmt->execute()) {
            if ($book_type == 'lost') {
                $lost_message = "Fine has been enforced successfully for lost book!";
            } else {
                $damaged_message = "Fine has been enforced successfully for damaged book!";
            }
        } else {
            if ($book_type == 'lost') {
                $lost_message = "Error enforcing fine for lost book: " . $stmt->error;
            } else {
                $damaged_message = "Error enforcing fine for damaged book: " . $stmt->error;
            }
        }
    }
}

// Fetch students who have lost books
$lostStudentsQuery  =  "SELECT DISTINCT u.id AS user_id, u.name, u.last_name 
                         FROM lost_books lb 
                         JOIN users u ON lb.user_id = u.id";
$lostStudentsResult  = $conn->query($lostStudentsQuery);

// Initialize lostBooksResult variable
$lostBooksResult = null;

// Fetch students with borrow requests marked as "return_pending" (for damaged books)
$damagedStudentsQuery = "SELECT DISTINCT u.id AS user_id, u.name, u.last_name 
                         FROM borrow_requests br 
                         JOIN users u ON br.user_id = u.id 
                         WHERE br.status = 'return_pending'";
$damagedStudentsResult = $conn->query($damagedStudentsQuery);

// If a lost book form submission occurred, fetch lost books for the selected student
if (isset($_POST['user_id']) && isset($_POST['book_type']) && $_POST['book_type'] == 'lost') {
    $user_id = $_POST['user_id'];
    $lostBooksQuery = "SELECT b.id, b.title, b.price FROM lost_books lb 
                       JOIN books b ON lb.book_id = b.id 
                       WHERE lb.user_id = ?";
    $stmt = $conn->prepare($lostBooksQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $lostBooksResult = $stmt->get_result();
}

// If a damaged book form submission occurred, fetch damaged books for the selected student
if (isset($_POST['user_id']) && isset($_POST['book_type']) && $_POST['book_type'] == 'damaged') {
    $user_id = $_POST['user_id'];
    $damagedBooksQuery = "SELECT b.id, b.title FROM borrow_requests br 
                          JOIN books b ON br.book_id = b.id 
                          WHERE br.user_id = ? AND br.status = 'return_pending'";
    $stmt = $conn->prepare($damagedBooksQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $damagedBooksResult = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enforce Fine for Lost or Damaged Book</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // When a student is selected in the lost books form, fetch lost books via AJAX
      document.getElementById("lost_user_id").addEventListener("change", function() {
        var userId = this.value;
        var bookDropdown = document.getElementById("lost_book_id");
        // Clear previous options
        bookDropdown.innerHTML = '<option value="">Select a book</option>';

        if (userId) {
          fetch("fetch_lost_books.php?user_id=" + userId)
            .then(response => response.json())
            .then(data => {
              data.forEach(book => {
                var option = document.createElement("option");
                option.value = book.id;
                option.setAttribute("data-price", book.price); // Include price in option
                option.textContent = book.title;
                bookDropdown.appendChild(option);
              });
            })
            .catch(error => console.error("Error fetching books:", error));
        }
      });

      // Calculate fine amount when a book is selected in the lost books form
      document.getElementById("lost_book_id").addEventListener("change", function() {
        var selectedOption = this.options[this.selectedIndex];
        var bookPrice = parseFloat(selectedOption.getAttribute("data-price"));
        if (!isNaN(bookPrice)) {
          // Fine calculated as 3 times the book's price
          document.getElementById("lost_amount").value = bookPrice * 3;
        } else {
          document.getElementById("lost_amount").value = "";
        }
      });

      // For the damaged books form, you might want to fetch damaged books dynamically.
      // Ensure you have a corresponding fetch_damaged_books.php file.
      document.getElementById("damaged_user_id").addEventListener("change", function() {
        var userId = this.value;
        var bookDropdown = document.getElementById("damaged_book_id");
        // Clear previous options
        bookDropdown.innerHTML = '<option value="">Select a book</option>';

        if (userId) {
          fetch("fetch_damaged_books.php?user_id=" + userId)
            .then(response => response.json())
            .then(data => {
              data.forEach(book => {
                var option = document.createElement("option");
                option.value = book.id;
                option.textContent = book.title;
                bookDropdown.appendChild(option);
              });
            })
            .catch(error => console.error("Error fetching damaged books:", error));
        }
      });
    });
  </script>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f4f4f9;
      color: #333;
      margin: 0;
      padding: 0;
    }
    .container {
      width: 80%;
      max-width: 1200px;
      margin: 50px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
    }
    h2 {
      text-align: center;
      color: #4CAF50;
      font-size: 2rem;
      width: 100%;
      margin-bottom: 40px;
    }
    .form-section {
      width: 45%;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      font-size: 1.1rem;
      font-weight: bold;
    }
    .form-group select, .form-group input {
      width: 100%;
      padding: 10px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }
    .form-group select:focus, .form-group input:focus {
      border-color: #4CAF50;
    }
    .btn-submit {
      width: 100%;
      padding: 15px;
      background-color: #4CAF50;
      color: white;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      border-radius: 5px;
    }
    .btn-submit:hover {
      background-color: #45a049;
    }
    .alert {
      background-color: #f2dede;
      color: #a94442;
      padding: 15px;
      border: 1px solid #ebccd1;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .alert.success {
      background-color: #dff0d8;
      color: #3c763d;
      border: 1px solid #d6e9c6;
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
    .form-group small {
      font-size: 0.9rem;
      color: #888;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Lost Book Fine Enforcement Section -->
    <div class="form-section">
      <button class="back-btn" onclick="window.location.href='dashboard.php';">
        <i class="fas fa-arrow-left"></i> Back
      </button>
      <h2>Lost Book Fine Enforcement</h2>
      <?php if (!empty($lost_message)) { ?>
        <div class="alert <?= strpos($lost_message, 'successfully') !== false ? 'success' : '' ?>">
          <?= $lost_message ?>
        </div>
      <?php } ?>
      <form method="POST" action="enforce_fine.php">
        <div class="form-group">
          <label for="lost_user_id">Select Student (Lost Book):</label>
          <select name="user_id" id="lost_user_id" required>
            <option value="">Select a student</option>
            <?php while ($row = $lostStudentsResult->fetch_assoc()) { ?>
              <option value="<?= $row['user_id'] ?>"><?= $row['name'] . ' ' . $row['last_name'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label for="lost_book_id">Select Book:</label>
          <select name="book_id" id="lost_book_id" required>
            <option value="">Select a book</option>
            <?php 
              // If the lost books were already fetched (for example, on form resubmission), list them
              if ($lostBooksResult) {
                  while ($row = $lostBooksResult->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>" data-price="<?= $row['price'] ?>">
                      <?= $row['title'] ?>
                    </option>
            <?php }
              }
            ?>
          </select>
        </div>
        <div class="form-group">
          <label for="lost_amount">Fine Amount (Birr):</label>
          <input type="number" name="amount" id="lost_amount" readonly>
        </div>
        <div class="form-group">
          <label for="reason">Fine Reason:</label>
          <select name="reason" id="reason" required>
            <option value="lost">Lost</option>
          </select>
        </div>
        <input type="hidden" name="book_type" value="lost">
        <button type="submit" class="btn-submit">Enforce Fine</button>
      </form>
    </div>

    <!-- Damaged Book Fine Enforcement Section -->
    <div class="form-section">
      <h2>Damaged Book Fine Enforcement</h2>
      <?php if (!empty($damaged_message)) { ?>
        <div class="alert <?= strpos($damaged_message, 'successfully') !== false ? 'success' : '' ?>">
          <?= $damaged_message ?>
        </div>
      <?php } ?>
      <form method="POST" action="enforce_fine.php">
        <div class="form-group">
          <label for="damaged_user_id">Select Student (Damaged Book):</label>
          <select name="user_id" id="damaged_user_id" required>
            <option value="">Select a student</option>
            <?php while ($row = $damagedStudentsResult->fetch_assoc()) { ?>
              <option value="<?= $row['user_id'] ?>"><?= $row['name'] . ' ' . $row['last_name'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label for="damaged_book_id">Select Book:</label>
          <select name="book_id" id="damaged_book_id" required>
            <option value="">Select a book</option>
            <?php 
              // If the damaged books were already fetched, list them
              if (isset($damagedBooksResult)) {
                  while ($row = $damagedBooksResult->fetch_assoc()) { ?>
                    <option value="<?= $row['id'] ?>">
                      <?= $row['title'] ?>
                    </option>
            <?php }
              }
            ?>
          </select>
        </div>
        <div class="form-group">
          <label for="reason">Fine Reason:</label>
          <select name="reason" id="reason" required>
            <option value="damaged">Damaged</option>
          </select>
        </div>
        <div class="form-group">
          <label for="amount">Fine Amount (Birr):</label>
          <input type="number" name="amount" id="amount" required placeholder="Enter fine amount in Birr" min="1" step="1">
        </div>
        <input type="hidden" name="book_type" value="damaged">
        <button type="submit" class="btn-submit">Enforce Fine</button>
      </form>
    </div>
  </div>
</body>
</html>
