<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Success - X-STREAM</title>

    <link rel="icon" type="image/svg+xml" href="/favicon-white-bg.svg">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #1E3A8A 0%, #3B82F6 50%, #60A5FA 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .glass-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 1rem;
        }

        .gradient-border {
            position: absolute;
            inset: -4px;
            background: linear-gradient(135deg, #3b82f6 0%, #22d3ee 50%, #3b82f6 100%);
            border-radius: 20px;
            filter: blur(12px);
            opacity: 0.25;
            transition: opacity 1s, filter 1s;
        }

        .glass-container:hover .gradient-border {
            opacity: 0.5;
            filter: blur(16px);
            transition-duration: 200ms;
        }

        .container {
            position: relative;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
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

        .logo-container {
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
        }

        .logo-box {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            padding: 12px 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
        }

        .logo-img {
            height: 40px;
            width: auto;
            opacity: 0.95;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(76, 175, 80, 0.2);
            backdrop-filter: blur(4px);
            border-radius: 50%;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
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
            color: white;
            margin-bottom: 20px;
            font-size: 32px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 15px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        p strong {
            color: white;
        }

        .message-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .message-box p {
            margin: 0;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.95);
        }

        .logo {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 30px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="glass-container">
        <div class="gradient-border"></div>
        <div class="container">

            <div class="logo-container">
                <div class="logo-box">
                    <img src="{{ asset('x-stream-logo-with-text-darkmode.svg') }}"
                        alt="X-STREAM Logo"
                        class="logo-img">
                </div>
            </div>

            <div class="success-icon"></div>
            <h1>Password Reset Successfully!</h1>

            <p>Your password has been changed.</p>

            <div class="message-box">
                <p><strong>You can now close this page and log in to the X-STREAM app with your new password.</strong></p>
            </div>

            <p class="logo">© 2025 X-STREAM</p>
        </div>
    </div>
</body>

</html>