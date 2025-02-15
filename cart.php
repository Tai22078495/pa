<?php
session_start();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['remove'])) {
        // Remove product logic
        $product_name = $_POST['product_name'];
        $cart = array_filter($cart, function($item) use ($product_name) {
            return $item['name'] != $product_name;
        });
        
        // Re-index the array to avoid gaps after removing an item
        $cart = array_values($cart);
        $_SESSION['cart'] = $cart;
    } elseif (isset($_POST['clear_cart'])) {
        // Clear cart logic
        $_SESSION['cart'] = [];
    }
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

        .cart-table button, .checkout-btn button, .btn-continue-shopping, .btn-checkout {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .cart-table button:hover, .checkout-btn button:hover, .btn-continue-shopping:hover, .btn-checkout:hover {
            background-color: #555;
        }

        .checkout-btn {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
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
            <tbody id="cart-items">
                <?php if (count($cart) > 0): ?>
                    <?php
                    $total = 0;
                    foreach ($cart as $product_id => $product):
                        $subtotal = $product['price'] * $product['quantity'];
                        $total += $subtotal;
                    ?>
                    <tr data-product-id="<?php echo $product_id; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <input type="number" min="1" value="<?php echo $product['quantity']; ?>" onchange="updateQuantity('<?php echo htmlspecialchars($product['name']); ?>', this.value)">
                        </td>
                        <td class="subtotal">$<?php echo number_format($subtotal, 2); ?></td>
                        <td>
                            <button class="btn-remove" onclick="removeFromCart('<?php echo $product_id; ?>', '<?php echo htmlspecialchars($product['name']); ?>')">Remove</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td id="total"><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-message">Your cart is empty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="checkout-btn">
            <a href="shop.php" class="btn-continue-shopping">Continue Shopping</a>
            <?php if (count($cart) > 0): ?>
                <a href="personalinfo.html" class="btn-checkout">Proceed to Checkout</a>
                <button onclick="clearCart()">Clear Cart</button>
            <?php endif; ?>
        </div>
        
    </div>

    <script>
        function updateQuantity(productName, newQuantity) {
            console.log('Updating quantity for product:', productName, 'New Quantity:', newQuantity);
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            console.log('Current cart in local storage:', cart);

            // Find the product in the cart by name
            const product = cart.find(item => item.name === productName);
            if (!product) {
                console.error('Product not found in local storage cart');
                return;
            }

            // Update the product quantity
            product.quantity = parseInt(newQuantity);
            localStorage.setItem('cart', JSON.stringify(cart));

            // Update subtotal in the table
            const row = document.querySelector(`tr[data-product-name="${productName}"]`);
            const price = parseFloat(row.querySelector('td:nth-child(2)').textContent.replace('$', ''));
            const subtotal = price * newQuantity;
            row.querySelector('.subtotal').textContent = `$${subtotal.toFixed(2)}`;

            // Recalculate the total
            recalculateTotals();

            // Notify backend to update the session
            fetch('update_quantity.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'update_quantity': true,
                    'product_name': product.name,
                    'quantity': newQuantity
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(response => response.json())
            .then(data => {
                console.log('Quantity updated successfully:', data);
            }).catch(error => {
                console.error('Error updating quantity:', error);
            });
        }

        function recalculateTotals() {
            console.log('Recalculating totals...');

            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let total = 0;

            cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
            });

            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
            console.log('New total:', total);
        }

        function removeFromCart(productId, productName) {
            console.log('Removing product ID:', productId, 'Product Name:', productName);

            // Update cart in localStorage
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart = cart.filter(item => item.name !== productName);
            localStorage.setItem('cart', JSON.stringify(cart));

            // Remove the row from the DOM
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
            }

            // Recalculate the total
            recalculateTotals();

            // Notify backend to update the session
            fetch('cart.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'remove': true,
                    'product_name': productName
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(response => response.json())
            .then(data => {
                console.log('Item removed successfully:', data);
            }).catch(error => {
                console.error('Error removing item:', error);
            });
        }

        function clearCart() {
            console.log('Clearing cart...');

            // Clear cart in localStorage
            localStorage.removeItem('cart');

            // Notify backend to clear the session
            fetch('cart.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'clear_cart': true
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(response => response.json())
            .then(data => {
                console.log('Cart cleared successfully:', data);
                location.reload(); // Reload the page to update totals
            }).catch(error => {
                console.error('Error clearing cart:', error);
            });
        }

        // Initial call to recalculate totals when the page loads
        document.addEventListener('DOMContentLoaded', recalculateTotals);
    </script>
</body>
</html>
