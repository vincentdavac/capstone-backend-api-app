<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - X-STREAM</title>

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

        /* Glass content box */
        .container {
            position: relative;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
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

        h1 {
            color: white;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            text-align: left;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Style for hidden inputs */
        input[type="hidden"] {
            display: none;
        }

        /* Style for regular text inputs */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 48px 14px 16px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            color: white;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 40px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            user-select: none;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: white;
        }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(10px);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.2));
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .error {
            background: rgba(244, 67, 54, 0.15);
            backdrop-filter: blur(8px);
            border-left: 4px solid rgba(244, 67, 54, 0.6);
            padding: 12px;
            margin-bottom: 25px;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.95);
            font-size: 14px;
            text-align: left;
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .logo {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 30px;
            text-align: center;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="glass-container">
        <div class="gradient-border"></div>
        <div class="container">

            <!-- Logo Section -->
            <div class="logo-container">
                <div class="logo-box">
                    <img src="{{ asset('x-stream-logo-with-text-darkmode.svg') }}" alt="X-STREAM Logo" class="logo-img">
                </div>
            </div>

            <h1>Reset Your Password</h1>

            @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                {{ $error }}<br>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required placeholder="Enter new password">
                    <span class="toggle-password" onclick="togglePassword('password', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </span>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Confirm new password">
                    <span class="toggle-password" onclick="togglePassword('password_confirmation', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </span>
                </div>

                <button type="submit">Reset Password</button>
            </form>

            <p class="logo">© 2025 X-STREAM. All rights reserved.</p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, iconElement) {
            const field = document.getElementById(fieldId);
            const isPassword = field.type === 'password';

            field.type = isPassword ? 'text' : 'password';

            // Toggle icon
            if (isPassword) {
                // Show "visibility_off" icon
                iconElement.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                `;
            } else {
                // Show "visibility" icon
                iconElement.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                `;
            }
        }
    </script>
</body>

</html>