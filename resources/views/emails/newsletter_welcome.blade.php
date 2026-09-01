<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Q-BLOGS Newsletter</title>
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
            display: table;
            width: 100%;
        }
        .brand-logo {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0f172a;
        }
        .brand-logo-accent {
            color: #6366f1;
        }
        .brand-tag {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 2px 8px;
            background-color: #f1f5f9;
            border-radius: 6px;
            display: inline-block;
            margin-left: 8px;
            vertical-align: middle;
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
        
        /* Details Card */
        .detail-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .detail-title {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0;
            margin-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .detail-table {
            width: 100%;
        }
        .detail-row td {
            padding: 8px 0;
            font-size: 14px;
            line-height: 1.5;
            vertical-align: top;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
            width: 150px;
        }
        .detail-value {
            color: #0f172a;
            font-weight: 500;
        }
        
        /* CTA Button */
        .btn-container {
            text-align: center;
            margin-bottom: 16px;
        }
        .btn-action {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none !important;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
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
                    <div class="brand-header">
                       <span class="brand-logo">Q-BLOGS</span>
                        <span class="brand-tag">Newsletter</span>
                    </div>

                    <!-- Email Heading -->
                    <h2 class="email-title">Welcome to the Q-BLOGS Newsletter!</h2>

                    <!-- Email Body Salutation -->
                    <div class="email-body">
                        <p>Hello {{ $subscription->first_name }},</p>
                        <p>Thank you for subscribing to our newsletter! You've successfully joined our community. From now on, you'll receive the latest market insights, thought leadership articles, policy & regulatory updates, and trend analyses directly in your inbox.</p>
                        <!-- <p>Here are the subscription preferences you selected:</p> -->
                    </div>

                    <!-- Details Card -->
                    <!-- <div class="detail-card">
                        <h3 class="detail-title">Subscription Details</h3>
                        <table class="detail-table" cellpadding="0" cellspacing="0" border="0">
                            <tr class="detail-row">
                                <td class="detail-label">Name:</td>
                                <td class="detail-value">{{ $subscription->first_name }} {{ $subscription->last_name }}</td>
                            </tr>
                            <tr class="detail-row">
                                <td class="detail-label">Email:</td>
                                <td class="detail-value">{{ $subscription->email }}</td>
                            </tr>
                            @if(!empty($subscription->organisation))
                            <tr class="detail-row">
                                <td class="detail-label">Organisation:</td>
                                <td class="detail-value">{{ $subscription->organisation }}</td>
                            </tr>
                            @endif
                            @if(!empty($subscription->role))
                            <tr class="detail-row">
                                <td class="detail-label">Role:</td>
                                <td class="detail-value">{{ $subscription->role }}</td>
                            </tr>
                            @endif
                            @if(!empty($subscription->topics))
                            <tr class="detail-row">
                                <td class="detail-label">Selected Topics:</td>
                                <td class="detail-value">
                                    {{ is_array($subscription->topics) ? implode(', ', $subscription->topics) : $subscription->topics }}
                                </td>
                            </tr>
                            @endif
                            <tr class="detail-row">
                                <td class="detail-label">Frequency:</td>
                                <td class="detail-value">{{ $subscription->frequency }}</td>
                            </tr>
                        </table>
                    </div> -->

                    <!-- Call To Action -->
                    <div class="btn-container">
                        <a href="https://adgnode.fmdqgroup.com/q-blog" class="btn-action" target="_blank">
                           Explore Q-BLOGS
                        </a>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid #f1f5f9; height: 1px; line-height: 1px; font-size: 1px;"></td>
            </tr>
            <tr>
                <td class="footer-padding">
                    <p class="footer-text">
                        You are receiving this email because you subscribed to the Q-BLOGS newsletter.
                    </p>
                    <p class="footer-text" style="margin-top: 8px;">
                        &copy; {{ date('Y') }} Q-BLOGS. All rights reserved.
                    </p>
                    <div class="footer-links">
                        <a href="{{ $unsubscribeUrl }}" class="footer-link">Unsubscribe</a>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
