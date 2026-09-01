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
        
        /* Dynamic Header Gradient based on status */
        .header-gradient-approved {
            height: 6px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .header-gradient-rejected {
            height: 6px;
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
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
            width: 120px;
        }
        .detail-value {
            color: #0f172a;
            font-weight: 500;
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-rejected {
            background-color: #ffe4e6;
            color: #991b1b;
        }
        
        /* Rejection Feedback Box */
        .feedback-box {
            background-color: #fff1f2;
            border-left: 4px solid #f43f5e;
            border-radius: 4px 12px 12px 4px;
            padding: 20px;
            margin-bottom: 32px;
        }
        .feedback-title {
            font-size: 14px;
            font-weight: 700;
            color: #991b1b;
            margin-top: 0;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .feedback-content {
            font-size: 15px;
            line-height: 1.6;
            color: #7f1d1d;
            margin: 0;
            font-style: italic;
        }
        
        /* CTA Button */
        .btn-container {
            text-align: center;
            margin-bottom: 16px;
        }
        .btn-action-approved {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none !important;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        .btn-action-rejected {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none !important;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
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
                <td class="{{ $status === 'published' ? 'header-gradient-approved' : 'header-gradient-rejected' }}"></td>
            </tr>
            <tr>
                <td class="content-padding">
                    <!-- Brand Identity -->
                    <div class="brand-header">
                        <span class="brand-logo">Q-BLOGS</span>
                       
                    </div>

                    <!-- Email Heading -->
                    <h2 class="email-title">{{ $title }}</h2>

                    <!-- Email Body Salutation -->
                    <div class="email-body">
                        <p>Dear {{ $inputterName }},</p>
                        
                        @if ($status === 'published')
                            <p>We are pleased to inform you that your article has been reviewed, approved, and successfully published to the public feed.</p>
                        @else
                            <p>We regret to inform you that your article submission requires changes and has been rejected by the reviewer. Please see the rejection feedback below and make the necessary updates before re-submitting.</p>
                        @endif
                    </div>

                    <!-- Details Card -->
                    <div class="detail-card">
                        <h3 class="detail-title">Submission Details</h3>
                        <table class="detail-table" cellpadding="0" cellspacing="0" border="0">
                            <tr class="detail-row">
                                <td class="detail-label">Article Title:</td>
                                <td class="detail-value">{{ $article->title }}</td>
                            </tr>
                            <tr class="detail-row">
                                <td class="detail-label">Status:</td>
                                <td class="detail-value">
                                    @if ($status === 'published')
                                        <span class="badge badge-approved">Approved & Published</span>
                                    @else
                                        <span class="badge badge-rejected">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="detail-row">
                                <td class="detail-label">Reviewed By:</td>
                                <td class="detail-value">{{ $authoriserName }}</td>
                            </tr>
                            <tr class="detail-row">
                                <td class="detail-label">Review Date:</td>
                                <td class="detail-value">{{ now()->format('M d, Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Rejection Feedback (If Rejected) -->
                    @if ($status === 'rejected' && !empty($reason))
                        <div class="feedback-box">
                            <h4 class="feedback-title">Rejection Feedback</h4>
                            <p class="feedback-content">"{{ $reason }}"</p>
                        </div>
                    @endif

                    <!-- Call To Action -->
                    <div class="btn-container">
                        @if ($status === 'published')
                            <a href="{{ $actionUrl }}" class="btn-action-approved" target="_blank">
                               View Published Article 
                            </a>
                        @else
                            <a href="{{ $actionUrl }}" class="btn-action-rejected" target="_blank">
                               Edit & Re-submit Article 
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid #f1f5f9; height: 1px; line-height: 1px; font-size: 1px;"></td>
            </tr>
            <tr>
                <td class="footer-padding">
                    <p class="footer-text">
                        This is an automated operational notification from the Q-BLOGS CMS Portal. Please do not reply directly to this email.
                    </p>
                    <p class="footer-text" style="margin-top: 8px;">
                        &copy; {{ date('Y') }} Q-BLOGS. All rights reserved.
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
