<?php
require_once '../../../backend-php/database.php';  // Ensure this connects to your database

// Check if category ID is provided
if (!isset($_GET['category_id'])) {
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

$category_id = (int)$_GET['category_id'];

// Fetch category name
$sql = "SELECT name FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$stmt->bind_result($category_name);
$stmt->fetch();
$stmt->close();

$category_location_map = [
    "Arts & Recreation" => [0, 99],
    "Computer Science, Information & General Work" => [100, 199],
    "History & Geography" => [200, 299],
    "Technology" => [300, 399],
    "Language" => [400, 499],
    "Literature" => [500, 599],
    "Philosophy & Psychology" => [600, 699],
    "Religion" => [700, 799],
    "Science" => [800, 899],
    "Social Sciences" => [900, 999]
];

// Check if the category exists in the mapping
if (isset($category_location_map[$category_name])) {
    $location_range = $category_location_map[$category_name];
    $first_three_digits = rand($location_range[0], $location_range[1]); // Pick random value in range
    echo json_encode(['location_prefix' => $first_three_digits]);
} else {
    echo json_encode(['error' => 'Invalid category']);
}

$conn->close();
?>
