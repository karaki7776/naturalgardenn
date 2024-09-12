<?php
session_start();

if(!isset($_SESSION['CustomerID']))
{
header("location:login-signin.php");
}

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == 'admin') {
        $admin = true;
    } else {
        $admin = false;
    }
} else {
    // إذا لم يكن المستخدم قد سجل الدخول، اعتبره غير مسؤول (غير مسؤول)
    

}
?>


<?php include 'header.php' ; ?>
    <div class="hero-content" style="   background-image: url('img/backg1.jpg');
    background-size: cover;
    background-position: center;">
        <div class="text-content">
            <h2> PLANTS  WILL  MAKE<br> YOUR  LIFE   BETTER  </h2>
            <p>We offer a wide range of high-quality<br>
                agricultural equipment and trees.</p>
            
<?php            
if (!isset($_SESSION['CustomerID'])) 
{
            echo "    <div class='buttons'>
                <a href='login-signin.php' class='btn primary-btn'>log-in</a>
                <a href='login-signin.php' class='btn secondary-btn'>Register</a>
            </div>";
         }         ?>
        </div>
        <div class="image-content">
            <img src="img/homephoto10.png" alt="Natural Garden">
        </div>
    </div>
</header>
    <main>
    <?php
// الاتصال بقاعدة البيانات
include 'config.php';
if (isset($_GET['search'])) {
    $searchTerm = $conn->real_escape_string($_GET['search']);
    
    // استعلام البحث
    $sql = "SELECT * FROM Products WHERE ProductName LIKE '%$searchTerm%' OR Description LIKE '%$searchTerm%'";
    $result = $conn->query($sql);
} else {
    // جلب جميع المنتجات بشكل افتراضي
    $sql = "SELECT * FROM Products ORDER BY RAND() LIMIT 24";
    $result = $conn->query($sql);
}

?>

    <section class="products">
    <div class="our-product">
    <h2>Our Products</h2>
    </div>

    
        <div class="search-bar-container">
            <form action="homepage.php" method="get">
                <input type="text" name="search" placeholder="Search for products...">
                <input type="submit" value="Search">
            </form>
        </div>
        <?php
if ($result->num_rows > 0) {
    echo '<div class="product-grid">';

    // عرض المنتجات
    while ($row = $result->fetch_assoc()) {
        echo '<div class="product-item">';
        echo '<img src="' . $row["ImageURL"] . '" alt="' . $row["ProductName"] . '">';
        echo '<h3>' . $row["ProductName"] . '</h3>';
        echo '<p>' . $row["Description"] . '</p>';
        echo '<p>$' . $row["Price"] . '</p>';
        
        echo "<div class='div-add-to-cart'><form action='cart.php' method='post'>";
        echo "<input type='hidden' name='product_id' value='" . $row['ProductID'] . "'>";
        echo "<input type='submit' name='addToCart' value='Add to Cart'>";
        echo "</form></div>";
        
        // إضافة خيار التحرير والحذف إذا كان المستخدم مسؤولاً
        if ($admin == true) {
            echo "<div class='div-edit'>";
            echo "<a href='edit.php?product_id=" . $row['ProductID'] . "'><i class='fa fa-pencil'></i>Edit</a>";
            echo "<a href='delete.php?product_id=" . $row['ProductID'] . "' onclick=\"return confirm('هل أنت متأكد من حذف هذا المنتج؟');\"><i class='fa fa-trash'></i></a>";
            echo "</div>";
        }

        echo '</div>';
    }

    echo '</div>';
    echo '</section>';
} else {
    echo '<div class="pp">No product available</div>';
}

$conn->close();
?>

<?php 
if ($admin == true) { 
 echo'   <div class="add-product-card">
        <div class="add-product">
            <a href="add-product.php"><i class="fas fa-plus"> Add product</i></a>
        </div>
    </div>';
}
?>
</div>
<div>
<a href="#all-products" class="view-all">View All Products</a>
</div>
</section>

</main>
<!-------------scroll reveal animation-------------------->
<script src="js/scrollreveal.min.js"></script>
 <!---========js==================-->
 <script src="addtocart.js"></script>
</body>
<?php include 'footer.php' ; ?>

</html>


