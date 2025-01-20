<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chicify_fashion";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch products
$sql = "SELECT id, name, price, images FROM products";
$result = $conn->query($sql);

if (!$result) {
    die("Error retrieving products: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Chicify Fashion</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background: #222;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        header h1 {
            margin: 0;
        }
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        nav ul li a {
            color: #fff;


            text-decoration: none;
            padding: 5px 10px;
        }
        nav ul li a:hover {
            text-decoration: underline;
        }
        main {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
      
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center; /* Center-align the grid */
            padding: 20px;
            margin: 0 auto;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px; /* Rounded corners */
            padding: 15px;
            text-align: center;
            width: 250px; /* Fixed width for consistent card size */
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
            transition: transform 0.2s ease, box-shadow 0.2s ease; /* Hover effect */
        }

        .product-card:hover {
            transform: translateY(-5px); /* Lift effect on hover */
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); /* Stronger shadow on hover */
        }

        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 4px; /* Rounded corners for images */
            margin-bottom: 15px;
        }

        .product-card h3 {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
        }

        .product-card p {
            font-size: 16px;
            color: #666;
            margin: 10px 0;
        }

        .product-card form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px; /* Space between form elements */
        }

        .product-card input[type="number"] {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .product-card button {
            background-color: #6c757d; /* Grey color */
            color: #fff; /* White text */
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease; /* Smooth transition for hover effects */
        }

        .product-card button:hover {
            background-color: #5a6268; /* Darker grey on hover */
            transform: scale(1.05); /* Slightly enlarge button on hover */
        }

                @media (max-width: 768px) {
                    .product {
                        width: 48%;
                    }
                }
                @media (max-width: 480px) {
                    .product {
                        width: 100%;
                    }
                }

    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Chicify Fashion</h1>
        </div>
        <nav>
            <ul>
                <li><a href="home.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="login.html">Login</a></li>
                <li class="cart" style="text-align: right;">
                    <a href="cart.php" id="cart-count">
                        <i class="fas fa-shopping-cart"></i> <span id="cart-count-number">0</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    <h2>Our Products</h2>
    <main>
        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($row['images']); ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                        <h3><?= htmlspecialchars($row['name']); ?></h3>
                        <p>$<?= number_format($row['price'], 2); ?></p>
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $row['id']; ?>">
                            <label for="quantity_<?= $row['id']; ?>">Quantity:</label>
                            <input type="number" id="quantity_<?= $row['id']; ?>" name="quantity" value="1" min="1">
                            <br> <!-- Adds a line break -->
                            <button onclick="addToCart('<?= htmlspecialchars($row['name']); ?>', <?= $row['price']; ?>, document.getElementById('quantity_<?= $row['id']; ?>').value)">Add to Cart</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products available.</p>
            <?php endif; ?>
        </div>
    </main>


    <script>
        // Khởi tạo giỏ hàng từ LocalStorage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        updateCartCount();

        // Hàm thêm sản phẩm vào giỏ hàng
        function addToCart(name, price, quantity) {
            let productIndex = cart.findIndex(item => item.name === name);

            if (productIndex === -1) {
                cart.push({ name, price, quantity: parseInt(quantity) });
            } else {
                cart[productIndex].quantity += parseInt(quantity);
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            syncCartWithBackend();
        }

        // Cập nhật số lượng sản phẩm trong biểu tượng giỏ hàng
        function updateCartCount() {
            let cartCount = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count-number').textContent = cartCount;
        }

        // Đồng bộ giỏ hàng với backend
        function syncCartWithBackend() {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(cart),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Cart synchronized with backend');
                } else {
                    console.error('Failed to synchronize cart');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>

<footer>
        <p>&copy; 2025 Chicify Fashion. All rights reserved.</p>
    </footer>
</body>

</html>
