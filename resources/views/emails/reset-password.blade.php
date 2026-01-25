<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Your Password | X-STREAM</title>
  <style>
    /* General Reset */
    body {
      background-color: #f4f6fa;
      margin: 0;
      padding: 0;
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      color: #333333;
    }

    /* Container */
    .email-wrapper {
      width: 100%;
      padding: 40px 0;
    }

    .email-content {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

.email-header {
  position: relative;
  background: linear-gradient(135deg, #007bff, #0056d2);
  color: #fff;
  text-align: center;
  padding: 35px 20px;
  overflow: hidden;
}

.email-header::after {
  content: "";
  position: absolute;
  top: 0;
  left: -50%;
  width: 200%;
  height: 100%;
  background: linear-gradient(120deg, rgba(255,255,255,0.15) 0%, transparent 50%, rgba(255,255,255,0.15) 100%);
  transform: skewX(-20deg);
  opacity: 0.5;
}


    .email-header img {
      height: 60px;
      margin-bottom: 12px;
    }

    .email-header h2 {
      font-size: 26px;
      letter-spacing: 1px;
      margin: 0;
      font-weight: 700;
    }

    /* Body */
    .email-body {
      padding: 35px 40px;
      text-align: left;
      line-height: 1.7;
    }

    .email-body h1 {
      font-size: 20px;
      color: #111827;
      margin-bottom: 16px;
    }

    .email-body p {
      font-size: 15px;
      color: #444;
      margin-bottom: 20px;
    }

    /* Button */
    .button-container {
      text-align: center;
      margin: 30px 0;
    }

    .button {
      background-color: #007bff;
      color: #ffffff !important;
      padding: 12px 28px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
    }

    .button:hover {
      background-color: #0056d2;
    }

    /* Footer */
    .email-footer {
      text-align: center;
      color: #888;
      font-size: 13px;
      padding: 20px;
      background-color: #f9fafb;
      border-top: 1px solid #eee;
    }

    /* Responsive Design */
    @media only screen and (max-width: 620px) {
      .email-body {
        padding: 25px 20px;
      }
      .email-header h2 {
        font-size: 22px;
      }
      .button {
        padding: 10px 22px;
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="email-wrapper">
    <div class="email-content">
      <!-- Header -->
      <div class="email-header">
<!-- <img src="{{ asset('favicon.svg') }}" alt="X-STREAM Logo" ...> -->
        <h2>X-STREAM</h2>
      </div>

      <!-- Body -->
      <div class="email-body">
        <h1>Password Reset Request</h1>
        <p>Hello {{ $email }},</p>
        <p>
          You’re receiving this email because we received a password reset request for your X-STREAM account.
        </p>

        <div class="button-container">
          <a href="{{ $resetUrl }}" class="button" target="_blank">Reset Password</a>
        </div>

        <p>
          This password reset link will expire in <strong>60 minutes</strong>.<br>
          If you did not request a password reset, you can safely ignore this email.
        </p>

        <p>Stay safe,<br><strong>The X-STREAM Team</strong></p>
      </div>

      <!-- Footer -->
      <div class="email-footer">
        © {{ date('Y') }} X-STREAM. All rights reserved.<br>
        This is an automated message. Please do not reply.
      </div>
    </div>
  </div>
</body>
</html>
