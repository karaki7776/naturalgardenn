<?php
session_start();

// تحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['CustomerID'])) {
    header("Location: login.php"); // إعادة توجيه إلى صفحة تسجيل الدخول إذا لم يكن المستخدم مسجل الدخول
    exit();
}

// إعدادات الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naturalgarden";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// الحصول على معرف المستخدم من الجلسة
$userID = $_SESSION['CustomerID'];

// معالجة إضافة منتج إلى سلة التسوق
if (isset($_POST['addToCart'])) {
    $productID = intval($_POST['product_id']);

    // التحقق مما إذا كان المنتج موجودًا بالفعل في السلة الخاصة بالمستخدم
    $sql = "SELECT * FROM Cart WHERE ProductID = ? AND CustomerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $productID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // تحديث الكمية إذا كان المنتج موجودًا بالفعل في السلة
        $sql = "UPDATE Cart SET Quantity = Quantity + 1 WHERE ProductID = ? AND UserID = ?";
    } else {
        // إدراج المنتج الجديد إلى السلة
        $sql = "INSERT INTO Cart (ProductID, Quantity, CustomerID) VALUES (?, 1, ?)";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $productID, $userID);
    $stmt->execute();
    $stmt->close();
    
    // إعادة توجيه لعرض السلة
    header("Location: cart.php");
    exit();
}

// معالجة تحديث الكميات في سلة التسوق
if (isset($_POST['updateCart'])) {
    foreach ($_POST['quantities'] as $cartID => $quantity) {
        $quantity = intval($quantity);
        if ($quantity > 0) {
            $sql = "UPDATE Cart SET Quantity = ? WHERE CartID = ? AND CustomerID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cartID, $userID);
            $stmt->execute();
            $stmt->close();
        }
    }
    // إعادة توجيه لعرض التحديث
    header("Location: cart.php");
    exit();
}

// معالجة إزالة المنتج من السلة
if (isset($_POST['removeProduct'])) {
    $cartID = intval($_POST['cart_id']);
    $sql = "DELETE FROM Cart WHERE CartID = ? AND CustomerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cartID, $userID);
    $stmt->execute();
    $stmt->close();

    // إعادة توجيه بعد الحذف
    header("Location: cart.php");
    exit();
}

// استعلام لاسترجاع بيانات المنتجات في سلة التسوق
$sql = "SELECT Cart.CartID, Products.ProductName, Products.Price, Products.ImageURL, Cart.Quantity 
        FROM Cart 
        JOIN Products ON Cart.ProductID = Products.ProductID 
        WHERE Cart.CustomerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// تعريف المتغيرات لحساب الإجمالي
$totalPrice = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/cart.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Favicon -->
    <link rel="shortcut icon" href="css/favicom.png" type="image/x-icon">
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Your Shopping Cart <i class="ri-shopping-cart-line"></i></h2>

    <!-- عرض محتويات سلة التسوق -->
    <form action="cart.php" method="post">
        <div class="cart-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $itemTotal = $row['Price'] * $row['Quantity']; // حساب إجمالي المنتج الواحد
                $totalPrice += $itemTotal; // إضافة سعر المنتج للإجمالي

                echo "<div class='cart-item'>";
                echo "<img src='" . $row['ImageURL'] . "' alt='" . $row['ProductName'] . "' class='product-image'>";
                echo "<div class='product-details'>";
                echo "<h3>" . $row['ProductName'] . "</h3>";
                echo "<p>Price: $" . number_format($row['Price'], 2) . "</p>";
                echo "<p>Quantity: <input type='number' name='quantities[" . $row['CartID'] . "]' value='" . $row['Quantity'] . "' min='1'></p>";

                // زر "Remove Product"
                echo "<form action='cart.php' method='post'>";
                echo "<input type='hidden' name='cart_id' value='" . $row['CartID'] . "'>";
                echo "<button type='submit' name='removeProduct' class='remove-btn'><i class='fa fa-trash'></i></button>";
                echo "</form>";

                echo "</div>";
                echo "</div>";
              
            }

            echo "<button type='submit' name='updateCart' class='update-btn'>Update Cart</button>";
            echo"<div class='cart-summary'>";
            echo "<form action='checkout.php' method='post'>";
            echo "<input type='hidden' name='totalAmount' value='" . $totalPrice . "'>";  // إرسال المجموع الكلي إلى صفحة الـ checkout
            echo "<button type='submit' class='checkout-btn'>Checkout Now</button>";
            echo "</form>";
            echo "</div>";        
        } else {
            echo "<div class='cart-empty-container'>";
            echo "<img src='img/cart3.png' alt='Empty Cart'>"; 
            echo "<h1>Your cart is empty.</h1>";
            echo "<p>What are you waiting for ?</p>";
            echo "<a href='homepage.php' class='shop-now-btn'>Start shoping</a>"; 
            echo "</div>";
        }
        ?>
        </div>
       
    </form>

<?php $conn->close(); ?>

</body>
</html>
