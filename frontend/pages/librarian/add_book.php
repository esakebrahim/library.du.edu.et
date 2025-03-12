<?php
session_start();
include '../../../backend-php/database.php';

if (!isset($_SESSION['librarian_id'])) {
    header("Location: ../../login.html");
    exit();
}

$librarianName = $_SESSION['librarian_name'];
$Id = $_SESSION['librarian_id'];

$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $Id);
$stmt->execute();
$result = $stmt->get_result();
$notifications_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

$pendingFeedbackCount = 0;
$sql = "SELECT COUNT(*) AS pending_count FROM feedback WHERE status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$pendingFeedbackCount = $row['pending_count']; 
$stmt->close();

$sql_role = "SELECT role_name FROM librarian_roles r JOIN users u ON r.id = u.role_id WHERE u.id = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param("i", $Id);
$stmt_role->execute();
$stmt_role->bind_result($role_name);
$stmt_role->fetch();
$stmt_role->close();

// Fetch categories for the dropdown
$categories = [];
$stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --warning: #fbbf24;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-800: #1e293b;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--gray-100);
        }

        .sidebar {
            min-height: 100vh;
            background: var(--gray-800);
            color: var(--white);
            padding: 1.5rem;
            width: 250px;
        }

        .sidebar h2 {
            font-size: 1.25rem;
            font-weight: 600;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar a {
            color: var(--gray-200);
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            transform: translateX(4px);
        }

        .sidebar a i {
            width: 1.5rem;
            margin-right: 0.75rem;
        }

        .notification-badge {
            background: var(--danger);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            float: right;
        }

        .content {
            flex: 1;
            padding: 2rem;
            background: var(--gray-100);
        }

        .card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }

        .form-control {
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
            padding: 0.75rem 1rem;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .alert {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .dropdown-toggle::after {
            float: right;
            margin-top: 8px;
        }

        .submenu {
            display: none;
            list-style: none;
            padding-left: 2rem;
        }

        .dropdown.active .submenu {
            display: block;
        }

        .submenu a {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .d-flex {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                min-height: auto;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar">
            <h2>Librarian Dashboard</h2>
            <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>

            <?php
            if ($role_name == 'Cataloging') {
                echo '<a href="add_category.php"><i class="fas fa-plus"></i> Add Category</a>';
                echo '<a href="../../../backend-php/add_book.php"><i class="fas fa-plus"></i> Add Books</a>';
                echo '<a href="../../../backend-php/view_books.php"><i class="fas fa-book"></i> Manage Books</a>';
            } elseif ($role_name == 'Circulation') {
                echo '<a href="../../../backend-php/pending books.php"><i class="fas fa-arrow-right"></i> Issue Book</a>';
                echo '<a href="confirm return.php"><i class="fas fa-arrow-left"></i> Return Book</a>';
                echo '<a href="manage_extensions.php"><i class="fas fa-arrow-right"></i>Manage extension</a>';

                echo '<div class="dropdown">';
                echo '<a href="#" class="dropdown-toggle"><i class="fas fa-money-bill-wave"></i> Payment <i class="fas fa-chevron-down float-right"></i></a>';
                echo '<ul class="submenu">';
                echo '<li><a href="confirm_fine.php"><i class="fas fa-check-circle"></i> Confirm Fine</a></li>';
                echo '<li><a href="enforce_fine.php"><i class="fas fa-gavel"></i> Enforce Fine</a></li>';
                echo '</ul>';
                echo '</div>';
               
            } elseif ($role_name == 'Acquisition') {
                echo '<a href=""><i class="fas fa-book-open"></i> Manage Acquisitions</a>';
            }

            if ($pendingFeedbackCount > 0) {
                echo '<a href="feedback display.php"><i class="fas fa-comments"></i> View Feedback <span class="badge badge-danger float-right">' . $pendingFeedbackCount . '</span></a>';
            }
            ?>
            <a href="../librarian/search_books.php"><i class="fas fa-search"></i> Search Book</a>

            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications
            <?php if ($notifications_count > 0): ?>
                <span class="notification-badge"><?php echo $notifications_count; ?></span>
            <?php endif; ?>
            </a>
            <a href="../../login.html"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="content">
            <div class="card">
                <h2>Add New Book</h2>
                
                <div id="alert-container"></div>

                <form id="addBookForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="title" class="form-label">Title:</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="author" class="form-label">Author:</label>
                                <input type="text" class="form-control" id="author" name="author" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="isbn" class="form-label">ISBN:</label>
                                <input type="text" class="form-control" id="isbn" name="isbn" required pattern="[0-9]{13}" title="Please enter a valid 13-digit ISBN">
                            </div>

                            <div class="form-group mb-3">
                                <label for="category_id" class="form-label">Category:</label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="published_year" class="form-label">Published Year:</label>
                                <input type="number" class="form-control" id="published_year" name="published_year" required min="1800" max="<?php echo date('Y') + 1; ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label for="edition" class="form-label">Edition:</label>
                                <input type="number" class="form-control" id="edition" name="edition" required min="1">
                            </div>

                            <div class="form-group mb-3">
                                <label for="price" class="form-label">Price:</label>
                                <input type="number" class="form-control" id="price" name="price" required min="0" step="0.01">
                            </div>

                            <div class="form-group mb-3">
                                <label for="location" class="form-label">Location:</label>
                                <input type="text" class="form-control" id="location" name="location" required placeholder="e.g., 25.231 edition1 2025 C.1">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Book</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Dropdown functionality
            let dropdowns = document.querySelectorAll(".dropdown");
            dropdowns.forEach(dropdown => {
                let toggle = dropdown.querySelector(".dropdown-toggle");
                toggle.addEventListener("click", function(event) {
                    event.preventDefault();
                    dropdown.classList.toggle("active");
                });
            });

            // Form submission
            const form = document.getElementById('addBookForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                fetch('../../../backend-php/add_book.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const alertContainer = document.getElementById('alert-container');
                    alertContainer.innerHTML = `
                        <div class="alert alert-${data.status === 'success' ? 'success' : 'danger'}">
                            ${data.message}
                        </div>
                    `;
                    
                    if (data.status === 'success') {
                        form.reset();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertContainer = document.getElementById('alert-container');
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger">
                            An error occurred while adding the book. Please try again.
                        </div>
                    `;
                });
            });
        });
    </script>
</body>
</html>