<?php
session_start();
require_once "connect_db.php";

// Retrieve email and password
$email = $_POST['username'];
$password = md5($_POST['password']); // ⚠️ Use password_hash() in production

// Query by email
$query = "SELECT * FROM customer WHERE gmail = '$email' AND pass= '$password'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Set session data
    $_SESSION['email'] = $row['gmail']; // Store Gmail
    $_SESSION['customer_name'] = $row['customer_name'];
    $_SESSION['customer_id'] = $row['customer_id'];
    $_SESSION['profileimg'] = $row['profileimg'];

    // Redirect all users to the main page (no level checks)
    header('Location: index.php');
} else {
    echo "<script>alert('Invalid email or password.'); window.location.href='login.php';</script>";
}
?>
