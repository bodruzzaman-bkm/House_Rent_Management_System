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
        :root{--primary:#2563eb;--accent:#10b981;--owner:#6366f1;--tenant:#f59e0b;--bg:#f4f6f8;--dark:#0f172a;--light:#fff}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;padding:32px 16px}
        .reg-wrap{max-width:500px;margin:0 auto}
        .reg-card{background:#fff;border-radius:16px;box-shadow:0 24px 64px rgba(0,0,0,0.12);overflow:hidden;margin-bottom:20px}
        .reg-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:36px 24px;color:#fff;text-align:center}
        .reg-header h2{font-size:26px;font-weight:700;letter-spacing:-0.5px}
        .reg-header p{font-size:13px;color:rgba(255,255,255,0.9);margin-top:6px}
        .reg-body{padding:32px 24px}
        .role-selection{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:12px}
        .role-btn{padding:20px;border-radius:12px;text-decoration:none;font-weight:700;text-align:center;transition:all 0.3s;border:3px solid transparent;display:flex;flex-direction:column;align-items:center;gap:8px;cursor:pointer}
        .role-btn:hover{transform:translateY(-6px);box-shadow:0 12px 32px rgba(0,0,0,0.15)}
        .role-icon{font-size:32px}
        .btn-owner{background:linear-gradient(135deg,#e0e7ff,#ede9fe);color:#4f46e5;border-color:#6366f1;box-shadow:0 4px 12px rgba(99,102,241,0.15)}
        .btn-owner:hover{border-color:#4f46e5;background:linear-gradient(135deg,#c7d2fe,#ddd6fe)}
        .btn-tenant{background:linear-gradient(135deg,#fef3c7,#fed7aa);color:#d97706;border-color:#f59e0b;box-shadow:0 4px 12px rgba(245,158,11,0.15)}
        .btn-tenant:hover{border-color:#d97706;background:linear-gradient(135deg,#fde68a,#fdba74)}
        .back-link{display:inline-block;margin-top:12px;color:var(--primary);text-decoration:none;font-size:14px;font-weight:600;transition:color 0.2s}
        .back-link:hover{color:#1e40af;text-decoration:underline}
        .intro-text{color:#6b7280;margin-bottom:16px;font-size:14px;line-height:1.6}
        .role-info{color:#6b7280;font-size:13px;margin-bottom:20px;padding:12px 14px;background:#f9fafb;border-left:4px solid var(--accent);border-radius:6px}
        .form-group{margin-bottom:16px;display:flex;flex-direction:column}
        .form-group label{display:block;font-weight:600;color:var(--dark);margin-bottom:8px;font-size:14px}
        .form-group input,.form-group select{padding:12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;transition:all 0.2s;font-family:inherit}
        .form-group input:focus,.form-group select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,0.1)}
        .form-group input::placeholder{color:#9ca3af}
        .section-title{font-weight:700;color:var(--dark);margin:20px 0 14px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;font-size:15px;letter-spacing:0.3px}
        .section-icon{margin-right:6px}
        .reg-btn{width:100%;padding:13px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:15px;cursor:pointer;transition:all 0.3s;margin-top:20px;display:flex;align-items:center;justify-content:center;gap:8px}
        .reg-btn:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(102,126,234,0.35)}
        .reg-btn:active{transform:translateY(-1px)}
        .login-link{text-align:center;margin-top:16px;font-size:14px;color:#6b7280}
        .login-link a{color:var(--primary);font-weight:600;text-decoration:none;transition:color 0.2s}
        .login-link a:hover{color:#1e40af;text-decoration:underline}
        @media(max-width:600px){.reg-header{padding:28px 20px}.reg-body{padding:24px 20px}.role-selection{gap:12px}.role-btn{padding:16px}}
    </style>
</head>
<body>

    <div class="reg-wrap">
    <div class="reg-card">
        <div class="reg-header">
            <h2>Create Account</h2>
            <p>Join House Rent Management System</p>
        </div>
        <div class="reg-body">
        <?php if ($role == ''): ?>
            <p class="intro-text">🚀 Choose your account type to get started</p>
            <div class="role-selection">
                <a href="register.php?role=owner" class="role-btn btn-owner">
                    <span class="role-icon">🏠</span>
                    <span>Property Owner</span>
                </a>
                <a href="register.php?role=tenant" class="role-btn btn-tenant">
                    <span class="role-icon">👤</span>
                    <span>Tenant</span>
                </a>
            </div>
            <p style="text-align:center;margin-top:18px">Already have an account? <a href="login.php" class="back-link">Sign In →</a></p>

        <?php else: ?>
            <div class="role-info">
                📋 Registering as: <strong><?php echo ucfirst($role) === 'Owner' ? '🏠 Property Owner' : '👤 Tenant'; ?></strong>
            </div>
            <form action="actions/register_action.php" method="POST">
                <input type="hidden" name="role" value="<?php echo $role; ?>">

                <div class="section-title"><span class="section-icon">👤</span>Personal Information</div>
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="nid">NID Number *</label>
                    <input type="text" id="nid" name="nid" placeholder="e.g., 1234567890" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone" placeholder="e.g., 01700000000" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Create a secure password" required>
                </div>

                <?php if ($role == 'owner'): ?>
                    <div class="section-title"><span class="section-icon">🏦</span>Bank Information</div>
                    <div class="form-group">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" placeholder="e.g., Dhaka Bank">
                    </div>
                    <div class="form-group">
                        <label for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number" placeholder="Your account number">
                    </div>
                    <div class="form-group">
                        <label for="branch_name">Branch Name</label>
                        <input type="text" id="branch_name" name="branch_name" placeholder="e.g., Mirpur Branch">
                    </div>
                <?php endif; ?>

                <?php if ($role == 'tenant'): ?>
                    <div class="section-title"><span class="section-icon">📍</span>Additional Information</div>
                    <div class="form-group">
                        <label for="permanent_address">Permanent Address</label>
                        <input type="text" id="permanent_address" name="permanent_address" placeholder="Your home address">
                    </div>
                    <div class="form-group">
                        <label for="occupation">Occupation</label>
                        <input type="text" id="occupation" name="occupation" placeholder="e.g., Software Engineer">
                    </div>
                <?php endif; ?>

                <button type="submit" class="reg-btn">✓ Complete Registration</button>
                <p class="login-link">Want to change role? <a href="register.php">← Start Over</a></p>
            </form>
        <?php endif; ?>
        </div>
    </div>
    </div>
</body>
</html>