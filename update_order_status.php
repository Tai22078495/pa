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
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
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
} else {
    $_SESSION['message'] = 'Invalid request.';
}

// Redirect to manage orders page
header("Location: manage_orders.php");
exit();
?>
