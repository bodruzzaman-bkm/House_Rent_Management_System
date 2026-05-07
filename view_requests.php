<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];

    $query = "SELECT r.request_id, u.name, f.location, r.request_status, r.offer_advance
          FROM request r
          JOIN flat f ON r.flat_id = f.flat_id
          JOIN user u ON r.tenant_id = u.user_id
          WHERE f.owner_id = ? AND r.request_status IN ('Pending', 'In Process', 'Pending Advance Payment')";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $owner_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Requests</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8;--warning:#f59e0b;--danger:#ef4444}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .dashboard-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:24px;text-align:center}
        .dashboard-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .dashboard-header p{font-size:15px;opacity:0.9;margin:0}
        .dashboard-container{max-width:900px;margin:0 auto;padding:0 16px 24px}
        .back-link{display:inline-flex;align-items:center;gap:6px;padding:10px 14px;background:#fff;border-radius:8px;text-decoration:none;color:var(--primary);font-weight:600;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);transition:all 0.2s}
        .back-link:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .requests-grid{display:grid;gap:16px;margin-top:16px}
        .request-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:20px;border-left:4px solid var(--primary);transition:all 0.2s;display:flex;justify-content:space-between;align-items:center;gap:16px}
        .request-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,0.1)}
        .request-info{flex:1}
        .request-tenant{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px}
        .request-detail{font-size:14px;color:#6b7280;margin:4px 0}
        .request-status{display:inline-block;padding:4px 10px;border-radius:6px;font-weight:700;font-size:12px;margin:8px 0}
        .status-pending{background:#fef3c7;color:#92400e}
        .status-confirmed{background:#d1fae5;color:#065f46}
        .request-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
        .btn-confirm{padding:10px 14px;background:var(--accent);color:#fff;border:none;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:6px}
        .btn-confirm:hover{background:#059669;transform:translateY(-2px)}
        .no-requests{text-align:center;padding:40px;background:#fff;border-radius:12px;color:#6b7280}
        .no-requests h2{color:#0f172a;margin-bottom:12px}
        @media(max-width:800px){.request-card{flex-direction:column;align-items:flex-start}.request-actions{width:100%}.btn-confirm{width:100%;justify-content:center}}
    </style>
</head>
<body>
<div class="dashboard-header">
    <h1>📬 Rental Requests</h1>
    <p>Manage rental requests from potential tenants</p>
</div>

<div class="dashboard-container">
    <a href="owner_dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php if ($result->num_rows === 0): ?>
        <div class="no-requests">
            <h2>No Requests Yet</h2>
            <p>You haven't received any rental requests for your properties.</p>
        </div>
    <?php else: ?>
        <div class="requests-grid">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="request-card">
                    <div class="request-info">
                        <div class="request-tenant">👤 <?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="request-detail">🏠 <strong>Property:</strong> <?php echo htmlspecialchars($row['location']); ?></div>
                        <div class="request-status <?php 
                            if ($row['request_status'] === 'Pending') echo 'status-pending';
                            elseif ($row['request_status'] === 'In Process') echo 'status-pending';
                            elseif ($row['request_status'] === 'Pending Advance Payment') echo 'status-pending';
                        ?>">
                            <?php 
                                if ($row['request_status'] === 'Pending') echo '⏱ Pending Response';
                                elseif ($row['request_status'] === 'In Process') echo '✉️ Offer Sent';
                                elseif ($row['request_status'] === 'Pending Advance Payment') echo '💳 Awaiting Payment';
                            ?>
                        </div>
                        <?php if ($row['request_status'] === 'Pending Advance Payment'): ?>
                            <div style="margin-top:8px;padding:8px 10px;background:#fef3c7;border-radius:6px;font-size:12px;color:#92400e">
                                💡 Tenant is paying BDT <?php echo number_format($row['offer_advance'], 0); ?> advance
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($row['request_status'] === 'Pending') { ?>
                        <div class="request-actions">
                            <a href="confirm_request.php?id=<?php echo $row['request_id']; ?>" class="btn-confirm">✓ Accept Request</a>
                        </div>
                    <?php } elseif ($row['request_status'] === 'Pending Advance Payment') { ?>
                        <div class="request-actions">
                            <button class="btn-confirm" style="background:#9ca3af;cursor:not-allowed" disabled>⏳ Awaiting Payment</button>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
