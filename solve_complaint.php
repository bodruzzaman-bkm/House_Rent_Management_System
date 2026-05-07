
<?php 
session_start(); 
include("config/db.php"); 
 
if (!isset($_SESSION['user_id'])) { 
   header("Location: login.php"); 
   exit(); 
} 
 
if (!isset($_GET['id'])) { 
header("Location: view_complaints.php"); 
exit(); 
} 
$id = $_GET['id']; 
mysqli_query($conn, "UPDATE complaint SET status='Solved' WHERE complaint_id='$id'"); 
?> 
<!DOCTYPE html> 
<html> 
<head> 
<title>Solved</title> 
<link rel="stylesheet" href="style.css"> 
</head> 
<body> 
<div class="container"> 
<h1>Complaint Marked as Solved</h1> 
<a href="view_complaints.php">Back</a> 
</div> 
</body> 
</html> 
