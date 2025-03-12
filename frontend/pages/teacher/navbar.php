<!-- navbar.php -->
<nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
    <div class="container">
        <a class="navbar-brand" href="teacher_dashboard.php">📚 Library System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_books.php">View Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="search_books.php">Search Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="borrow_books.php">Borrow Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php">Reservations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_borrowed_books.php">View Borrowed Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="request_extension.php">Request Extension</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link logout-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .navbar {
    background-color: #007bff !important; /* Make navbar blue */
    padding: 10px 20px; /* Reduce extra space */
}

.navbar .navbar-nav .nav-link {
    color: white !important; /* Ensure text is white */
    font-weight: 500;
}

.navbar .navbar-nav .nav-link:hover {
    color: #dcdcdc !important; /* Slightly lighter hover effect */
}

.navbar-toggler {
    border: none; /* Remove default border */
}

.navbar-toggler-icon {
    background-color: white; /* Ensure icon is visible */
}

.collapse {
    background: #007bff; /* Make mobile menu background blue */
    padding: 10px;
}

/* 🔵 Custom Navbar Styling */
.custom-navbar {
    background: linear-gradient(45deg, #1e3c72, #2a5298);
    padding: 8px 20px; /* Reduced padding */
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

/* 🔹 Navbar Brand */
.navbar-brand {
    font-size: 1.4rem;
    font-weight: bold;
    color: #ffffff;
}

/* 🔹 Navbar Links */
.navbar-nav .nav-link {
    color: #ffffff;
    font-weight: 500;
    transition: color 0.3s ease-in-out, transform 0.2s;
    font-size: 1rem;
    padding: 10px 12px; /* Smaller padding to fix height */
    border-radius: 5px;
}

/* 🔹 Hover Effect */
.navbar-nav .nav-link:hover {
    color: #ffcc00;
    background-color: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
}

/* 🔹 Fix for Mobile Navbar (Background Visibility) */
.navbar-collapse {
    background: linear-gradient(45deg, #1e3c72, #2a5298);
    padding: 10px;
}

/* 🔹 Logout Button */
.logout-link {
    background: rgba(255, 0, 0, 0.7);
    padding: 8px 12px;
    border-radius: 5px;
    font-weight: bold;
    transition: 0.3s;
}

.logout-link:hover {
    background: #ff0000;
    color: #fff;
    transform: scale(1.05);
}

/* 🔹 Navbar Toggler Button */
.navbar-toggler {
    border: none;
    outline: none;
}

.navbar-toggler:focus {
    box-shadow: none;
}

/* 🔹 Mobile Adjustments */
@media (max-width: 991px) {
    .navbar-nav {
        text-align: center;
    }
    .navbar-nav .nav-link {
        display: block;
        width: 100%;
        padding: 12px;
    }
}
</style>
