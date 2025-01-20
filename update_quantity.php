<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $product_name = $_POST['product_name'];
    $new_quantity = intval($_POST['quantity']);

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['name'] == $product_name) {
                $item['quantity'] = $new_quantity;
                break;
            }
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Quantity updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>