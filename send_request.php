<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['flat_id'])) {
    header("Location: tenant_dashboard.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];
$flat_id = $_GET['flat_id'];

$query = "INSERT INTO request (date, request_status, tenant_id, flat_id)
          VALUES (CURDATE(), 'Pending', '$tenant_id', '$flat_id')";

$status = mysqli_query($conn, $query) ? "Request Sent Successfully" : "Error Sending Request";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>House Rent Management System</h1>
    <h2><?php echo htmlspecialchars($status); ?></h2>

    <a href="tenant_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
