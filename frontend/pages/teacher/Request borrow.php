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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Books</title>
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
            display: flex;
            gap: 2rem;
            background: #f3f4f6;
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
                flex-direction: column;
            }

            .section {
                min-height: auto;
                margin-bottom: 1rem;
            }

            .available-section, .reserved-section {
                max-height: 600px;
            }
        }

        .section {
            flex: 1;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            background: var(--white);
            transition: var(--transition);
            min-height: calc(100vh - 4rem);
        }

        .section h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-bottom: none;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .book-container {
            background-color: var(--white);
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            margin: 1rem 0;
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .book-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-color: var(--primary);
        }

        .book-info {
            flex-grow: 1;
            font-size: 0.95rem;
        }

        .book-info span {
            display: block;
            margin: 0.5rem 0;
            color: var(--dark);
        }

        .book-info strong {
            color: var(--primary-dark);
            font-weight: 600;
            margin-right: 0.5rem;
        }

        input[type="submit"] {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        input[type="submit"]:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-box {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
            position: relative;
        }

        .search-box .search-icon {
            position: absolute;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
            pointer-events: none;
        }

        .search-box .loader {
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 2px solid var(--light);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

        .search-box input {
            width: 100%;
            max-width: 500px;
            padding: 1rem 3.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            outline: none;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f9fafb;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            background: var(--white);
        }

        .available-section, .reserved-section {
            overflow-y: auto;
            max-height: calc(100vh - 8rem);
            padding-right: 1rem;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) #f3f4f6;
        }

        .available-section::-webkit-scrollbar, .reserved-section::-webkit-scrollbar {
            width: 6px;
        }

        .available-section::-webkit-scrollbar-track, .reserved-section::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 3px;
        }

        .available-section::-webkit-scrollbar-thumb, .reserved-section::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 3px;
        }

        .no-books {
            text-align: center;
            color: var(--secondary);
            font-size: 1rem;
            padding: 2rem;
            background: #f9fafb;
            border-radius: 0.75rem;
            border: 1px dashed #e5e7eb;
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
        <a href="search_books.php">
            <i class="fas fa-search"></i> Search Books
        </a>
        <a href="Request borrow.php" class="active">
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
        <!-- Reserved Books Section -->
        <div class="section reserved-section">
            <h2>Your Reserved Books</h2>
            <?php
            $reservedQuery = "SELECT DISTINCT b.id, r.reservation_id, b.title, b.author, b.status 
                              FROM reservation r 
                              JOIN books b ON r.book_id = b.id 
                              WHERE r.user_id = ? AND r.status = 'active' AND b.status = 'reserved'";
            $reservedStmt = $conn->prepare($reservedQuery);
            $reservedStmt->bind_param("i", $user_id);
            $reservedStmt->execute();
            $reservedResult = $reservedStmt->get_result();

            if ($reservedResult->num_rows > 0) {
                while ($row = $reservedResult->fetch_assoc()) {
                    echo "<div class='book-container'>";
                    echo "<div class='book-info'>";
                    echo "<span><strong>Title:</strong> {$row['title']}</span>";
                    echo "<span><strong>Author:</strong> {$row['author']}</span>";
                    echo "<span><strong>Status:</strong> {$row['status']}</span>";
                    echo "</div>";
                    echo "<form action='borrow.php' method='post' onsubmit='return confirmBorrow();'>";
                    echo "<input type='hidden' name='user_id' value='{$user_id}'>";
                    echo "<input type='hidden' name='book_id' value='{$row['id']}'>";
                    echo "<input type='hidden' name='reservation_id' value='{$row['reservation_id']}'>";
                    echo "<input type='hidden' name='is_reserved' value='true'>";
                    echo "<input type='submit' value='Borrow' />";
                    echo "</form>";
                    echo "</div>";
                }
            } else {
                echo "<p class='no-books'>You have no reserved books.</p>";
            }
            ?>
        </div>

        <!-- Available Books Section -->
        <div class="section available-section">
            <h2>Available Books</h2>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search by title or author">
                <div class="loader" id="searchLoader"></div>
            </div>

            <div id="availableBooks">
                <?php
                $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";
                $availableQuery = "SELECT DISTINCT b.id, b.title, b.author, b.status 
                                   FROM books b 
                                   LEFT JOIN borrow_requests br ON b.id = br.book_id 
                                   WHERE b.status = 'available' 
                                   AND (
                                       br.book_id IS NULL 
                                       OR br.status IS NULL
                                       OR br.status = 'return_accept'
                                       OR br.status = 'borrow_reject'
                                   )
                                   AND NOT EXISTS (
                                       SELECT 1 FROM borrow_requests br2 
                                       WHERE br2.book_id = b.id AND br2.status = 'borrow_pending'
                                   )
                                   AND (b.title LIKE ? OR b.author LIKE ?)
                                   ORDER BY b.title ASC";
                $availableStmt = $conn->prepare($availableQuery);
                $availableStmt->bind_param("ss", $search, $search);
                $availableStmt->execute();
                $availableResult = $availableStmt->get_result();

                if ($availableResult->num_rows > 0) {
                    while ($row = $availableResult->fetch_assoc()) {
                        echo "<div class='book-container'>";
                        echo "<div class='book-info'>";
                        echo "<span><strong>Title:</strong> {$row['title']}</span>";
                        echo "<span><strong>Author:</strong> {$row['author']}</span>";
                        echo "<span><strong>Status:</strong> {$row['status']}</span>";
                        echo "</div>";
                        echo "<form action='borrow.php' method='post' onsubmit='return confirmBorrow();'>";
                        echo "<input type='hidden' name='user_id' value='{$user_id}'>";
                        echo "<input type='hidden' name='book_id' value='{$row['id']}'>";
                        echo "<input type='hidden' name='is_reserved' value='false'>";
                        echo "<input type='submit' value='Borrow' />";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='no-books'>All available books are reserved.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const searchInput = document.getElementById("searchInput");
            const searchLoader = document.getElementById("searchLoader");
            let searchTimeout;

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

            // Enhanced live search functionality
            searchInput.addEventListener("input", function() {
                const query = this.value.trim();
                
                // Show loader
                searchLoader.style.display = 'block';
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Set new timeout for debouncing
                searchTimeout = setTimeout(() => {
                    // Create FormData object
                    const formData = new FormData();
                    formData.append('query', query);

                    // Fetch API for better error handling
                    fetch('borrow_search.php?query=' + encodeURIComponent(query))
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(data => {
                            document.getElementById("availableBooks").innerHTML = data;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById("availableBooks").innerHTML = 
                                '<p class="no-books">Error searching books. Please try again.</p>';
                        })
                        .finally(() => {
                            // Hide loader
                            searchLoader.style.display = 'none';
                        });
                }, 300); // Debounce time of 300ms
            });
        });

        function confirmBorrow() {
            var rules = "Library Borrowing Rules:\n\n" +
                        "1. Borrow up to 5 books at a time.\n" +
                        "2. Return books within 14 days or pay 1 birr per day.\n" +
                        "3. Handle books carefully; damage or loss must be compensated.\n" +
                        "4. Reserved books must be borrowed within 24 hours.\n" +
                        "5. Violating rules may lead to suspension of borrowing privileges.";
            return confirm(rules + "\n\nDo you accept these rules?");
        }
    </script>
</body>
</html>