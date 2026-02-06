<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - X-STREAM</title>

    <link rel="icon" type="image/svg+xml" href="favicon-white-bg.svg">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #1E3A8A 0%, #3B82F6 50%, #60A5FA 100%);

            height: 100vh;
            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0;
            padding: 0;
        }

        .group {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            width: 100%;
            max-width: 500px;
        }

        .absolute {
            position: absolute;
            inset: -4px;
            background: linear-gradient(135deg, #3b82f6 0%, #22d3ee 50%, #3b82f6 100%);
            border-radius: 20px;
            filter: blur(12px);
            opacity: 0.25;
            transition: opacity 1s, filter 1s;
        }

        .group:hover .absolute {
            opacity: 0.5;
            filter: blur(16px);
            transition-duration: 200ms;
        }

        .relative {
            position: relative;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2rem 3rem;
            text-align: center;
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
            padding: 5px 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
        }

        .logo-img {
            height: 45px;
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
            margin: 0 auto 20px;
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
            margin-bottom: 15px;
            font-size: 32px;
            font-weight: bold;
        }

        .text-center p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.125rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
        }

        .info-box p {
            margin: 0.5rem 0;
            color: rgba(255, 255, 255, 0.95);
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .info-box p strong {
            margin-right: 10px;
            color: white;
        }

        .btn {
            display: inline-block;
            width: 100%;
            max-width: 288px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 300ms;
            margin-top: 20px;
            text-align: center;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }

        .logo {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="group">
        <div class="absolute"></div>

        <div class="relative">

            <div class="logo-container">
                <div class="logo-box">
                    <img src="{{ asset('x-stream-logo-with-text-darkmode.svg') }}"
                        alt="X-STREAM Logo"
                        class="logo-img">
                </div>
            </div>

            <div class="success-icon"></div>
            <h1>Email Verified!</h1>

            <div class="text-center">
                <p>Your X-STREAM account has been successfully verified.</p>
            </div>

            <div class="info-box">
                <p><strong>✓</strong> You can now log in to your account</p>
                <p><strong>✓</strong> Access all X-STREAM features</p>
                <p><strong>✓</strong> Receive river monitoring alerts</p>
            </div>

            <a href="https://x-stream.ucc.bsit4c.com/admin/signin" class="btn">Go to App Login</a>

            <p class="logo">© 2025 X-STREAM. All rights reserved.</p>

        </div>
    </div>

</body>

</html>