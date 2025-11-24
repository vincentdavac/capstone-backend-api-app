<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Success - X-STREAM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            max-width: 500px;
            text-align: center;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease 0.3s backwards;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        .success-icon::after {
            content: "✓";
            color: white;
            font-size: 50px;
            font-weight: bold;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 32px;
        }
        p {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 14px;
            color: #999;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon"></div>
        <h1>Password Reset Successfully! 🎉</h1>
        <p>Your password has been changed.</p>
        <p><strong>You can now close this page and log in to the X-STREAM app with your new password.</strong></p>
        <p class="logo">© 2025 X-STREAM</p>
    </div>
</body>
</html>