<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Check for required inputs
    if (!isset($_POST['address'], $_POST['cartData'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Decode cart data
    $cartData = json_decode($_POST['cartData'], true);
    if (empty($cartData)) {
        echo json_encode(['success' => false, 'message' => 'Cart data is empty or invalid']);
        exit;
    }

    // Debugging output to verify cartData structure
    error_log('Received cart data: ' . print_r($cartData, true));

    // Ensure each cart item has the required structure
    foreach ($cartData as $item) {
        if (!isset($item['product_id'], $item['quantity'], $item['total_price'])) {
            error_log('Invalid cart item structure: ' . print_r($item, true)); // Debugging output
            echo json_encode(['success' => false, 'message' => 'Invalid cart item structure']);
            exit;
        }
    }

    $address = $_POST['address'];
    $username = 'guest'; // Default username for non-logged-in users

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
        $totalPrice = array_sum(array_column($cartData, 'total_price'));
        $stmt->bind_param("sd", $username, $totalPrice);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        // Insert each cart item into the order_items table
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($cartData as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['total_price'] / $quantity; // assuming total_price is for the total quantity

            // Check if the product ID exists in the products table
            $product_check_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE id = ?");
            $product_check_stmt->bind_param("i", $productId);
            $product_check_stmt->execute();
            $product_check_stmt->bind_result($product_exists);
            $product_check_stmt->fetch();
            $product_check_stmt->close();

            if ($product_exists == 0) {
                throw new Exception("Product ID $productId does not exist in the products table");
            }

            $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
            $stmt->execute();
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        $conn->close();

        // Return success response
        echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
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