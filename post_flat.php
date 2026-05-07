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
    <style>
        :root{--primary:#10b981;--bg:#f4f6f8}
        body{background:var(--bg);margin:0;padding:0}
        .page-header{background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:36px 24px;margin-bottom:32px;text-align:center}
        .page-header h1{font-size:28px;margin:0 0 4px;font-weight:700}
        .page-header p{font-size:15px;opacity:0.9;margin:0}
        .form-container{max-width:600px;margin:0 auto;padding:0 16px 32px;background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);padding:32px}
        .form-group{margin-bottom:20px;display:flex;flex-direction:column}
        .form-group label{margin-bottom:8px;font-weight:600;color:#0f172a;font-size:14px}
        .form-group input{padding:12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;transition:all 0.2s}
        .form-group input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(16,185,129,0.1)}
        .form-group input::placeholder{color:#9ca3af}
        .form-actions{display:flex;gap:12px;margin-top:24px}
        .btn-submit{flex:1;padding:12px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;transition:all 0.2s}
        .btn-submit:hover{background:#059669;transform:translateY(-2px);box-shadow:0 6px 16px rgba(16,185,129,0.3)}
        .btn-cancel{flex:1;padding:12px;background:#6b7280;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:15px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;transition:all 0.2s}
        .btn-cancel:hover{background:#4b5563;transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,0.2)}
        .form-title{font-size:20px;font-weight:700;color:#0f172a;margin-bottom:24px;text-align:center}
        @media(max-width:600px){.form-container{padding:24px}.form-group input{font-size:16px}}
    </style>
</head>

<body>

    <div class="page-header">
        <h1>🏠 Post New Flat</h1>
        <p>Create a listing for your rental property</p>
    </div>

    <div class="form-container">
        <h2 class="form-title">📝 Property Details</h2>

        <form action="actions/post_flat_action.php" method="POST">

            <div class="form-group">
                <label for="location">📍 Location Area *</label>
                <input type="text" id="location" name="location" placeholder="e.g., Mirpur, Dhanmondi" required>
            </div>

            <div class="form-group">
                <label for="detailed_location">🗺️ Detailed Address *</label>
                <input type="text" id="detailed_location" name="detailed_location" placeholder="e.g., Road 5, House 12/A" required>
            </div>

            <div class="form-group">
                <label for="area">📏 Flat Size (Sq. Feet) *</label>
                <input type="number" id="area" name="area" placeholder="e.g., 1200" required>
            </div>

            <div class="form-group">
                <label for="bedroom">🛏️ Number of Bedrooms *</label>
                <input type="number" id="bedroom" name="bedroom" placeholder="e.g., 3" required>
            </div>

            <div class="form-group">
                <label for="floor">⬆️ Floor Number *</label>
                <input type="number" id="floor" name="floor" placeholder="e.g., 4" required>
            </div>

            <div class="form-group">
                <label for="asking_rent">💰 Asking Rent (BDT/month) *</label>
                <input type="number" id="asking_rent" name="asking_rent" placeholder="e.g., 25000" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">✓ Publish Listing</button>
                <a href="owner_dashboard.php" class="btn-cancel">✕ Cancel</a>
            </div>
        </form>
    </div>

</body>

</html>