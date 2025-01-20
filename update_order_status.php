<?php
session_start();

// Kiểm tra xem người dùng có quyền admin không
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php"); // Chuyển hướng về trang login nếu chưa đăng nhập
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chicify_fashion";

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra nếu có ID đơn hàng và trạng thái mới được gửi qua phương thức POST
if (isset($_GET['id']) && isset($_POST['status'])) {
    $order_id = $_GET['id'];
    $status = $_POST['status'];

    // Cập nhật trạng thái đơn hàng
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo "Trạng thái đơn hàng đã được cập nhật!";
    } else {
        echo "Lỗi khi cập nhật trạng thái đơn hàng: " . $stmt->error;
    }

    $stmt->close();
}

// Lấy thông tin đơn hàng
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    // Lấy thông tin đơn hàng từ cơ sở dữ liệu
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    $stmt->close();

    // Nếu không có đơn hàng, chuyển hướng về trang quản lý đơn hàng
    if (!$order) {
        echo "Đơn hàng không tồn tại.";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order Status</title>
</head>
<body>
    <h1>Cập nhật trạng thái đơn hàng</h1>
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
// Đóng kết nối
$conn->close();
?>
