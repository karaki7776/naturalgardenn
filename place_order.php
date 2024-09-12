<?php
session_start();

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['CustomerID'])) {
    echo "No user is logged in.";
    exit();
}

$customerID = $_SESSION['CustomerID'];
$totalAmountWithDelivery = $_POST['totalAmountWithDelivery'];

// إضافة الطلب إلى جدول orders
$sqlOrder = "INSERT INTO orders (customerID, orderDate, totalAmount) VALUES (?, NOW(), ?)";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("id", $customerID, $totalAmountWithDelivery);

if ($stmtOrder->execute()) {
    $orderID = $conn->insert_id; // الحصول على رقم الطلب

    // جلب المنتجات من سلة التسوق
    $sqlCart = "SELECT p.ProductID, c.Quantity, p.Price 
                FROM cart c 
                JOIN products p ON c.ProductID = p.ProductID 
                WHERE c.CustomerID = ?";
    $stmtCart = $conn->prepare($sqlCart);
    $stmtCart->bind_param("i", $customerID);
    $stmtCart->execute();
    $resultCart = $stmtCart->get_result();

    // إضافة تفاصيل المنتجات إلى order_details
    $sqlOrderDetails = "INSERT INTO orderdetails (OrderID, ProductID, Quantity, Price) VALUES (?, ?, ?, ?)";
    $stmtOrderDetails = $conn->prepare($sqlOrderDetails);

    while ($row = $resultCart->fetch_assoc()) {
        $stmtOrderDetails->bind_param("iiid", $orderID, $row['ProductID'], $row['Quantity'], $row['Price']);
        $stmtOrderDetails->execute();
    }

    // إفراغ سلة التسوق بعد إتمام الطلب
    $sqlClearCart = "DELETE FROM cart WHERE CustomerID = ?";
    $stmtClearCart = $conn->prepare($sqlClearCart);
    $stmtClearCart->bind_param("i", $customerID);
    $stmtClearCart->execute();

    echo "<p>Order placed successfully</p>.";
header("refresh:2;url=homepage.php");
} else {
    echo "Error placing order.";
}

$conn->close();
?>
