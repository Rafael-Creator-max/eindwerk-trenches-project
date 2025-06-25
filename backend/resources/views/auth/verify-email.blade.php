<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email Address - Crypto Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7fafc;
            color: #2d3748;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #4a6cf7;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .button {
            display: inline-block;
            background-color: #4a6cf7;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-weight: 600;
            margin: 20px 0;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .button:hover {
            background-color: #3a5bd9;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #718096;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .text-center {
            text-align: center;
        }
        .message {
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .success {
            color: #38a169;
            background-color: #f0fff4;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c6f6d5;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .action-link {
            color: #4a6cf7;
            text-decoration: none;
            font-weight: 600;
        }
        .action-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 640px) {
            .actions {
                flex-direction: column;
                align-items: flex-start;
            }
            .button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div class="logo">Crypto Tracker</div>
                <h1>Verify Your Email Address</h1>
            </div>
            <div class="content">
                @if (session('status') == 'verification-link-sent')
                    <div class="success">
                        A new verification link has been sent to your email address.
                    </div>
                @endif

                <div class="message">
                    <p>Before continuing, please check your email for a verification link. If you didn't receive the email, click the button below to request another.</p>
                </div>

                <form method="POST" action="{{ route('verification.send') }}" class="text-center">
                    @csrf
                    <button type="submit" class="button">
                        Resend Verification Email
                    </button>
                </form>

                <div class="footer">
                    <div class="actions">
                        <a href="{{ route('profile.show') }}" class="action-link">
                            Edit Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="action-link" style="background: none; padding: 0; border: none; cursor: pointer;">
                                Log Out
                            </button>
                        </form>
                    </div>
                    <p style="margin-top: 15px; font-size: 13px; color: #a0aec0;">
                        &copy; {{ date('Y') }} Crypto Tracker. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
