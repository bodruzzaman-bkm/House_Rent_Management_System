<?php
session_start();
include("config/db.php");

$tenant_id = $_SESSION['user_id'];
$flat_id = $_GET['id'];

// Get owner id
$query = "SELECT owner_id FROM flats WHERE flat_id='$flat_id'";
$result = mysqli_query($conn, $query);
$owner = mysqli_fetch_assoc($result);

// Insert request
$insert = "INSERT INTO flat_requests (flat_id, tenant_id, owner_id)
           VALUES ('$flat_id', '$tenant_id', '".$owner['owner_id']."')";

mysqli_query($conn, $insert);

header("Location: tenant_dashboard.php");
?>
