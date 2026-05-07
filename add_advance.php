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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Advance Payment</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .page-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:36px 24px;margin-bottom:32px;text-align:center}
        .page-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .form-container{max-width:500px;margin:0 auto;padding:0 16px 32px;background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:32px}
        .message-alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600}
        .message-success{background:#d1fae5;border-left:4px solid var(--accent);color:#065f46}
        .message-error{background:#fee2e2;border-left:4px solid #ef4444;color:#991b1b}
        .form-group{margin-bottom:20px;display:flex;flex-direction:column}
        .form-group label{margin-bottom:8px;font-weight:600;color:#0f172a;font-size:14px}
        .form-group input{padding:12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;transition:all 0.2s}
        .form-group input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,0.1)}
        .form-group input::placeholder{color:#9ca3af}
        .form-actions{display:flex;gap:12px;margin-top:24px}
        .btn-submit{flex:1;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;transition:all 0.2s}
        .btn-submit:hover{background:#059669;transform:translateY(-2px);box-shadow:0 6px 16px rgba(16,185,129,0.3)}
        .btn-back{flex:1;padding:12px;background:#6b7280;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:all 0.2s}
        .btn-back:hover{background:#4b5563;transform:translateY(-2px)}
        .form-title{font-size:20px;font-weight:700;color:#0f172a;margin-bottom:24px;text-align:center}
        @media(max-width:600px){.form-container{padding:24px}}
    </style>
</head>
<body>
<div class="page-header">
    <h1>💳 Add Advance Payment</h1>
</div>

<div class="form-container">
    <h2 class="form-title">Record Advance Payment</h2>

    <?php if ($message): ?>
        <div class="message-alert <?php echo strpos($message, 'Error') === false ? 'message-success' : 'message-error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="agreement_id">📋 Agreement ID *</label>
            <input type="number" id="agreement_id" name="agreement_id" placeholder="e.g., 1001" required>
        </div>

        <div class="form-group">
            <label for="advance">💰 Advance Amount (BDT) *</label>
            <input type="number" id="advance" name="advance" placeholder="e.g., 50000" step="0.01" min="0" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">✓ Save Advance</button>
            <a href="owner_dashboard.php" class="btn-back">← Back</a>
        </div>
    </form>
</div>
</body>
</html>
