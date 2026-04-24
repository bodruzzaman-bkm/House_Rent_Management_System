<?php
include("config/db.php");

$request_id = $_GET['id'];

// Get request info
$res = mysqli_query($conn, "SELECT * FROM flat_requests WHERE request_id='$request_id'");
$data = mysqli_fetch_assoc($res);

// Insert tenancy
mysqli_query($conn, "INSERT INTO tenancies (flat_id, tenant_id, owner_id, start_date)
VALUES ('".$data['flat_id']."', '".$data['tenant_id']."', '".$data['owner_id']."', CURDATE())");

// Update flat status
mysqli_query($conn, "UPDATE flats SET status='Rented' WHERE flat_id='".$data['flat_id']."'");

// Update request status
mysqli_query($conn, "UPDATE flat_requests SET status='Approved' WHERE request_id='$request_id'");

header("Location: owner_dashboard.php");
?>
