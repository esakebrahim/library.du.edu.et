<?php
session_start();
include_once '../../../backend-php/database.php'; 

// Get unread notifications count
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread' and type='general'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

// Get unread feedback count
$sql = "SELECT COUNT(*) AS feedback_count FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$feedbackCount = $row['feedback_count'] ?? 0;
$stmt->close();

// Get latest unread notification ID
$sql = "SELECT id FROM notifications WHERE user_id = ? AND status = 'unread' AND type = 'feedback' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$notificationId = $row['id'] ?? null;
$stmt->close();

$availableBooksQuery = "
    SELECT b.id, b.title, b.author, b.status, b.location, b.price, lb.name 
    FROM books b
    JOIN library_branches lb ON b.branch_id = lb.id
    ORDER BY b.title ASC
";
$availableBooksStmt = $conn->prepare($availableBooksQuery);
$availableBooksStmt->execute();
$availableBooksResult = $availableBooksStmt->get_result();



// Colleges and Departments
$colleges = [
    "Engineering & Technology" => [
        "Computer Science",
        "Electrical Engineering",
        "Computer Engineering",
        "Architecture",
        "Construction Technology and Management",
        "Civil Engineering",
        "Mechanical Engineering",
        "Automotive Engineering",
        "Water Resource and Irrigation Engineering",
        "Hydraulics and Water Resource Engineering"
    ],
    "College of Natural & Computational Science" => [
        "Biology",
        "Chemistry",
        "Mathematics",
        "Physics",
        "Sport Science",
        "Geology",
        "Statistics"
       
    ],
    "College of Business & Economics" => [
        "Economics",
        "Accounting And Finance",
        "Logistics and Supply Chain Management",
        "Public Administration and Development Management",
        "Management"
        
      
    ],
    "College of Medicine & Health Science" => [
        "Anesthesiology",
        "Medical laboratory",
        "Public Health",
        "Environmental Health",
        " Midwifery",
        "Nursing",
        "Psychiatry",
        "Pharmacy",
        "Medicine"
      
    ],
    "College of Agriculture & Natural Resource" => [
        "Agricultural Economics",
        "Animal and Range Science",
        "Horticulture",
        "Land Administration And Surveying",
        "Natural Resource and Management",
        "Plant Science",
        "Veterinary Science"
       
    ],
    "College of Social Science and Humanities" => [
      
        "Gedeo Language & Literature",
        "History & Heritage Management",
        "Journalism & Communication",
        "Social Anthropology",
        "Sociology",
        "Amharic Language & Literature",
        "Civics & Ethical Studies",
        "English Language & Literature",
        "Geography & Environmental Studies",
        "Oromo Language & Literature"
       
    ],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #343a40;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* Header Styles */
        header {
            width: 100%;
            background: linear-gradient(120deg, #2563eb, #4f46e5);
            color: white;
            padding: 2rem;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-left: 2rem;
        }

        .dropdown-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            gap: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-right: 2rem;
        }

        .dropdown {
            min-width: 220px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border-radius: 10px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.9);
            color: #1f2937;
            transition: all 0.3s ease;
            cursor: pointer;
            outline: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dropdown:hover {
            border-color: rgba(255, 255, 255, 0.4);
            background: white;
            transform: translateY(-1px);
        }

        .dropdown:focus {
            border-color: white;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
        }

        h1 {
            color: #4361ee;
            margin: 1.5rem 0;
            text-align: center;
            font-size: 1.8rem;
            font-weight: 600;
            letter-spacing: -0.025em;
        }

        .table-container {
            width: 70%;
            max-width: 1200px;
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            margin-left: 280px;
            border: 1px solid #e5e7eb;
        }

        #searchInput {
            width: 100%;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s ease;
        }

        #searchInput:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        .no-data {
            text-align: center;
            font-size: 1.2em;
            color: #6c757d;
            margin: 20px;
        }

        /* Sidebar Styles */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            padding-top: 1.5rem;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar h4 {
            text-align: center;
            font-weight: 700;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.9);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0.2rem 0.8rem;
            border-radius: 10px;
        }

        .sidebar a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .notification-badge {
            background-color: #f72585;
            color: white;
            border-radius: 20px;
            padding: 0.25rem 0.6rem;
            font-size: 0.75rem;
            margin-left: auto;
            font-weight: 600;
        }

        /* Mobile Toggle Button */
        .toggle-sidebar {
            display: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .toggle-sidebar {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1001;
                background: #4361ee;
                color: white;
                padding: 0.5rem;
                border-radius: 5px;
                cursor: pointer;
            }

            .dropdown-container {
                position: static;
                transform: none;
                margin: 1rem auto;
                padding: 1rem;
                flex-direction: column;
                width: calc(100% - 2rem);
                max-width: 400px;
            }

            .dropdown {
                width: 100%;
                margin: 0;
            }

            header {
                flex-direction: column;
                padding: 1.5rem 1rem;
                gap: 1rem;
            }

            .header-title {
                margin-left: 0;
                font-size: 1.5rem;
            }

            .table-container {
                margin: 1rem;
                padding: 1.5rem;
                width: calc(100% - 2rem);
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <div class="toggle-sidebar d-lg-none">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4><i class="fas fa-book-reader"></i> Student Portal</h4>
        <a href="First Page.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="view books.php"><i class="fas fa-book"></i> View Available Books</a>
        <a href="Request borrow.php"><i class="fas fa-hand-holding"></i> Borrow a Book</a>
        <a href="request_return.php"><i class="fas fa-undo"></i> Request Return</a>
        <a href="borrowing history.php"><i class="fas fa-history"></i> My Borrowing History</a>
        <a class="active" href="#"><i class="fas fa-search"></i> Search for Books</a>
        <a href="Display Fine.php"><i class="fas fa-wallet"></i> Payment</a>
        <a href="report_lost.php"><i class="fas fa-exclamation-triangle"></i> Report Lost Book</a>
        <a href="profile_settings.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
        <a href="notifications.php">
            <i class="fas fa-bell"></i> Notifications
            <?php if (isset($notifications_count) && $notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="feedback_submission.php?notification_id=<?php echo $notificationId ?? ''; ?>">
            <i class="fas fa-comment-alt"></i> Feedback
            <?php if (isset($feedbackCount) && $feedbackCount > 0): ?>
                <span class="notification-badge"><?php echo $feedbackCount; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
  
   
    </div>

    <header>
        <div class="header-title">Search Books</div>
        <div class="dropdown-container">
            <select id="collegeDropdown" class="dropdown" onchange="populateDepartments()">
                <option value="">Select College</option>
                <?php foreach ($colleges as $college => $departments): ?>
                    <option value="<?= htmlspecialchars($college) ?>"><?= htmlspecialchars($college) ?></option>
                <?php endforeach; ?>
            </select>

            <select id="departmentDropdown" class="dropdown" onchange="fetchBooksByDepartment()">
                <option value="">Select Department</option>
            </select>
        </div>
    </header>

    <div class="table-container">
        <h1>Available Books</h1>
        
        <input type="text" id="searchInput" onkeyup="searchBooks()" placeholder="Search by title or author...">
        
        <table id="booksTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($availableBooksResult->num_rows > 0) {
                    while ($row = $availableBooksResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['title']}</td>";
                        echo "<td>{$row['author']}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td>{$row['location']}</td>";
                        echo "<td>{$row['price']}</td>";
                        echo "<td>{$row['name']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='no-data'>No available books found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const colleges = <?php echo json_encode($colleges); ?>;
        
        function populateDepartments() {
            const collegeDropdown = document.getElementById("collegeDropdown");
            const departmentDropdown = document.getElementById("departmentDropdown");
            const selectedCollege = collegeDropdown.value;

            departmentDropdown.innerHTML = "<option value=''>Select Department</option>";

            if (selectedCollege && colleges[selectedCollege]) {
                colleges[selectedCollege].forEach(department => {
                    const option = document.createElement("option");
                    option.value = department;
                    option.textContent = department;
                    departmentDropdown.appendChild(option);
                });
            }
        }

        function searchBooks() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let table = document.getElementById("booksTable");
            let rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) { 
                let title = rows[i].getElementsByTagName("td")[0].innerText.toLowerCase();
                let author = rows[i].getElementsByTagName("td")[1].innerText.toLowerCase();
                
                if (title.includes(input) || author.includes(input)) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        function fetchBooksByDepartment() {
            let department = document.getElementById("departmentDropdown").value;

            if (department) {
                fetch(`fetch_books.php?department=${encodeURIComponent(department)}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById("booksTable").innerHTML = data;
                    })
                    .catch(error => console.error("Error fetching books:", error));
            }
        }

        $(document).ready(function() {
            $('.toggle-sidebar').click(function() {
                $('.sidebar').toggleClass('active');
            });

            // Close sidebar when clicking outside on mobile
            $(document).click(function(event) {
                if (!$(event.target).closest('.sidebar, .toggle-sidebar').length) {
                    $('.sidebar').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
