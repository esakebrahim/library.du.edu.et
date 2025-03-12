<?php
session_start();
include_once '../../../backend-php/database.php';

if (!isset($_SESSION['teacher_id'])) {
    die("Access denied. You must log in as a Teacher.");
}

$user_id = $_SESSION['teacher_id'];

// Fetch notification count for the badge
$notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? and status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notification_count = $notif_row['count'];

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --light: #f8fafc;
            --dark: #1e293b;
            --white: #ffffff;
            --sidebar-width: 280px;
            --header-height: 70px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(to bottom, var(--dark), #2d3748);
            color: var(--white);
            padding: 1.5rem;
            transition: var(--transition);
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 1rem 0 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--white);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            font-weight: 500;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            transform: translateX(5px);
        }

        .sidebar a i {
            width: 1.5rem;
            margin-right: 1rem;
            font-size: 1.1rem;
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.08);
            color: var(--white);
        }

        .badge {
            background: var(--danger);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            font-size: 0.75rem;
            margin-left: auto;
        }

        .container {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            max-width: calc(100% - var(--sidebar-width));
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            color: var(--white);
            padding: 0.75rem;
            border-radius: 0.5rem;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .mobile-menu-toggle:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        @media (max-width: 1024px) {
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .container {
                margin-left: 0;
                max-width: 100%;
                padding: 1rem;
            }
        }

        .dropdown-container {
            position: absolute;
            top: 280%;
            right: 220px;
            transform: translateY(-50%);
            background-color: white; /* Ensures dropdown does not appear transparent */
            /* Keeps it above other elements */
            padding: 5px;
            border-radius: 5px;
        }

        .dropdown {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        h1 {
            color: #007bff;
            margin: 20px 0;
            text-align: left;
        }

        .table-container {
            
            width: 1200px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        #searchInput {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
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
    </style>
</head>
<body>
    <div class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </div>

    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Library System</h2>
        </div>
        <a href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="../../../CRONE/notification.php">
            <i class="fas fa-bell"></i> Notifications
            <?php if ($notification_count > 0): ?>
                <span class="badge"><?php echo $notification_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="feedback_submission.php">
            <i class="fas fa-comment"></i> Feedback
        </a>
        <a href="search_books.php" class="active">
            <i class="fas fa-search"></i> Search Books
        </a>
        <a href="Request borrow.php">
            <i class="fas fa-arrow-right"></i> Borrow Books
        </a>
        <a href="request_return.php">
            <i class="fas fa-arrow-left"></i> Return Books
        </a>
        <a href="view books.php">
            <i class="fas fa-calendar-check"></i> Reservations
        </a>
        <a href="borrowing history.php">
            <i class="fas fa-list"></i> View Borrowed Books
        </a>
        <a href="Request_extension.php">
            <i class="fas fa-clock"></i> Request Extension
        </a>
        <a href="../../login.html">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="container">
        <header>
            <div class="dropdown-container">
                <select id="collegeDropdown" class="dropdown" onchange="populateDepartments()">
                    <option value="">Select College</option>
                    <?php foreach ($colleges as $college => $departments): ?>
                        <option value="<?= $college ?>"><?= $college ?></option>
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
    </div>

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

        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');

            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024) {
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
