<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header('Location: login.php');
    exit();
}

$sql = "SELECT * FROM flat WHERE status IN ('Available', 'available', 'AVAILABLE') ORDER BY flat_id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Available Flats</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .flat-card { max-width: 900px; margin: 16px auto; padding: 20px; border-radius: 8px; background: #fff; box-shadow: 0 2px 14px rgba(0,0,0,0.06); }
        .flat-card h3 { margin-bottom: 12px; }
        .flat-card p { margin: 6px 0; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-tenant">
        <h1>Browse Available Flats</h1>
        <p>All currently available flats are shown below.</p>
    </div>

    <?php if ($result->num_rows === 0): ?>
        <div class="flat-card">
            <p>No available flats match your search. Try different filters or check again later.</p>
        </div>
    <?php else: ?>
        <?php while ($flat = $result->fetch_assoc()): ?>
            <div class="flat-card">
                <h3>Flat Location: <?php echo htmlspecialchars($flat['location']); ?></h3>
                <p><strong>Detailed Address:</strong> <?php echo htmlspecialchars($flat['detailed_location']); ?></p>
                <p>
                    <strong>Floor:</strong> <?php echo $flat['floor']; ?> |
                    <strong>Area:</strong> <?php echo $flat['area']; ?> sq ft |
                    <strong>Bedrooms:</strong> <?php echo $flat['bedroom']; ?>
                </p>
                <p style="font-size: 18px; color: #007BFF;"><strong>Asking Rent:</strong> <?php echo number_format($flat['asking_rent']); ?> BDT</p>
                <p><strong>Owner Contact:</strong> (Hidden until agreement is confirmed)</p>
                <a href="actions/request_flat_action.php?flat_id=<?php echo $flat['flat_id']; ?>" class="btn-request">Send Rent Request</a>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <p style="text-align:center; margin: 24px 0;"><a href="tenant_dashboard.php">Back to Dashboard</a></p>
</div>
</body>
</html>
