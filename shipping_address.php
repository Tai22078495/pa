<?php
session_start();

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Here, save the shipping address information to the session
    $_SESSION['shipping_address'] = $_POST['address'];
    $_SESSION['shipping_city'] = $_POST['city'];
    $_SESSION['shipping_zip'] = $_POST['zip'];
    
    // After saving the address, redirect to the payment page
    header("Location: payment.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Address</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .payment-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .checkout-btn {
            text-align: center;
            margin-top: 20px;
        }

        .checkout-btn button {
            background-color: #333;
            color: white;
            padding: 15px 30px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        .checkout-btn button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

<div class="payment-container">
    <h2>Enter Shipping Address</h2>

    <form action="shipping_address.php" method="POST">
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required><br><br>

        <label for="city">City:</label>
        <input type="text" id="city" name="city" required><br><br>

        <label for="zip">Zip Code:</label>
        <input type="text" id="zip" name="zip" required><br><br>
      
        <div class="checkout-btn">
            <button type="submit">Proceed to Payment</button>
        </div>
    </form>

    <div class="checkout-btn">
        <form action="cart.php" method="GET" style="display: inline;">
            <button type="submit" class="btn-gobacktocart">Go Back to Cart</button>
        </form>
    </div>
</div>

</body>
</html>
