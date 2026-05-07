<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$owner_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg)}
        .dashboard-header{background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:36px 24px;margin-bottom:24px}
        .dashboard-header h1{font-size:28px;margin-bottom:4px}
        .dashboard-header p{font-size:15px;opacity:0.9}
        .navbar{background:#fff;padding:16px 24px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);margin:0 24px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
        .navbar-title{font-size:16px;font-weight:700}
        .navbar-actions{display:flex;gap:10px;flex-wrap:wrap}
        .btn-nav{padding:10px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;transition:all 0.2s;display:inline-flex;align-items:center;gap:6px}
        .btn-add{background:var(--accent);color:#fff}
        .btn-requests{background:#8b5cf6;color:#fff}
        .btn-bills{background:var(--primary);color:#fff}
        .btn-manage{background:#f59e0b;color:#fff}
        .btn-logout{background:#ef4444;color:#fff;margin-left:auto}
        .btn-nav:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,0.1)}
        .dashboard-container{max-width:1100px;margin:0 auto;padding:0 16px 24px}
        .flats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px;margin-top:20px}
        .flat-card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:20px;border-left:4px solid var(--primary);transition:all 0.2s}
        .flat-card:hover{box-shadow:0 8px 20px rgba(0,0,0,0.1);transform:translateY(-4px)}
        .flat-header{display:flex;justify-content:space-between;align-items:start;margin-bottom:12px}
        .flat-title{font-size:18px;font-weight:700;color:#0f172a}
        .flat-status{padding:6px 12px;border-radius:6px;font-weight:700;font-size:12px}
        .status-available{background:#ecfdf5;color:#065f46}
        .status-rented{background:#fef3c7;color:#92400e}
        .flat-info{font-size:14px;color:#4b5563;margin:8px 0}
        .flat-label{color:#6b7280;font-weight:600}
        .flat-value{color:#1f2937;font-weight:500}
        .flat-rent{font-size:18px;font-weight:700;color:#059669;margin:12px 0}
        .no-flats{text-align:center;padding:40px;background:#fff;border-radius:12px;color:#6b7280}
        @media(max-width:820px){.navbar{flex-direction:column;align-items:flex-start}.navbar-actions{flex-direction:column;width:100%}.btn-logout{margin-left:0;width:100%;justify-content:center}.flats-grid{grid-template-columns:1fr}}
    </style>
</head>

<body>

    <div class="dashboard-header">
        <h1>👋 Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>Manage your properties and rental income</p>
    </div>

    <div class="navbar">
        <h2 class="navbar-title">Your Properties</h2>
        <div class="navbar-actions">
            <a href="post_flat.php" class="btn-nav btn-add">➕ Post Flat</a>
            <a href="view_requests.php" class="btn-nav btn-requests">📬 Requests</a>
            <a href="generate_bill.php" class="btn-nav btn-bills">📝 Generate Bill</a>
            <a href="owner_bills.php" class="btn-nav btn-manage">💳 Manage Bills</a>
            <a href="actions/logout_action.php" class="btn-nav btn-logout">🚪 Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        <?php
        $sql = "SELECT * FROM flat WHERE owner_id = ? ORDER BY flat_id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<div class="flats-grid">';
            while ($flat = $result->fetch_assoc()) {
                $flat_status = !empty($flat['status']) ? trim($flat['status']) : 'Unknown';
                $is_available = (strtolower($flat_status) === 'available');
        ?>

                <div class="flat-card">
                    <div class="flat-header">
                        <div class="flat-title">#<?php echo htmlspecialchars($flat['flat_id']); ?> - <?php echo htmlspecialchars($flat['location']); ?></div>
                        <div class="flat-status <?php echo $is_available ? 'status-available' : 'status-rented'; ?>">
                            <?php echo $is_available ? '✓ Available' : '✓ Rented'; ?>
                        </div>
                    </div>
                    <p class="flat-info"><span class="flat-label">📍 Address:</span> <?php echo htmlspecialchars($flat['detailed_location']); ?></p>
                    <div style="display:flex;gap:12px;font-size:13px;margin:10px 0">
                        <span class="flat-info">📏 <span class="flat-value"><?php echo $flat['area']; ?> sqft</span></span>
                        <span class="flat-info">🛏️ <span class="flat-value"><?php echo $flat['bedroom']; ?> bed</span></span>
                        <span class="flat-info">⬆️ <span class="flat-value">Floor <?php echo $flat['floor']; ?></span></span>
                    </div>
                    <div class="flat-rent">💰 BDT <?php echo number_format($flat['asking_rent']); ?>/month</div>
                </div>

        <?php
            }
            echo '</div>';
        } else {
            echo "<div class='no-flats'><p>📭 You haven't posted any flats yet.</p><p style='margin-top:12px'><a href='post_flat.php' style='color:var(--primary);font-weight:600;text-decoration:none'>➕ Click here to post your first flat</a></p></div>";
        }
        $stmt->close();
        ?>
    </div>

</body>

</html>