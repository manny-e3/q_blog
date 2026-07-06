<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        /* Reset styles for email clients */
        body, table, td, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        table {
            border-collapse: collapse !important;
        }
        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #f4f6fa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        
        /* Premium Styling */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f6fa;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header-gradient {
            height: 6px;
            background: linear-gradient(135deg, #0d9488 0%, #6366f1 100%);
        }
        .content-padding {
            padding: 40px 48px;
        }
        .brand-header {
            margin-bottom: 32px;
            display: flex;
            align-items: center;
        }
        .brand-logo {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #0d9488 0%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: #0d9488;
        }
        .brand-tag {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-left: 8px;
            padding: 2px 6px;
            background-color: #f1f5f9;
            border-radius: 4px;
        }
        .email-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 16px;
            line-height: 1.3;
        }
        .email-body {
            font-size: 16px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 32px;
        }
        .detail-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 32px;
        }
        .detail-item {
            margin-bottom: 12px;
            font-size: 14px;
            line-height: 1.5;
        }
        .detail-item:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #475569;
            display: inline-block;
            width: 120px;
        }
        .detail-value {
            color: #0f172a;
        }
        .btn-container {
            text-align: center;
            margin-bottom: 16px;
        }
        .btn-action {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none !important;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .footer-padding {
            padding: 24px 48px 40px 48px;
            text-align: center;
        }
        .footer-text {
            font-size: 12px;
            line-height: 1.5;
            color: #94a3b8;
        }
        .footer-links {
            margin-top: 16px;
        }
        .footer-link {
            color: #6366f1;
            text-decoration: none;
            font-size: 12px;
            margin: 0 8px;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" align="center" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="header-gradient"></td>
            </tr>
            <tr>
                <td class="content-padding">
                    <!-- Brand Identity -->
                  

                    <!-- Email Heading -->
                    <h2 class="email-title">{{ $title }}</h2>

                    <!-- Email Body Paragraphs -->
                    <div class="email-body">
                        {!! nl2br(e($bodyMessage)) !!}
                    </div>

                    <!-- Call To Action -->
                    <div class="btn-container">
                        <a href="{{ $actionUrl ?? url('/api/v1/docs') }}" class="btn-action" target="_blank">
                           Login 
                        </a>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid #f1f5f9; height: 1px; line-height: 1px; font-size: 1px;"></td>
            </tr>
            <!-- <tr>
                <td class="footer-padding">
                    <p class="footer-text">
                        This is an automated operational notification from the Q-BLOG Admin Portal. Please do not reply directly to this email.
                    </p>
                    <p class="footer-text" style="margin-top: 8px;">
                        &copy; 2026 Q-BLOG. All rights reserved.
                    </p>
                    <div class="footer-links">
                        <a href="{{ url('/api/v1/docs') }}" class="footer-link" target="_blank">API Reference</a>
                        <span style="color: #cbd5e1;">&bull;</span>
                        <a href="{{ url('/api/v1/health') }}" class="footer-link" target="_blank">System Health</a>
                    </div>
                </td>
            </tr> -->
        </table>
    </div>
</body>
</html>
