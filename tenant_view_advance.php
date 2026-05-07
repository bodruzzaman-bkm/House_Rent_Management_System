<?php
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['user_id'];

$query = "SELECT a.advance, a.first_month_rent, f.location
          FROM agreement a
          JOIN links l ON a.agreement_id = l.agreement_id
          JOIN flat f ON l.flat_id = f.flat_id
          WHERE l.tenant_id = '$tenant_id'";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Advance Payments</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .dashboard-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:24px;text-align:center}
        .dashboard-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .dashboard-header p{font-size:15px;opacity:0.9;margin:0}
        .dashboard-container{max-width:800px;margin:0 auto;padding:0 16px 24px}
        .back-link{display:inline-flex;align-items:center;gap:6px;padding:10px 14px;background:#fff;border-radius:8px;text-decoration:none;color:var(--primary);font-weight:600;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);transition:all 0.2s}
        .back-link:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .advance-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px;margin-top:16px}
        .advance-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:20px;border-left:4px solid var(--accent);transition:all 0.2s}
        .advance-card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(0,0,0,0.1)}
        .advance-location{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:12px}
        .advance-row{display:flex;justify-content:space-between;padding:10px 0;font-size:14px;border-bottom:1px solid #e5e7eb}
        .advance-row:last-child{border-bottom:none}
        .advance-label{color:#6b7280;font-weight:600}
        .advance-value{color:#1f2937;font-weight:500;text-align:right}
        .amount-value{color:var(--accent);font-weight:700;font-size:16px}
        .no-advances{text-align:center;padding:40px;background:#fff;border-radius:12px;color:#6b7280}
        .no-advances h2{color:#0f172a;margin-bottom:12px}
        @media(max-width:800px){.advance-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="dashboard-header">
    <h1>💰 Your Advance Payments</h1>
    <p>View your advance and initial rental payments</p>
</div>

<div class="dashboard-container">
    <a href="tenant_dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php if ($result->num_rows === 0): ?>
        <div class="no-advances">
            <h2>No Advance Payments</h2>
            <p>You don't have any active rental agreements with advance payments yet.</p>
        </div>
    <?php else: ?>
        <div class="advance-grid">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="advance-card">
                    <div class="advance-location">🏠 <?php echo htmlspecialchars($row['location']); ?></div>
                    <div class="advance-row">
                        <span class="advance-label">💳 Advance Payment</span>
                        <span class="advance-value amount-value">BDT <?php echo number_format($row['advance'], 2); ?></span>
                    </div>
                    <div class="advance-row">
                        <span class="advance-label">📅 First Month Rent</span>
                        <span class="advance-value amount-value">BDT <?php echo number_format($row['first_month_rent'], 2); ?></span>
                    </div>
                    <div class="advance-row">
                        <span class="advance-label">💵 Total Paid</span>
                        <span class="advance-value amount-value">BDT <?php echo number_format($row['advance'] + $row['first_month_rent'], 2); ?></span>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
