<?php
$role = isset($_GET['role']) ? $_GET['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - House Rent System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--muted:#6b7280}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,'Segoe UI',Roboto;background:linear-gradient(135deg,#667eea,#764ba2);min-height:100vh;padding:28px 16px}
        .reg-wrap{max-width:520px;margin:0 auto}
        .reg-card{background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.15);overflow:hidden;margin-bottom:16px}
        .reg-header{background:linear-gradient(135deg,#667eea,#764ba2);padding:28px 24px;color:#fff;text-align:center}
        .reg-header h2{font-size:22px;font-weight:700}
        .reg-body{padding:24px}
        .role-selection{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .role-btn{padding:18px;border-radius:12px;text-decoration:none;font-weight:700;text-align:center;transition:all 0.3s;border:2px solid #e5e7eb}
        .role-btn:hover{transform:translateY(-4px);box-shadow:0 10px 24px rgba(0,0,0,0.1)}
        .btn-owner{background:#e0e7ff;color:#3730a3;border-color:#6366f1}
        .btn-tenant{background:#dcfce7;color:#15803d;border-color:#16a34a}
        .back-link{display:inline-block;margin-top:12px;color:var(--primary);text-decoration:none;font-size:14px}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-weight:600;color:#1f2937;margin-bottom:6px;font-size:14px}
        .form-group input,.form-group select{width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px}
        .section-title{font-weight:700;color:#1f2937;margin:16px 0 12px;padding-bottom:8px;border-bottom:2px solid #f0f0f0}
        .reg-btn{width:100%;padding:12px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;transition:all 0.3s;margin-top:16px}
        .reg-btn:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(102,126,234,0.3)}
    </style>
</head>
<body>

    <div class="reg-wrap">
    <div class="reg-card">
        <div class="reg-header">
            <h2>Create Account</h2>
        </div>
        <div class="reg-body">
        <?php if ($role == ''): ?>
            <p style="color:#6b7280;margin-bottom:16px">Choose your registration type</p>
            <div class="role-selection">
                <a href="register.php?role=owner" class="role-btn btn-owner">🏠 I am an Owner</a>
                <a href="register.php?role=tenant" class="role-btn btn-tenant">👤 I am a Tenant</a>
            </div>
            <p style="text-align:center;margin-top:16px"><a href="login.php" class="back-link">← Back to Login</a></p>

        <?php else: ?>
            <p style="color:#6b7280;font-size:14px;margin-bottom:14px">Register as: <strong><?php echo ucfirst($role); ?></strong></p>
            <form action="actions/register_action.php" method="POST">
                <input type="hidden" name="role" value="<?php echo $role; ?>">

                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>NID Number *</label>
                    <input type="text" name="nid" required>
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>

                <?php if ($role == 'owner'): ?>
                    <div class="section-title">Bank Information</div>
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name">
                    </div>
                    <div class="form-group">
                        <label>Account Number</label>
                        <input type="text" name="account_number">
                    </div>
                    <div class="form-group">
                        <label>Branch Name</label>
                        <input type="text" name="branch_name">
                    </div>
                <?php endif; ?>

                <?php if ($role == 'tenant'): ?>
                    <div class="section-title">Additional Info</div>
                    <div class="form-group">
                        <label>Permanent Address</label>
                        <input type="text" name="permanent_address">
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation">
                    </div>
                <?php endif; ?>

                <button type="submit" class="reg-btn">Complete Registration</button>
                <p style="text-align:center;margin-top:12px"><a href="register.php" class="back-link">← Change Role</a></p>
            </form>
        <?php endif; ?>
        </div>
    </div>
    </div>
</body>
</html>