<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 10px 10px;
            font-size: 14px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message-content {
            white-space: pre-wrap;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 24px;">
            <span style="font-size: 32px;">🤝</span><br>
            VolunteerConnect
        </h1>
    </div>
    
    <div class="content">
        <div class="message-content">
            {{ $messageContent }}
        </div>
        
        <a href="{{ config('app.url') }}" class="button">
            Visit VolunteerConnect
        </a>
    </div>
    
    <div class="footer">
        <p>You're receiving this email because you're a member of VolunteerConnect.</p>
        <p>
            <a href="{{ config('app.url') }}" style="color: #667eea; text-decoration: none;">VolunteerConnect</a> | 
            <a href="{{ config('app.url') }}/privacy" style="color: #667eea; text-decoration: none;">Privacy Policy</a> | 
            <a href="{{ config('app.url') }}/contact" style="color: #667eea; text-decoration: none;">Contact Us</a>
        </p>
        <p style="margin-top: 10px; font-size: 12px;">
            © {{ date('Y') }} VolunteerConnect. All rights reserved.
        </p>
    </div>
</body>
</html>