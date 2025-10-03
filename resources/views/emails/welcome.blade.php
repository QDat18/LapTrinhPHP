<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Volunteer Connect!</title>
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
            padding: 40px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e0e0e0;
        }
        .feature-box {
            background: #f8f9fa;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Welcome to Volunteer Connect!</h1>
        <p>Let's Make a Difference Together</p>
    </div>
    
    <div class="content">
        <h2>Welcome {{ $user->first_name }}! 👋</h2>
        
        <p>Congratulations! Your account is now fully verified and ready to use. We're thrilled to have you as part of our community dedicated to making positive change.</p>
        
        <div style="text-align: center;">
            <a href="{{ route('volunteer.dashboard') }}" class="button">
                🚀 Get Started
            </a>
        </div>
        
        <h3>What You Can Do Now:</h3>
        
        <div class="feature-box">
            <strong>🔍 Discover Opportunities</strong><br>
            Browse hundreds of volunteer opportunities matching your skills and interests
        </div>
        
        <div class="feature-box">
            <strong>🤝 Connect with Organizations</strong><br>
            Find reputable NGOs and organizations making real impact
        </div>
        
        <div class="feature-box">
            <strong>📊 Track Your Impact</strong><br>
            Log your volunteer hours and see the difference you're making
        </div>
        
        <div class="feature-box">
            <strong>⭐ Build Your Profile</strong><br>
            Earn badges, get reviews, and showcase your volunteer journey
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">
        
        <h3>Quick Tips to Get Started:</h3>
        <ol>
            <li><strong>Complete your profile</strong> - Add your skills and interests</li>
            <li><strong>Browse opportunities</strong> - Find causes you care about</li>
            <li><strong>Apply and connect</strong> - Reach out to organizations</li>
            <li><strong>Make an impact</strong> - Start volunteering and changing lives!</li>
        </ol>
        
        <p>Have questions? Our support team is here to help you every step of the way.</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} Volunteer Connect. All rights reserved.</p>
        <p>📧 support@volunteerconnect.com | 📱 +84 123 456 789</p>
        <p style="margin-top: 10px;">
            <a href="{{ route('home') }}" style="color: #667eea; margin: 0 10px;">Home</a> |
            <a href="{{ route('opportunities.index') }}" style="color: #667eea; margin: 0 10px;">Browse</a> |
            <a href="{{ route('about') }}" style="color: #667eea; margin: 0 10px;">About</a>
        </p>
    </div>
</body>
</html>