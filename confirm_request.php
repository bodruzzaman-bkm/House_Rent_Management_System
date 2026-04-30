<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_requests.php");
    exit();
}

$request_id = $_GET['id'];

$res = mysqli_query($conn, "SELECT * FROM request WHERE request_id='$request_id'");
$data = mysqli_fetch_assoc($res);

$tenant_id = $data['tenant_id'];
$flat_id = $data['flat_id'];
$owner_id = $_SESSION['user_id'];

// Update request
mysqli_query($conn, "UPDATE request SET request_status='Approved' WHERE request_id='$request_id'");

// Update flat
mysqli_query($conn, "UPDATE flat SET status='Rented' WHERE flat_id='$flat_id'");

// Create agreement
mysqli_query($conn, "INSERT INTO agreement (advance, start_date, owner_id)
                     VALUES (0, CURDATE(), '$owner_id')");

$agreement_id = mysqli_insert_id($conn);

// Link
mysqli_query($conn, "INSERT INTO links (agreement_id, tenant_id, flat_id)
                     VALUES ('$agreement_id', '$tenant_id', '$flat_id')");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Request Confirmed</h1>

    <p>Tenant ID: <?php echo htmlspecialchars($tenant_id); ?></p>
    <p>Flat ID: <?php echo htmlspecialchars($flat_id); ?></p>

    <a href="view_requests.php">Back</a>
</div>
</body>
</html>
