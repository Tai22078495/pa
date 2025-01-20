<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for required inputs
    if (!isset($_POST['address'], $_POST['city'], $_POST['postal_code'], $_POST['cartData'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Decode cart data
    $cartData = json_decode($_POST['cartData'], true);
    if (empty($cartData)) {
        echo json_encode(['success' => false, 'message' => 'Cart data is empty or invalid']);
        exit;
    }

    $address = $_POST['address'];
    $city = $_POST['city'];
    $postalCode = $_POST['postal_code'];

    // Connect to the database
    $conn = new mysqli('localhost', 'root', '', 'chicify_fashion');
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert order into the orders table
        $stmt = $conn->prepare("INSERT INTO orders (username, total_price, status, created_at) VALUES (?, ?, 'pending', NOW())");
        $username = $_SESSION['username']; // assuming you have the username stored in the session
        $totalPrice = array_sum(array_column($cartData, 'total_price'));
        $stmt->bind_param("sd", $username, $totalPrice);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        // Insert each cart item into the order_items table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cartData as $item) {
            if (!isset($item['product_id'], $item['quantity'], $item['total_price'])) {
                throw new Exception('Invalid cart item structure');
            }

            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['total_price'] / $quantity; // assuming total_price is for the total quantity

            $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
            $stmt->execute();
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        $conn->close();

        // Return success response and redirect to payment page
        echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
        header("Location: payment.php");
        exit;

    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Failed to place the order: ' . $e->getMessage()]);
        exit;
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
?>