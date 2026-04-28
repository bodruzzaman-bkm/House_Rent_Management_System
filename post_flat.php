<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Flat - House Rent System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="form-container">
        <h2>Post a New Flat Advertisement</h2>

        <form action="actions/post_flat_action.php" method="POST">

            <div class="form-group">
                <label>Location Area (e.g., Mirpur, Dhanmondi) *</label>
                <input type="text" name="location" placeholder="Which area?" required>
            </div>

            <div class="form-group">
                <label>Detailed Address (Road, House, Block) *</label>
                <input type="text" name="detailed_location" placeholder="e.g. Road 5, House 12/A" required>
            </div>

            <div class="form-group">
                <label>Flat Size (Square Feet) *</label>
                <input type="number" name="area" placeholder="e.g., 1200" required>
            </div>

            <div class="form-group">
                <label>Number of Bedrooms *</label>
                <input type="number" name="bedroom" placeholder="e.g., 3" required>
            </div>

            <div class="form-group">
                <label>Floor Number (Numeric Only) *</label>
                <input type="number" name="floor" placeholder="e.g., 4" required>
            </div>

            <div class="form-group">
                <label>Asking Rent (BDT per month) *</label>
                <input type="number" name="asking_rent" placeholder="e.g., 25000" required>
            </div>

            <button type="submit" style="background-color: #28a745;">Publish Flat Advertisement</button>

            <a href="owner_dashboard.php" class="back-link">← Cancel and Go Back</a>
        </form>
    </div>

</body>

</html>