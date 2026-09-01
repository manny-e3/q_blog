<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed Successfully</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #f4f6fa 0%, #e2e8f0 100%);
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 480px;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #0d9488 0%, #6366f1 100%);
        }
        .icon-wrapper {
            width: 72px;
            height: 72px;
            background-color: #f0fdf4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
            border: 4px solid #f8fafc;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .icon {
            color: #10b981;
            font-size: 32px;
            font-weight: bold;
        }
        .title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 12px 0;
            letter-spacing: -0.5px;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            color: #64748b;
            margin: 0 0 32px 0;
        }
        .email-display {
            font-weight: 600;
            color: #334155;
            background-color: #f8fafc;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: inline-block;
            margin-top: 8px;
        }
        .btn {
            display: inline-block;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 24px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
        }
        .btn:active {
            transform: translateY(0);
        }
        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="icon-wrapper">
                <span class="icon">&check;</span>
            </div>
            <h1 class="title">Unsubscribed</h1>
            <p class="message">
                You have been successfully unsubscribed from the Q-BLOGS newsletter. We are sorry to see you go!
                <br>
                <span class="email-display">{{ $email }}</span>
                <br><br>
                If this was a mistake, you can always subscribe again anytime on our website.
            </p>
            <a href="https://adgnode.fmdqgroup.com/q-blog" class="btn">Return to Homepage</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Q-BLOGS. All rights reserved.
        </div>
    </div>
</body>
</html>
