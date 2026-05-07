<?php
include("db.php");

$message = '';

function ensureAgreementFirstMonthRentColumn($conn)
{
    $checkSql = "SELECT COUNT(*) AS column_count
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'agreement'
                   AND COLUMN_NAME = 'first_month_rent'";
    $checkResult = $conn->query($checkSql);
    $row = $checkResult ? $checkResult->fetch_assoc() : null;

    if (!$row || intval($row['column_count']) === 0) {
        $conn->query("ALTER TABLE agreement ADD COLUMN first_month_rent int(11) NOT NULL DEFAULT 0 AFTER start_date");
    }
}

if (isset($_POST['submit'])) {
    ensureAgreementFirstMonthRentColumn($conn);

    $tenant_id = intval($_POST['tenant_id']);
    $flat_id = intval($_POST['flat_id']);
    $owner_id = intval($_POST['owner_id']);
    $advance = floatval($_POST['advance']);
    $start_date = trim($_POST['start_date']);
    $month = date("F-Y", strtotime($start_date));

    if (!$tenant_id || !$flat_id || !$owner_id || !$start_date) {
        $message = 'All fields are required.';
    } else {
        $res = $conn->query("SELECT asking_rent FROM flat WHERE flat_id='$flat_id'");
        $data = $res->fetch_assoc();
        $base_rent = $data['asking_rent'];

        $sql1 = "INSERT INTO agreement (advance, start_date, first_month_rent, owner_id)
                 VALUES ('$advance','$start_date','$base_rent','$owner_id')";

        if ($conn->query($sql1)) {
            $agreement_id = $conn->insert_id;

            $conn->query("INSERT INTO links (agreement_id, tenant_id, flat_id)
                          VALUES ('$agreement_id', '$tenant_id', '$flat_id')");

            $sql2 = "INSERT INTO monthly_bill 
                     (agreement_id, billing_month, base_rent, maintanance, electricity, gas, water, service_charge, total_amount, payment_status)
                     VALUES ('$agreement_id','$month','$base_rent',0,0,0,0,0,'$base_rent','Unpaid')";

            if ($conn->query($sql2)) {
                $message = 'Agreement created and first month bill recorded successfully.';
            } else {
                $message = 'Bill Error: ' . $conn->error;
            }
        } else {
            $message = 'Agreement Error: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Rental Agreement</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .page-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:32px;text-align:center}
        .page-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .form-container{max-width:600px;margin:0 auto;padding:0 16px 32px;background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:32px}
        .message-alert{padding:14px 16px;border-radius:8px;margin-bottom:20px;font-weight:600;border-left:4px solid}
        .success-message{background:#d1fae5;border-color:var(--accent);color:#065f46}
        .error-message{background:#fee2e2;border-color:#ef4444;color:#991b1b}
        .form-group{margin-bottom:20px;display:flex;flex-direction:column}
        .form-group label{margin-bottom:8px;font-weight:600;color:#0f172a;font-size:14px}
        .form-group input{padding:12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;transition:all 0.2s}
        .form-group input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,0.1)}
        .form-group input::placeholder{color:#9ca3af}
        .form-actions{display:flex;gap:12px;margin-top:24px}
        .btn-submit{flex:1;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;transition:all 0.2s}
        .btn-submit:hover{background:#059669;transform:translateY(-2px);box-shadow:0 6px 16px rgba(16,185,129,0.3)}
        .form-title{font-size:20px;font-weight:700;color:#0f172a;margin-bottom:24px;text-align:center}
        .form-info{background:#ede9fe;border-left:4px solid #8b5cf6;padding:12px;border-radius:6px;margin-bottom:20px;font-size:13px;color:#5b21b6}
        @media(max-width:600px){.form-container{padding:24px}}
    </style>
</head>
<body>
<div class="page-header">
    <h1>📋 Create Rental Agreement</h1>
</div>

<div class="form-container">
    <h2 class="form-title">New Agreement Details</h2>

    <?php if ($message): ?>
        <div class="message-alert <?php echo strpos($message, 'Error') === false && strpos($message, 'error') === false ? 'success-message' : 'error-message'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="form-info">
        <strong>📌 Note:</strong> Enter tenant, flat, and owner IDs along with advance amount and start date to create a new rental agreement.
    </div>

    <form method="POST">
        <div class="form-group">
            <label for="tenant_id">👤 Tenant ID *</label>
            <input type="number" id="tenant_id" name="tenant_id" placeholder="e.g., 101" required>
        </div>

        <div class="form-group">
            <label for="flat_id">🏠 Flat ID *</label>
            <input type="number" id="flat_id" name="flat_id" placeholder="e.g., 1" required>
        </div>

        <div class="form-group">
            <label for="owner_id">👨‍💼 Owner ID *</label>
            <input type="number" id="owner_id" name="owner_id" placeholder="e.g., 1" required>
        </div>

        <div class="form-group">
            <label for="advance">💰 Advance Amount (BDT) *</label>
            <input type="number" id="advance" name="advance" step="0.01" min="0" placeholder="e.g., 50000" required>
        </div>

        <div class="form-group">
            <label for="start_date">📅 Agreement Start Date *</label>
            <input type="date" id="start_date" name="start_date" required>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit" class="btn-submit">✓ Create Agreement</button>
        </div>
    </form>
</div>
</body>
</html>