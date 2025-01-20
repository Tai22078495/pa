<?php
session_start();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove'])) {
    // Remove product logic
    $product_name = $_POST['product_name'];
    $cart = array_filter($cart, function($item) use ($product_name) {
        return $item['name'] != $product_name;
    });
    
    // Re-index the array to avoid gaps after removing an item
    $cart = array_values($cart);
    $_SESSION['cart'] = $cart;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Chicify Fashion</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .cart-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .cart-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th, .cart-table td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .cart-table th {
            background-color: #333;
            color: white;
        }

        .cart-table td {
            background-color: #f9f9f9;
        }

        .cart-table button {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .cart-table button:hover {
            background-color: #555;
        }

        .checkout-btn {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .checkout-btn a {
            background-color: #333;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
        }

        .checkout-btn a:hover {
            background-color: #555;
        }

        .empty-message {
            text-align: center;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2>Your Shopping Cart</h2>

        <table class="cart-table">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
            <?php if (count($_SESSION['cart']) > 0): ?>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $product_id => $product):
                    $subtotal = $product['price'] * $product['quantity'];
                    $total += $subtotal;
                ?>
                <tr data-product-id="<?php echo $product_id; ?>">
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo $product['quantity']; ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                        <button class="btn-remove" onclick="removeFromCart('<?php echo $product_id; ?>', '<?php echo $product['name']; ?>')">Remove</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3"><strong>Total</strong></td>
                    <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="empty-message">Your cart is empty.</td>
                </tr>
            <?php endif; ?>
        </table>

        <div class="checkout-btn">
            <a href="shop.php" class="btn-continue-shopping">Continue Shopping</a>
            <?php if (count($_SESSION['cart']) > 0): ?>
                <a href="shipping_address.php">Proceed to Checkout</a>
            <?php endif; ?>
        </div>
        
    </div>

    <script>
           function removeFromCart(productId, productName) {
            // Update cart in localStorage (if applicable)
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart = cart.filter(item => item.name !== productName);
            localStorage.setItem('cart', JSON.stringify(cart));

            // Remove the row from the DOM
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
            }

            // Notify backend to update the session
            fetch('remove_from_cart.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'remove': true,
                    'product_name': productName
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(response => response.text())
            .then(data => {
                console.log('Item removed successfully:', data);
                location.reload(); // Reload the page to update totals
            }).catch(error => {
                console.error('Error removing item:', error);
            });
        }
    </script>
</body>

</html>