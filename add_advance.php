<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $agreement_id = $_POST['agreement_id'];
    $advance = $_POST['advance'];

    $query = "UPDATE agreement SET advance='$advance'
              WHERE agreement_id='$agreement_id'";

    $message = mysqli_query($conn, $query) ? "Advance Saved" : "Error";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Advance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Add Advance Payment</h1>

    <form method="POST">
        <input type="number" name="agreement_id" placeholder="Agreement ID" required><br><br>
        <input type="number" name="advance" placeholder="Advance Amount" required><br><br>
        <button type="submit">Submit</button>
    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="owner_dashboard.php">Back</a>
</div>
</body>
</html>
