<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Verify Email - X-STREAM</title>
</head>

<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(180deg, #f0f7ff 0%, #e6f2ff 100%); padding: 30px; margin: 0;">

    <div style="max-width: 600px; margin: auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 40px rgba(30, 58, 138, 0.12);">

        <div style="background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%); padding: 30px; text-align: center;">
            <h2 style="margin: 0; color: white; font-size: 28px; font-weight: 600; letter-spacing: 0.5px;">X-STREAM</h2>
        </div>

        <div style="padding: 40px 35px;">
            <h2 style="color: #1E3A8A; margin-bottom: 20px; font-size: 24px; font-weight: 600;">Verify Your Email Address</h2>

            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 15px;">Hello <strong style="color: #3B82F6;">{{ $user->email }}</strong>,</p>

            <p style="color: #4b5563; line-height: 1.6; margin-bottom: 25px;">
                Thank you for signing up! Please verify your email address to activate your X-STREAM account and access river monitoring features.
            </p>

            <div style="text-align: center; margin: 35px 0;">
                <a href="{{ $verificationUrl }}"
                    style="background: linear-gradient(135deg, #3B82F6 0%, #1E3A8A 100%); 
                          color: white; 
                          padding: 16px 35px; 
                          text-decoration: none; 
                          border-radius: 10px; 
                          font-weight: 600;
                          font-size: 16px;
                          display: inline-block;
                          box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
                          transition: all 0.3s ease;">
                    Verify Email Address
                </a>
            </div>

            <div style="background: rgba(59, 130, 246, 0.05); 
                       border-left: 4px solid #3B82F6; 
                       padding: 18px; 
                       margin: 25px 0;
                       border-radius: 0 8px 8px 0;">
                <p style="margin: 0; color: #1E3A8A; font-weight: 500;">
                    ⏰ This verification link will expire in <strong style="color: #1E3A8A;">60 minutes</strong>.
                </p>
            </div>

            <div style="background: #f8fafc; 
                       border: 1px solid #e2e8f0; 
                       border-radius: 8px; 
                       padding: 18px; 
                       margin: 25px 0;">
                <p style="margin: 0; color: #64748b; font-size: 14px;">
                    <strong style="color: #1E3A8A;">Note:</strong> If you did not create an account, please ignore this email or contact our support team immediately.
                </p>
            </div>

            <div style="margin: 30px 0;">
                <p style="color: #1E3A8A; font-weight: 500; margin-bottom: 12px;">After verification, you can:</p>
                <ul style="color: #4b5563; padding-left: 20px; line-height: 1.7;">
                    <li>Access real-time river monitoring data</li>
                    <li>Receive critical flood alerts</li>
                    <li>View historical analytics</li>
                    <li>Manage monitoring stations</li>
                </ul>
            </div>

            <p style="color: #4b5563; line-height: 1.6; margin-top: 30px;">
                Stay safe,<br>
                <strong style="color: #1E3A8A;">The X-STREAM Team</strong>
            </p>
        </div>

        <div style="background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%); 
                   padding: 25px; 
                   text-align: center; 
                   color: rgba(255, 255, 255, 0.85);">
            <p style="margin: 0 0 10px 0; font-size: 14px;">
                © 2025 X-STREAM. All rights reserved.
            </p>
            <p style="margin: 0; font-size: 12px; color: rgba(255, 255, 255, 0.7);">
                This is an automated message. Please do not reply to this email.
            </p>
            <p style="margin: 10px 0 0 0; font-size: 11px; color: rgba(255, 255, 255, 0.6);">
                River Monitoring System
            </p>
        </div>

    </div>

</body>

</html>