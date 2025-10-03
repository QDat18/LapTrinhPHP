<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification Code</title>
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
        .otp-box {
            background: #f0f7ff;
            border: 3px dashed #667eea;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
            border-radius: 10px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
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
        <h1>🔐 Security Verification</h1>
        <p>Your OTP Code</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->first_name }}!</h2>
        
        <p>To complete your phone verification, please use the following One-Time Password (OTP):</p>
        
        <div class="otp-box">
            <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Your OTP Code:</p>
            <div class="otp-code">{{ $otp }}</div>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">Valid for 10 minutes</p>
        </div>
        
        <div class="warning-box">
            <strong>⚠️ Security Notice:</strong><br>
            • Do not share this code with anyone<br>
            • This code will expire in 10 minutes<br>
            • If you didn't request this code, please ignore this email
        </div>
        
        <p><strong>Steps to verify:</strong></p>
        <ol>
            <li>Return to the verification page</li>
            <li>Enter the 6-digit code above</li>
            <li>Click "Verify" to complete the process</li>
        </ol>
        
        <p>If you're having trouble, you can request a new code on the verification page.</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} Volunteer Connect. All rights reserved.</p>
        <p>This is an automated email. Please do not reply.</p>
    </div>
</body>
</html>
