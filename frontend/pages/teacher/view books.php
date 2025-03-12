<?php
session_start();
include_once '../../../backend-php/database.php';

if (!isset($_SESSION['teacher_id'])) {
    die("Access denied. You must log in as a Teacher.");
}

$user_id = $_SESSION['teacher_id'];

// Fetch unread notifications count
$notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? and status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notification_count = $notif_row['count'];

// Handle AJAX search request
if(isset($_POST['search'])) {
    $search = '%' . $_POST['search'] . '%';
    $sql = "SELECT distinct books.id, books.title, books.author FROM books
            LEFT JOIN borrow_requests ON books.id = borrow_requests.book_id 
            WHERE books.status='available' 
            AND (borrow_requests.book_id is NULL 
                OR borrow_requests.status='borrow_reject' 
                OR borrow_requests.status='return_accept')
            AND (books.title LIKE ? OR books.author LIKE ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $books = array();
    while($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    
    echo json_encode($books);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

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

        .content-wrapper {
            background: var(--white);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .search-container {
            margin-bottom: 2rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th {
            background-color: var(--primary);
            color: var(--white);
            font-weight: 500;
            text-align: left;
            padding: 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--secondary);
        }

        tr:hover td {
            background-color: rgba(79, 70, 229, 0.05);
        }

        .action-btn {
            background-color: var(--primary);
            color: var(--white);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .action-btn:hover {
            background-color: var(--primary-dark);
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            color: var(--white);
            padding: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            z-index: 1001;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .container {
                margin-left: 0;
                max-width: 100%;
            }

            .mobile-menu-toggle {
                display: block;
            }
        }

        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }

            .content-wrapper {
                padding: 1.5rem;
            }
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
        <div class="content-wrapper">
            <h2>Available Books</h2>
            
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Search by title or author...">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody">
                    <?php
                    // Fetch available books
                    $sql = "SELECT books.id, books.title, books.author FROM books
                            LEFT JOIN borrow_requests ON books.id = borrow_requests.book_id 
                            WHERE books.status='available' 
                            AND (borrow_requests.book_id is NULL 
                                OR borrow_requests.status='borrow_reject' 
                                OR borrow_requests.status='return_accept')";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['title']}</td>
                                    <td>{$row['author']}</td>
                                    <td>
                                        <form method='POST' action='reserve_book.php'>
                                            <input type='hidden' name='id' value='{$row['id']}'>
                                            <button type='submit' class='action-btn'>Reserve</button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align: center;'>No available books</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const sidebar = document.querySelector('.sidebar');

            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024) {
                    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });

            // Live search functionality
            const searchInput = document.getElementById('searchInput');
            const booksTableBody = document.getElementById('booksTableBody');

            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const searchTerm = this.value;
                    
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'search=' + encodeURIComponent(searchTerm)
                    })
                    .then(response => response.json())
                    .then(books => {
                        booksTableBody.innerHTML = '';
                        if (books.length > 0) {
                            books.forEach(book => {
                                booksTableBody.innerHTML += `
                                    <tr>
                                        <td>${book.id}</td>
                                        <td>${book.title}</td>
                                        <td>${book.author}</td>
                                        <td>
                                            <form method='POST' action='reserve_book.php'>
                                                <input type='hidden' name='id' value='${book.id}'>
                                                <button type='submit' class='action-btn'>Reserve</button>
                                            </form>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            booksTableBody.innerHTML = `
                                <tr>
                                    <td colspan='4' style='text-align: center;'>No books found</td>
                                </tr>
                            `;
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }, 300); // Debounce delay
            });
        });
    </script>
</body>
</html>