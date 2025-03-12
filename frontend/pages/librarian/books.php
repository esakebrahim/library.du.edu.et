<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            background-color: #f4f7fa;
        }

        .card-header {
            background-color: #0069d9;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .btn-back {
            font-size: 1rem;
            color: #0069d9;
            border: 1px solid #0069d9;
        }

        .btn-back:hover {
            background-color: #0069d9;
            color: white;
        }

        .form-group label {
            font-weight: 600;
        }

        .form-control {
            border-radius: 0.375rem;
            box-shadow: none;
            border: 1px solid #ccc;
        }

        .form-control:focus {
            border-color: #0069d9;
            box-shadow: 0 0 5px rgba(0, 105, 217, 0.5);
        }

        .alert {
            font-weight: bold;
            border-radius: 0.375rem;
        }

        .card-body {
            padding: 2rem;
        }
    </style>

    <script>
 document.addEventListener("DOMContentLoaded", function () {
    const categorySelect = document.getElementById("category_id");
    const locationInput = document.getElementById("location");
    const titleInput = document.getElementById("title");
    const authorInput = document.getElementById("author");
    const isbnInput = document.getElementById("isbn");
    const priceInput = document.getElementById("price");
    const publishedYearInput = document.getElementById("published_year");
    const currentYear = new Date().getFullYear();

    // Mapping categories to location ranges
    const categoryLocationMap = {
        "Computer Science, Information & General Work": [0, 99],
        "Philosophy & Psychology": [100, 199],
        "Religion": [200, 299],
        "Social Sciences": [300, 399],
        "Language": [400, 499],
        "Science": [500, 599],
        "Technology": [600, 699],
        "Arts & Recreation": [700, 799],
        "Literature": [800, 899],
        "History & Geography": [900, 999],    
    };

    // When location is entered, select the corresponding category
    locationInput.addEventListener("input", function () {
        const firstThreeDigits = locationInput.value.match(/^\d{3}/);
        if (firstThreeDigits) {
            const categoryNumber = parseInt(firstThreeDigits[0], 10);

            for (const [category, range] of Object.entries(categoryLocationMap)) {
                if (categoryNumber >= range[0] && categoryNumber <= range[1]) {
                    for (let option of categorySelect.options) {
                        if (option.text.trim().toLowerCase() === category.toLowerCase()) {
                            option.selected = true;
                            break;
                        }
                    }
                    break;
                }
            }
        }
    });

    function extractDetailsFromLocation() {
        let locationValue = locationInput.value.trim();
        let pattern = /^(\d{3}\.\d{3}) edition(\d+) (\d{4}) C\.\d+$/i;

        let match = locationValue.match(pattern);
        if (match) {
            document.getElementById("edition").value = match[2];
            document.getElementById("published_year").value = match[3];
        } else {
            document.getElementById("edition").value = "";
            document.getElementById("published_year").value = "";
        }
    }

    function validateLocation() {
        let locationValue = locationInput.value.trim();
        let pattern = /^\d{3}\.\d{3} edition\d+ \d{4} C\.\d+$/i;

        if (!pattern.test(locationValue)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Location Format',
                text: 'Please use the correct format: 625.231 edition1 2025 C.1',
                confirmButtonColor: '#0069d9'
            });
            return false;
        }
        return true;
    }

    function validateInput() {
        let titleValue = titleInput.value.trim();
        let authorValue = authorInput.value.trim();
        let isbnValue = isbnInput.value.trim();
        let titlePattern = /^[A-Za-z\s]+$/;
        let authorPattern = /^[A-Za-z\s]+$/;
        let isbnPattern = /^\d{13}$/;

        if (!titlePattern.test(titleValue)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Title',
                text: 'Title must contain only letters and spaces.',
                confirmButtonColor: '#0069d9'
            });
            return false;
        }

        if (!authorPattern.test(authorValue)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Author Name',
                text: 'Author name must contain only letters and spaces.',
                confirmButtonColor: '#0069d9'
            });
            return false;
        }

        if (!isbnPattern.test(isbnValue)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid ISBN',
                text: 'ISBN must be exactly 13 digits.',
                confirmButtonColor: '#0069d9'
            });
            return false;
        }

        return validateLocation();
    }

    function validatePriceAndYear() {
    let price = parseFloat(priceInput.value);
    let publishedYear = parseInt(publishedYearInput.value, 10);
    
    if (isNaN(price) || price <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Price',
            text: 'Price must be a positive number.',
            confirmButtonColor: '#0069d9'
        });
        return false;
    }
    
    if (isNaN(publishedYear) || publishedYear <= 1900 || publishedYear >= currentYear) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Published Year',
            text: 'Published year must be greater than 1900 and less than the current year.',
            confirmButtonColor: '#0069d9'
        });
        return false;
    }
    return true;
}
    document.querySelector("form").addEventListener("submit", function (event) {
        if (!validateInput() || !validatePriceAndYear()) {
            event.preventDefault();
        }
    });

    locationInput.addEventListener("input", extractDetailsFromLocation);
});

    </script>
</head>
<body>

<div class="container">
    <a href="../frontend/pages/librarian/dashboard.php" class="btn btn-back mb-4"><i class="fas fa-arrow-left"></i> Back</a>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5>Add Book</h5>
                </div>
                <div class="card-body">
                    <form action="add_book.php" method="post" onsubmit="return validateLocation()">
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" class="form-control" id="title" name="title"  required>
                        </div>
                        <div class="form-group">
                            <label for="author">Author:</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                        <div class="form-group">
                            <label for="isbn">ISBN:</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category:</label>
                            <select class="form-control" id="category_id" name="category_id"  required>
                                <option value="" disabled selected>Select a category</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No categories available</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Price (Birr):</label>
                            <input type="number" class="form-control" step="0.01" id="price" name="price" >
                        </div>
                        <div class="form-group">
                            <label for="branch_id">Branch:</label>
                            <input type="text" class="form-control" id="branch_id" name="branch_id" 
                                   value="<?php echo htmlspecialchars($assigned_branch_name); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="location">Location (Format: 625.231 edition1 2025 C.1):</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   required oninput="extractDetailsFromLocation()">
                        </div>
                        <div class="form-group">
                            <label for="edition">Edition:</label>
                            <input type="number" class="form-control" id="edition" name="edition" readonly required>
                        </div>
                        <div class="form-group">
                            <label for="published_year">Published Year:</label>
                            <input type="number" class="form-control" id="published_year" name="published_year" readonly required>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">Add Book</button>
                    </form>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info mt-3"><?php echo $message; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
