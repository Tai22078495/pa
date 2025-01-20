<?php
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

    // Insert each cart item into the database
    foreach ($cartData as $item) {
        if (!isset($item['product_id'], $item['quantity'], $item['total_price'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item structure']);
            exit;
        }

        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $totalPrice = $item['total_price'];

        // Prepare and execute the SQL query
        $stmt = $conn->prepare("INSERT INTO orders (address, city, postal_code, product_id, quantity, total_price, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssiii", $address, $city, $postalCode, $productId, $quantity, $totalPrice);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to insert order: ' . $stmt->error]);
            exit;
        }
    }

    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
