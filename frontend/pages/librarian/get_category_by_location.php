<?php
require_once '../../../backend-php/database.php'; 

if (!isset($_GET['prefix'])) {
    echo json_encode(['error' => 'Location prefix is required']);
    exit;
}

$prefix = (int)$_GET['prefix'];

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

$category_id = null;

// Determine category based on prefix
foreach ($category_location_map as $category_name => $range) {
    if ($prefix >= $range[0] && $prefix <= $range[1]) {
        // Get category ID from the database
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->bind_result($category_id);
        $stmt->fetch();
        $stmt->close();
        break;
    }
}

if ($category_id) {
    echo json_encode(['category_id' => $category_id]);
} else {
    echo json_encode(['error' => 'No matching category found']);
}

$conn->close();
?>
