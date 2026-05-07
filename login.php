<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - House Rent System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root{--primary:#2563eb;--accent:#10b981;--muted:#6b7280;--bg:#f3f4f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,'Segoe UI',Roboto;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}
        .login-wrap{width:100%;max-width:420px}
        .login-card{background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.15);overflow:hidden}
        .login-header{background:linear-gradient(135deg,#667eea,#764ba2);padding:32px 24px;color:#fff;text-align:center}
        .login-header h1{font-size:24px;margin-bottom:8px;font-weight:700}
        .login-header p{font-size:14px;opacity:0.9}
        .login-body{padding:32px 24px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-weight:600;color:#1f2937;margin-bottom:8px;font-size:14px}
        .form-group input{width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:8px;font-size:14px;transition:all 0.2s}
        .form-group input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,0.1)}
        .login-btn{width:100%;padding:12px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:16px;cursor:pointer;transition:all 0.3s;margin-top:8px}
        .login-btn:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(102,126,234,0.3)}
        .login-footer{text-align:center;padding-top:20px;border-top:1px solid #e5e7eb}
        .login-footer p{color:#6b7280;font-size:14px;margin-bottom:8px}
        .login-footer a{color:var(--primary);text-decoration:none;font-weight:600;transition:color 0.2s}
        .login-footer a:hover{color:#1e40af}
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>House Rent Management System</p>
        </div>
        <div class="login-body">
            <form action="actions/login_action.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">Sign In</button>
            </form>
            <div class="login-footer">
                <p>Don't have an account?</p>
                <a href="register.php">Create Account →</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>