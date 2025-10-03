<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
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
        .info-box {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🤝 Volunteer Connect</h1>
        <p>Welcome to Our Community!</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->first_name }}!</h2>
        
        <p>Thank you for registering with Volunteer Connect. We're excited to have you join our community of volunteers and organizations making a difference!</p>
        
        <p>To complete your registration, please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">
                ✅ Verify Email Address
            </a>
        </div>
        
        <div class="info-box">
            <strong>⏰ Important:</strong> This verification link will expire in 24 hours.
        </div>
        
        <p>If the button doesn't work, copy and paste this link into your browser:</p>
        <p style="word-break: break-all; color: #667eea;">{{ $verificationUrl }}</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">
        
        <p><strong>What's Next?</strong></p>
        <ol>
            <li>Verify your email address</li>
            <li>Enter the OTP code sent to your email</li>
            <li>Complete your profile</li>
            <li>Start making a difference!</li>
        </ol>
        
        <p>If you didn't create an account with Volunteer Connect, please ignore this email.</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} Volunteer Connect. All rights reserved.</p>
        <p>Need help? Contact us at support@volunteerconnect.com</p>
    </div>
</body>
</html>