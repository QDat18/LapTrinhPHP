<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 10px 10px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔑 Password Reset Request</h1>
        <p>Volunteer Connect</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->first_name }}!</h2>
        
        <p>We received a request to reset the password for your Volunteer Connect account.</p>
        
        <p>Click the button below to reset your password:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">
                🔐 Reset Password
            </a>
        </div>
        
        <div class="warning-box">
            <strong>⏰ Important:</strong><br>
            • This link will expire in 24 hours<br>
            • If you didn't request this reset, please ignore this email<br>
            • Your password will remain unchanged until you create a new one
        </div>
        
        <p>If the button doesn't work, copy and paste this link into your browser:</p>
        <p style="word-break: break-all; color: #667eea;">{{ $resetUrl }}</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">
        
        <p><strong>Security Tips:</strong></p>
        <ul>
            <li>Use a strong, unique password</li>
            <li>Include uppercase, lowercase, numbers, and symbols</li>
            <li>Avoid using personal information</li>
            <li>Don't reuse passwords from other sites</li>
        </ul>
        
        <p>If you didn't request a password reset, your account may be at risk. Please contact our support team immediately.</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} Volunteer Connect. All rights reserved.</p>
        <p>Need help? Contact us at support@volunteerconnect.com</p>
    </div>
</body>
</html>
