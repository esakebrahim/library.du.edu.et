<?php
session_start();
require_once '../../../backend-php/database.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Validate report type
$report_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$allowed_types = ['borrowings', 'users', 'books', 'feedback'];

if (!in_array($report_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid report type']);
    exit();
}

try {
    // Get preview data based on report type
    switch ($report_type) {
        case 'borrowings':
            $query = "
                SELECT 
                    b.title,
                    u.name as borrower_name,
                    u.type as borrower_type,
                    lb.name as branch_name,
                    br.borrow_date,
                    br.return_date,
                    br.status
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                JOIN users u ON br.user_id = u.id
                JOIN library_branches lb ON br.branch_id = lb.id
                ORDER BY br.borrow_date DESC
                LIMIT 5
            ";
            break;

        case 'users':
            $query = "
                SELECT 
                    u.name,
                    u.email,
                    u.type,
                    u.status,
                    u.created_at,
                    COUNT(DISTINCT br.id) as total_borrows,
                    COUNT(DISTINCT f.id) as total_feedback
                FROM users u
                LEFT JOIN borrows br ON u.id = br.user_id
                LEFT JOIN feedback f ON u.id = f.user_id
                GROUP BY u.id
                ORDER BY u.created_at DESC
                LIMIT 5
            ";
            break;

        case 'books':
            $query = "
                SELECT 
                    b.title,
                    b.author,
                    b.isbn,
                    b.status,
                    lb.name as branch_name,
                    COUNT(DISTINCT br.id) as total_borrows,
                    COUNT(DISTINCT f.id) as total_feedback
                FROM books b
                JOIN library_branches lb ON b.branch_id = lb.id
                LEFT JOIN borrows br ON b.id = br.book_id
                LEFT JOIN feedback f ON b.id = f.book_id
                GROUP BY b.id
                ORDER BY b.title
                LIMIT 5
            ";
            break;

        case 'feedback':
            $query = "
                SELECT 
                    f.feedback_text,
                    f.rating,
                    f.created_at,
                    u.name as user_name,
                    u.type as user_type,
                    b.title as book_title,
                    lb.name as branch_name
                FROM feedback f
                JOIN users u ON f.user_id = u.id
                JOIN books b ON f.book_id = b.id
                JOIN library_branches lb ON b.branch_id = lb.id
                ORDER BY f.created_at DESC
                LIMIT 5
            ";
            break;
    }

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates and truncate long text
        foreach ($row as $key => $value) {
            if (strpos($key, 'date') !== false || strpos($key, 'created_at') !== false) {
                $row[$key] = date('Y-m-d', strtotime($value));
            } elseif (strpos($key, 'text') !== false && strlen($value) > 100) {
                $row[$key] = substr($value, 0, 100) . '...';
            }
        }
        $data[] = $row;
    }

    // Set response headers
    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
} 