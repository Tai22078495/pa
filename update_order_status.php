<?php
session_start();

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chicify_fashion";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if order ID and new status are provided via POST
if (isset($_GET['id']) && isset($_POST['status'])) {
    $order_id = $_GET['id'];
    $status = $_POST['status'];

    // Update order status
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Order status updated successfully!';
    } else {
        $_SESSION['message'] = 'Error updating order status: ' . $stmt->error;
    }

    $stmt->close();

    // Redirect to manage orders page
    header("Location: manage_orders.php");
    exit();
}

// Fetch order details
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Fetch order details from the database
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    $stmt->close();

    // If no order found, redirect to manage orders
    if (!$order) {
        $_SESSION['message'] = 'Order not found.';
        header("Location: manage_orders.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <h1>Update Order Status</h1>
    </header>

    <form method="POST">
        <label for="order_id">Order ID: </label>
        <input type="text" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>" readonly>
        <br>

        <label for="status">Status: </label>
        <select name="status" required>
            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
        <br>

        <button type="submit">Update Status</button>
    </form>

    <br>
    <a href="manage_orders.php">Back to Manage Orders</a>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
