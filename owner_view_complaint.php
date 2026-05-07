
<?php 
session_start(); 
include("config/db.php"); 
 
if (!isset($_SESSION['user_id'])) { 
   header("Location: login.php"); 
   exit(); 
} 
 
$owner_id = $_SESSION['user_id']; 
 
$query = "SELECT c.complaint_id, c.issue, c.status, f.location 
         FROM complaint c 
         JOIN flat f ON c.flat_id = f.flat_id 
         WHERE f.owner_id = '$owner_id'"; 
 
$result = mysqli_query($conn, $query); 
?> 
 
<!DOCTYPE html> 
<html> 
<head> 
   <title>Complaints</title> 
   <link rel="stylesheet" href="style.css"> 
</head> 
<body> 
<div class="container"> 
   <h1>Complaints</h1> 
 
   <?php while ($row = mysqli_fetch_assoc($result)) { ?> 
       <p> 
           Location: <?php echo htmlspecialchars($row['location']); ?><br> 
           Issue: <?php echo htmlspecialchars($row['issue']); ?><br> 
           Status: <?php echo htmlspecialchars($row['status']); ?><br> 
 
           <?php if ($row['status'] == 'Pending') { ?> 
               <a href="solve_complaint.php?id=<?php echo $row['complaint_id']; ?>">Mark as 
Solved</a> 
           <?php } ?> 
       </p> 
       <hr> 
   <?php } ?> 
 
   <a href="owner_dashboard.php">Back</a> 
</div> 
</body> 
</html>
