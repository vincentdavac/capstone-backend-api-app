<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Email - X-STREAM</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 30px;">

    <div style="max-width: 600px; margin: auto; background: #fff; border-radius: 10px; overflow: hidden;">
        
        <div style="background: #0d6efd; padding: 25px; text-align: center;">
            <h2 style="margin: 0; color: white;">X-STREAM</h2>
        </div>

        <div style="padding: 30px;">
            <h2>Email Verification Required</h2>
            <p>Hello {{ $user->email }},</p>
            <p>
                Thank you for signing up! Please verify your email address to activate your X-STREAM account.
            </p>

            <div style="text-align: center; margin: 35px 0;">
                <a href="{{ $verificationUrl }}" 
                   style="background: #0d6efd; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    Verify Email Address
                </a>
            </div>

            <p>
                This verification link will expire in <strong>60 minutes</strong>.
            </p>
            <p>
                If you did not create an account, please ignore this email.
            </p>

            <p>Stay safe,<br><strong>The X-STREAM Team</strong></p>
        </div>

        <div style="background: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666;">
            © 2025 X-STREAM. All rights reserved.<br>
            This is an automated message. Please do not reply.
        </div>

    </div>

</body>
</html>
