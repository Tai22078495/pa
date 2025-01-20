<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chicify_fashion";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle file upload
function uploadImage($file) {
    $target_dir = "images/";
    
    // Ensure the images directory exists and has correct permissions
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a valid image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File is not an image.');</script>";
        return false;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        return false;
    }

    // Move file to target directory
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        return false;
    }

    return $target_file;
}

// Function to add a product
function addProduct($conn, $name, $price, $image_url, $quantity) {
    $sql = "INSERT INTO products (name, price, images, quantity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<script>alert('Prepare failed: " . $conn->error . "');</script>";
        return false;
    }
    $stmt->bind_param("sdsi", $name, $price, $image_url, $quantity);
    if (!$stmt->execute()) {
        echo "<script>alert('Execute failed: " . $stmt->error . "');</script>";
        return false;
    }
    return true;
}

// Function to edit a product
function editProduct($conn, $id, $name, $price, $image_url, $quantity) {
    $sql = "UPDATE products SET name = ?, price = ?, images = ?, quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<script>alert('Prepare failed: " . $conn->error . "');</script>";
        return false;
    }
    $stmt->bind_param("sdsii", $name, $price, $image_url, $quantity, $id);
    if (!$stmt->execute()) {
        echo "<script>alert('Execute failed: " . $stmt->error . "');</script>";
        return false;
    }
    return true;
}

// Function to delete a product
function deleteProduct($conn, $id) {
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<script>alert('Prepare failed: " . $conn->error . "');</script>";
        return false;
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        echo "<script>alert('Execute failed: " . $stmt->error . "');</script>";
        return false;
    }
    return true;
}

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $image_url = uploadImage($_FILES['image_url']);
        if ($image_url && addProduct($conn, $_POST['product_name'], $_POST['price'], $image_url, $_POST['quantity'])) {
            $message = "Product added successfully!";
        }
    } elseif (isset($_POST['edit_product'])) {
        $image_url = !empty($_FILES['image_url']['name']) ? uploadImage($_FILES['image_url']) : $_POST['current_image_url'];
        if ($image_url && editProduct($conn, $_POST['id'], $_POST['product_name'], $_POST['price'], $image_url, $_POST['quantity'])) {
            $message = "Product updated successfully!";
        }
    }
}

// Handle delete action
if (isset($_GET['delete_product'])) {
    if (deleteProduct($conn, intval($_GET['delete_product']))) {
        $message = "Product deleted successfully!";
    }
}

// Fetch all products
$sql = "SELECT id, name, price, images, quantity, created_at FROM products";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Manage Products</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Add styles for larger images */
        .product-image {
            width: 100px; /* Adjust the width as needed */
            height: 100px; /* Adjust the height as needed */
            object-fit: cover; /* Ensure the image fits within the specified dimensions */
        }
    </style>
    <script>
        // Function to display an alert message
        function showAlert(message) {
            alert(message);
        }

        // Check if a message needs to be displayed
        <?php if (!empty($message)): ?>
            showAlert("<?php echo $message; ?>");
        <?php endif; ?>
    </script>
</head>
<body>
    <header>
        <h1>Admin Panel - Manage Products</h1>
    </header>

    <nav>
        <a href="manage_products.php">Manage Products</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="logout.php">Logout</a>
    </nav>

    <h2>Add Product</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="product_name">Name:</label>
        <input type="text" name="product_name" required><br>
        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" required><br>
        <label for="image_url">Image:</label>
        <input type="file" name="image_url" required><br>
        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" required><br>
        <button type="submit" name="add_product">Add Product</button>
    </form>

    <h2>Products List</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Image</th>
                <th>Quantity</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><img src="<?php echo $row['images']; ?>" alt="Product Image" class="product-image"></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="current_image_url" value="<?php echo $row['images']; ?>">
                            <input type="text" name="product_name" value="<?php echo $row['name']; ?>" required><br>
                            <input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>" required><br>
                            <input type="file" name="image_url"><br>
                            <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" required><br>
                            <button type="submit" name="edit_product">Edit</button>
                        </form>
                        <a href="?delete_product=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
