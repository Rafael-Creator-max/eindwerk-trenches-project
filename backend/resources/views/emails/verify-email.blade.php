<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #4a6cf7;
            border-radius: 8px 8px 0 0;
            color: white;
        }
        .content {
            padding: 30px;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4a6cf7;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Crypto Tracker!</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>Thank you for registering with Crypto Tracker. To get started, please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        </div>
        
        <p>If you did not create an account, no further action is required.</p>
        
        <p>Thanks,<br>The Crypto Tracker Team</p>
        
        <div class="footer">
            <p>If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:</p>
            <p>{{ $verificationUrl }}</p>
            <p>This verification link will expire in 60 minutes.</p>
            <p>&copy; {{ date('Y') }} Crypto Tracker. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
