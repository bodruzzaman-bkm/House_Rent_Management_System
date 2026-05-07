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
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0}
        .dashboard-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:24px;text-align:center}
        .dashboard-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .dashboard-header p{font-size:15px;opacity:0.9;margin:0}
        .dashboard-container{max-width:1000px;margin:0 auto;padding:0 16px 24px}
        .back-link{display:inline-flex;align-items:center;gap:6px;padding:10px 14px;background:#fff;border-radius:8px;text-decoration:none;color:var(--primary);font-weight:600;margin:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);transition:all 0.2s}
        .back-link:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .flats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px;margin:20px 0}
        .flat-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:20px;border-left:4px solid var(--primary);transition:all 0.2s}
        .flat-card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(0,0,0,0.1)}
        .flat-card h3{margin:0 0 12px;font-size:18px;color:#0f172a}
        .flat-card p{margin:8px 0;font-size:14px;color:#4b5563}
        .flat-label{color:#6b7280;font-weight:600}
        .flat-info-row{display:flex;gap:16px;margin:12px 0;font-size:13px}
        .flat-info-row span{color:#6b7280;font-weight:600}
        .flat-info-row strong{color:#1f2937}
        .flat-rent{font-size:18px;font-weight:700;color:#059669;margin:12px 0}
        .card-btn{display:inline-block;padding:10px 14px;background:var(--primary);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;margin-top:12px;transition:all 0.2s}
        .card-btn:hover{background:#1e40af;transform:translateY(-2px)}
        .no-flats{text-align:center;padding:40px;background:#fff;border-radius:12px;color:#6b7280;margin-top:20px}
        .no-flats h2{color:#0f172a;margin-bottom:12px}
        @media(max-width:800px){.flats-grid{grid-template-columns:1fr}.back-link{margin:12px}}
    </style>
</head>
<body>
<div class="dashboard-header">
    <h1>🏘️ Browse Available Flats</h1>
    <p>Find your perfect rental home from our available listings</p>
</div>

<div class="dashboard-container">
    <a href="tenant_dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php if ($result->num_rows === 0): ?>
        <div class="no-flats">
            <h2>No Available Flats</h2>
            <p>Sorry, no flats are currently available for rent. Please check back later!</p>
            <a href="tenant_dashboard.php" class="card-btn" style="display:inline-block">← Go Back</a>
        </div>
    <?php else: ?>
        <div class="flats-grid">
            <?php while ($flat = $result->fetch_assoc()): ?>
                <div class="flat-card">
                    <h3>🏠 <?php echo htmlspecialchars($flat['location']); ?></h3>
                    <p>📍 <?php echo htmlspecialchars($flat['detailed_location']); ?></p>
                    <div class="flat-info-row">
                        <span>📏 <strong><?php echo $flat['area']; ?> sqft</strong></span>
                        <span>🛏️ <strong><?php echo $flat['bedroom']; ?> beds</strong></span>
                        <span>⬆️ <strong>Floor <?php echo $flat['floor']; ?></strong></span>
                    </div>
                    <div class="flat-rent">💰 BDT <?php echo number_format($flat['asking_rent']); ?>/month</div>
                    <p style="font-size:13px;color:#6b7280;margin:12px 0">🔒 Owner details shown after agreement</p>
                    <a href="actions/request_flat_action.php?flat_id=<?php echo $flat['flat_id']; ?>" class="card-btn">📬 Send Request</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
