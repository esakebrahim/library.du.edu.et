<?php
include_once '../../../backend-php/database.php';

$departmentCategories = [
    "Computer Science" => ["Computer science, information & general work", "Technology"],
    "Electrical Engineering" => ["Technology"],
    "Computer Engineering" => ["Computer science, information & general work", "Technology"],
    "Architecture" => ["Arts & recreation"],
    "Construction Technology and Management" => ["Technology"],
    "Civil Engineering" => ["Technology"],
    "Mechanical Engineering" => ["Technology"],
    "Automotive Engineering" => ["Technology"],
    "Water Resource and Irrigation Engineering" => ["Technology"],
    "Hydraulics and Water Resource Engineering" => ["Technology"],

    "Biology" => ["Science"],
    "Chemistry" => ["Science"],
    "Mathematics" => ["Science"],
    "Physics" => ["Science"],
    "Sport Science" => ["Arts & recreation"],
    "Geology" => ["Science"],
    "Statistics" => ["Science"],

    "Economics" => ["Social sciences"],
    "Accounting And Finance" => ["Social sciences"],
    "Logistics and Supply Chain Management" => ["Social sciences"],
    "Public Administration and Development Management" => ["Social sciences"],
    "Management" => ["Social sciences"],

    "Anesthesiology" => ["Science"],
    "Medical laboratory" => ["Science"],
    "Public Health" => ["Science"],
    "Environmental Health" => ["Science"],
    "Midwifery" => ["Science"],
    "Nursing" => ["Science"],
    "Psychiatry" => ["Philosophy and psychology"],
    "Pharmacy" => ["Science"],
    "Medicine" => ["Science"],

    "Agricultural Economics" => ["Social sciences"],
    "Animal and Range Science" => ["Science"],
    "Horticulture" => ["Science"],
    "Land Administration And Surveying" => ["Technology"],
    "Natural Resource and Management" => ["Science"],
    "Plant Science" => ["Science"],
    "Veterinary Science" => ["Science"],

    "Gedeo Language & Literature" => ["Language", "Literature"],
    "History & Heritage Management" => ["History & geography"],
    "Journalism & Communication" => ["Language"],
    "Social Anthropology" => ["Social sciences"],
    "Sociology" => ["Social sciences"],
    "Amharic Language & Literature" => ["Language", "Literature"],
    "Civics & Ethical Studies" => ["Social sciences"],
    "English Language & Literature" => ["Language", "Literature"],
    "Geography & Environmental Studies" => ["History & geography"],
    "Oromo Language & Literature" => ["Language", "Literature"],
];

$department = urldecode($_GET['department']);


if (!isset($departmentCategories[$department])) {
    echo "<tr><td class='no-data'>No books found for this department.</td></tr>";
    exit();
}

$categories = $departmentCategories[$department];
$categoryPlaceholders = implode(",", array_fill(0, count($categories), "?"));

$query = "
    SELECT books.title, books.author, books.status, books.location, books.price, library_branches.name AS name
    FROM books
    JOIN categories ON books.category_id = categories.id
    JOIN library_branches ON books.branch_id = library_branches.id
    WHERE categories.name IN ($categoryPlaceholders)
    ORDER BY books.title ASC
";


$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat("s", count($categories)), ...$categories);
$stmt->execute();
$result = $stmt->get_result();

echo "<tr>
        <th>Title</th>
        <th>Author</th>
        <th>Status</th>
        <th>Location</th>
        <th>Price</th>
         <th>branch</th>
      </tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
    echo "<tr><td colspan='6' class='no-data'>No available books found.</td></tr>";
}

$conn->close();
?>
