<?php
session_start();
include_once '../../../backend-php/database.php'; 

$availableBooksQuery = "
    SELECT id, title, author, status 
    FROM books 
    ORDER BY title ASC
";
$availableBooksStmt = $conn->prepare($availableBooksQuery);
$availableBooksStmt->execute();
$availableBooksResult = $availableBooksStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Books</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #343a40;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        header {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 15px;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background-color: #495057;
        }

        h1 {
            color: #007bff;
            margin: 20px 0;
            text-align: center;
        }

        .table-container {
            width: 90%;
            max-width: 1000px;
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
    <header>
        <a href="../../login.html" class="back-btn">⬅ Back</a>
        Available Books
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
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='no-data'>No available books found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
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
    </script>
</body>
</html>

<?php
$conn->close();
?>
