<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Books</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            padding-top: 20px;
        }
        
        .sidebar h4 {
            color: white;
            text-align: center;
        }
        
        .sidebar a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
        }
        
        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            margin-left: 260px; /* Adjust for sidebar */
        }

        .card {
            margin: 20px; /* Margin for top spacing */
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <h4>Admin Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="add_book.php">Add book</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="">Add Branch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="">View Branches</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="">Confirm Checkout</a>
        </li>
    </ul>
</div>

<!-- Main Content -->
<div class="content">
    <div class="container">
        <div class="row justify-content-center">
            <!-- View Books Card -->
            <div class="col-md-10 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>View Books</h5>
                    </div>
                    <div class="card-body">
                        <form action="view_books.php" method="post" class="mb-4">
                            <div class="form-row">
                                <div class="col">
                                    <input type="text" class="form-control" placeholder="Title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" placeholder="Author" name="author" value="<?php echo isset($author) ? htmlspecialchars($author) : ''; ?>">
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" placeholder="ISBN" name="isbn" value="<?php echo isset($isbn) ? htmlspecialchars($isbn) : ''; ?>">
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary" name="search">Search</button>
                                </div>
                            </div>
                        </form>
                        <?php if (!empty($message)): ?>
                            <div class='alert alert-info'><?php echo $message; ?></div>
                        <?php endif; ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>ISBN</th>
                                    <th>Publication Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($books)): ?>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($book['id']); ?></td>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                            <td><?php echo htmlspecialchars($book['published_year']); ?></td>
                                            <td>
                                                <a href="update_book.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="btn btn-warning btn-sm">Update</a>
                                                <a href="?delete_id=<?php echo htmlspecialchars($book['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No books found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>