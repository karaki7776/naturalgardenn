
<?php 
session_start(); 
 
$servername = "localhost"; 
$dbusername = "root";  
$dbpassword = "";  
$dbname = "naturalgarden";  

// إنشاء الاتصال بقاعدة البيانات 
$con = new mysqli($servername, $dbusername, $dbpassword, $dbname); 

if ($con->connect_error) { 
    die("Connection failed: " . $con->connect_error); 
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $email = $_POST['email']; 
    $username = $_POST['username'];
    $phone = $_POST['phone']; 
    $address = $_POST['address']; 
    $password = $_POST['password']; 
    
    // تشفير كلمة المرور
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // إعداد استعلام محضر
    $stmt = $con->prepare("INSERT INTO users (username, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt) {
        // ربط البيانات بالاستعلام
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $phone, $address);

        // تنفيذ الاستعلام
        if ($stmt->execute()) {
            $success = "Registration completed successfully";
        } else {
            $error="An error occured while register " . $stmt->error;
        }

        // إغلاق البيان
        $stmt->close();
    } else {
        $error="Something went wrong " . $con->error;
    }
} 

// إغلاق الاتصال
$con->close(); 
?> 
 
<!DOCTYPE html> 
<html lang="ar"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>تسجيل - Natural Garden</title> 
    <link rel="stylesheet" href="css/register.css"> 
</head> 
<body> 
    <header> 
        <h1>Natural Garden</h1> 
    </header> 
     
    <div class="register-container"> 
        <h2>Register Now</h2> 
 
        <?php if (isset($error)): ?> 
            <p style="color:red;"><?php echo $error; ?></p> 
        <?php endif; ?> 
 
        <?php if (isset($success)): ?> 
            <p style="color:green;"><?php echo $success; ?></p> 
        <?php endif; ?> 
 
        <form method="post" action="register.php"> 
            <label for="email">Enter your Email:</label><br> 
            <input type="email" id="email" name="email" required><br><br> 
 
            <label for="username">Username:</label><br> 
            <input type="text" id="username" name="username" required><br><br> 
 
            <label for="phone">Phone Number:</label><br> 
            <input type="text" id="phone" name="phone" required><br><br> 
 
            <label for="address">Address:</label><br> 
            <input type="text" id="address" name="address" required><br><br> 
 
            <label for="password">Password:</label><br> 
            <input type="password" id="password" name="password" required><br><br> 
 
            <input type="submit" value="Submit"> 
        </form> 
    </div> 
</body> 
</html>