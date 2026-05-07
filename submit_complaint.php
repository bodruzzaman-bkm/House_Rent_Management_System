
<?php 
session_start(); 
include("config/db.php"); 
 
if (!isset($_SESSION['user_id'])) { 
   header("Location: login.php"); 
   exit(); 
} 
 
$message = ""; 
 
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
   $tenant_id = $_SESSION['user_id']; 
   $flat_id = $_POST['flat_id']; 
   $issue = $_POST['issue']; 
 
   $query = "INSERT INTO complaint (status, issue, tenant_id, flat_id) 
             VALUES ('Pending', '$issue', '$tenant_id', '$flat_id')"; 
 
   $message = mysqli_query($conn, $query) ? "Complaint Submitted" : "Error"; 
} 
?> 
 
<!DOCTYPE html> 
<html> 
<head> 
   <title>Complaint</title> 
   <link rel="stylesheet" href="style.css"> 
</head> 
<body> 
<div class="container"> 
   <h1>Submit Complaint</h1> 
 
   <form method="POST"> 
       <input type="number" name="flat_id" placeholder="Flat ID" required><br><br> 
       <textarea name="issue" placeholder="Describe Issue" required></textarea><br><br> 
       <button type="submit">Submit</button> 
   </form> 
 
   <p><?php echo htmlspecialchars($message); ?></p> 
 
   <a href="tenant_dashboard.php">Back</a> 
</div> 
</body> 
</html> 
