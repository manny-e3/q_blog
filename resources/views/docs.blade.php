<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q-BLOG API Reference Console</title>
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bg-dark: #090d16;
            --bg-card: rgba(18, 24, 38, 0.7);
            --bg-sidebar: #0b0f19;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --accent-teal: #0d9488;
            --accent-indigo: #6366f1;
            --accent-violet: #8b5cf6;
            --border-color: rgba(255, 255, 255, 0.08);
            --font-display: 'Outfit', sans-serif;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'Fira Code', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: var(--font-sans);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Ambient Glow Gradients */
        .glow-overlay {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            top: -200px;
            right: -100px;
            pointer-events: none;
            z-index: 0;
        }

        .glow-overlay-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.1) 0%, rgba(0, 0, 0, 0) 70%);
            bottom: -100px;
            left: -100px;
            pointer-events: none;
            z-index: 0;
        }

        /* Sidebar Style */
        .sidebar {
            width: 320px;
            background-color: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 10;
            position: relative;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header i {
            font-size: 28px;
            background: linear-gradient(135deg, var(--accent-teal), var(--accent-indigo));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-header h1 {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .sidebar-search {
            padding: 16px 20px;
            position: relative;
        }

        .sidebar-search input {
            width: 100%;
            padding: 10px 16px 10px 38px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: rgba(255, 255, 255, 0.03);
            color: var(--text-main);
            font-family: var(--font-sans);
            font-size: 14px;
            transition: all 0.2s;
        }

        .sidebar-search input:focus {
            outline: none;
            border-color: var(--accent-indigo);
            background-color: rgba(255, 255, 255, 0.05);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .sidebar-search i {
            position: absolute;
            left: 32px;
            top: 27px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 10px 16px;
        }

        .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin: 20px 8px 8px 8px;
            font-weight: 600;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 4px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .menu-item:hover {
            color: var(--text-main);
            background-color: rgba(255, 255, 255, 0.03);
        }

        .menu-item.active {
            color: var(--text-main);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(13, 148, 136, 0.05) 100%);
            border-left: 3px solid var(--accent-indigo);
            font-weight: 500;
        }

        /* Main Workspace Content */
        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            z-index: 5;
        }

        /* Top Bar */
        .top-bar {
            height: 70px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            backdrop-filter: blur(10px);
            background-color: rgba(9, 13, 22, 0.8);
        }

        .top-bar-auth {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .top-bar-auth input {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: rgba(255, 255, 255, 0.03);
            color: var(--text-main);
            font-size: 13px;
            width: 220px;
        }

        .top-bar-auth input:focus {
            outline: none;
            border-color: var(--accent-teal);
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-family: var(--font-sans);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-indigo), var(--accent-violet));
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Main Scrollable Docs & Console Area */
        .workspace {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .docs-view {
            flex: 1;
            overflow-y: auto;
            padding: 40px;
        }

        .module-section {
            display: none;
        }

        .module-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .module-header {
            margin-bottom: 30px;
        }

        .module-header h2 {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .module-header p {
            color: var(--text-muted);
            font-size: 15px;
        }

        /* Endpoint Card */
        .endpoint-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            backdrop-filter: blur(8px);
            transition: border-color 0.2s;
        }

        .endpoint-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
        }

        .endpoint-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .endpoint-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .method-badge {
            font-family: var(--font-mono);
            font-weight: 700;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .method-GET { background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .method-POST { background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); }
        .method-PATCH { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
        .method-DELETE { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

        .path-text {
            font-family: var(--font-mono);
            font-weight: 500;
            font-size: 14px;
            color: #fff;
        }

        .auth-badge {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .auth-badge.required {
            background-color: rgba(99, 102, 241, 0.1);
            color: var(--accent-indigo);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .endpoint-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* Detail Blocks (Request/Response) */
        .tabs-header {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 16px;
        }

        .tab-btn {
            padding: 8px 16px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 13px;
            cursor: pointer;
            position: relative;
            font-weight: 500;
        }

        .tab-btn.active {
            color: #fff;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--accent-indigo);
        }

        .tab-content {
            display: none;
            position: relative;
        }

        .tab-content.active {
            display: block;
        }

        .code-wrapper {
            position: relative;
            background-color: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
            max-height: 300px;
            overflow-y: auto;
        }

        .code-wrapper pre {
            margin: 0;
        }

        .code-wrapper code {
            font-family: var(--font-mono);
            font-size: 12.5px;
            line-height: 1.5;
            color: #a7f3d0;
        }

        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
        }

        .copy-btn:hover {
            color: #fff;
        }

        /* Console Panel */
        .console-panel {
            width: 450px;
            border-left: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            background-color: rgba(9, 13, 22, 0.5);
            z-index: 8;
        }

        .console-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .console-header h3 {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 600;
        }

        .console-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .console-group {
            margin-bottom: 20px;
        }

        .console-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .console-input {
            width: 100%;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 12px;
            color: var(--text-main);
            font-family: var(--font-mono);
            font-size: 13px;
        }

        .console-input:focus {
            outline: none;
            border-color: var(--accent-indigo);
        }

        .console-textarea {
            width: 100%;
            height: 120px;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            color: var(--text-main);
            font-family: var(--font-mono);
            font-size: 13px;
            resize: vertical;
        }

        .console-textarea:focus {
            outline: none;
            border-color: var(--accent-indigo);
        }

        .console-output-area {
            flex: 1;
            border-top: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .console-output-header {
            padding: 12px 24px;
            background-color: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .status-pill {
            font-family: var(--font-mono);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
        }

        .status-success { background-color: rgba(16, 185, 129, 0.15); color: #10b981; }
        .status-error { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; }

        .console-output {
            flex: 1;
            overflow-y: auto;
            padding: 16px 24px;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .console-output pre {
            margin: 0;
        }

        .console-output code {
            font-family: var(--font-mono);
            font-size: 12px;
            color: #d1d5db;
        }
    </style>
</head>
<body>

    <div class="glow-overlay"></div>
    <div class="glow-overlay-2"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-cpu"></i>
            <h1>Q-BLOG API Console</h1>
        </div>

        <div class="sidebar-search">
            <i class="bi bi-search"></i>
            <input type="text" id="search-input" placeholder="Search endpoints..." onkeyup="filterMenu()">
        </div>

        <div class="sidebar-menu" id="sidebar-menu">
            <!-- Dynamic module list injected here -->
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="content">
        <!-- Top Navigation -->
        <div class="top-bar">
            <div>
                <span style="color: var(--text-muted); font-size: 13px;">API Base URL:</span>
                <strong style="font-family: var(--font-mono); margin-left: 8px;">/api/v1</strong>
            </div>

            <!-- Credentials manager -->
            <div class="top-bar-auth">
                <input type="email" id="auth-email" placeholder="Username or Email" value="{{ env('APP_API_USERNAME', 'author@test.com') }}">
                <input type="password" id="auth-password" placeholder="Password" value="{{ env('APP_API_PASSWORD', 'password') }}">
                <button class="btn btn-primary" onclick="alert('Credentials saved locally for console requests!')">
                    <i class="bi bi-key" style="margin-right: 6px;"></i>Save Auth
                </button>
            </div>
        </div>

        <!-- Document Views & Testing Interface -->
        <div class="workspace">
            <div class="docs-view" id="docs-view">
                <!-- Modules rendering dynamically -->
            </div>

            <!-- Testing console -->
            <div class="console-panel">
                <div class="console-header">
                    <h3>Interactive Console</h3>
                    <span style="font-size: 11px; color: var(--text-muted);">Try it out!</span>
                </div>

                <div class="console-body">
                    <div class="console-group">
                        <label>Endpoint</label>
                        <div style="display: flex; gap: 8px;">
                            <span id="console-method" class="method-badge method-GET" style="display: flex; align-items: center; justify-content: center; width: 60px;">GET</span>
                            <input type="text" id="console-url" class="console-input" value="/api/v1/health" readonly>
                        </div>
                    </div>

                    <div class="console-group" id="console-body-group" style="display: none;">
                        <label>Request Body (JSON)</label>
                        <textarea id="console-payload" class="console-textarea"></textarea>
                    </div>

                    <div class="console-group" id="console-query-group" style="display: none;">
                        <label>Query Params (e.g. key=val&k2=v2)</label>
                        <input type="text" id="console-query" class="console-input" placeholder="category=market-review">
                    </div>

                    <button class="btn btn-primary" style="width: 100%; margin-top: 10px;" onclick="sendRequest()">
                        <i class="bi bi-play-fill" style="margin-right: 6px;"></i>Execute API call
                    </button>
                </div>

                <!-- API Response Output -->
                <div class="console-output-area">
                    <div class="console-output-header">
                        <span>Response</span>
                        <span id="response-status-pill" class="status-pill" style="display: none;">200 OK</span>
                    </div>
                    <div class="console-output">
                        <pre><code id="console-response">Execute an endpoint to see response output.</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Data & Interactive JS Logic -->
    <script>
        const apiData = [
          
            {
                name: "Public Blog Articles",
                desc: "Public-facing queries for searching, browsing, and sharing published articles.",
                endpoints: [
                    {
                        method: "GET",
                        path: "/articles/featured",
                        desc: "Fetch featured post and latest articles.",
                        auth: false,
                        body: {},
                        response: { featured: { id: 1, title: "Market Bond Yields", slug: "market-bond-yields", is_featured: true }, latest: [] }
                    },
                    {
                        method: "GET",
                        path: "/articles",
                        desc: "Get filterable and paginated list of articles.",
                        auth: false,
                        query: "sort=latest&limit=10",
                        body: {},
                        response: { current_page: 1, data: [] }
                    },
                    {
                        method: "GET",
                        path: "/articles/search",
                        desc: "Performs full-text queries on published articles.",
                        auth: false,
                        query: "q=Yield",
                        body: {},
                        response: []
                    },
                    {
                        method: "POST",
                        path: "/articles/{id}/view",
                        desc: "Increments article view tracking counter.",
                        auth: false,
                        body: {},
                        response: { message: "View tracked successfully.", views_count: 121 }
                    },
                    {
                        method: "POST",
                        path: "/articles/{id}/share",
                        desc: "Logs and increments social sharing statistics.",
                        auth: false,
                        body: { platform: "LinkedIn" },
                        response: { message: "Share tracked successfully on LinkedIn", shares_count: 34 }
                    }
                ]
            },
            {
                name: "CMS Article Management",
                desc: "Create, save drafts, preview markdown rendering, and submit drafts for publishing approval.",
                endpoints: [
                    {
                        method: "POST",
                        path: "/cms/articles",
                        desc: "Create a new article. If an authoriser_id is specified, the review notification will be sent only to that authoriser.",
                        auth: true,
                        body: { title: "Nigeria Market Cap", content: "### Content info", category_id: 1, tags: [1], is_featured: false, inputter_id: 2, authoriser_id: 1 },
                        response: { id: 10, title: "Nigeria Market Cap", slug: "nigeria-market-cap", status: "pending", authoriser_id: 1 }
                    },
                    {
                        method: "POST",
                        path: "/cms/articles/preview",
                        desc: "Live converts markdown payload to HTML format.",
                        auth: true,
                        body: { content: "# Headline\n**Bold Text**" },
                        response: { html: "<h1>Headline</h1><br><strong>Bold Text</strong>" }
                    },
                    {
                        method: "POST",
                        path: "/cms/articles/{id}/publish",
                        desc: "Submits draft for Authoriser approval, or publishes directly if auth user is an Authoriser.",
                        auth: true,
                        body: {},
                        response: { message: "Article submitted for approval.", article: { id: 10, status: "pending" } }
                    },
                    {
                        method: "GET",
                        path: "/cms/my-articles/pending",
                        desc: "Retrieves list of the authenticated user's own articles that are pending approval.",
                        auth: true,
                        body: {},
                        response: [
                            {
                                id: 10,
                                title: "Nigeria Market Cap",
                                slug: "nigeria-market-cap",
                                content: "### Content info",
                                summary: "Market capitalization details",
                                status: "pending",
                                reject_reason: null,
                                is_featured: false,
                                inputter_id: 2,
                                authoriser_id: null,
                                category_id: 1,
                                views_count: 0,
                                shares_count: 0,
                                created_at: "2026-06-16T12:00:00.000000Z",
                                updated_at: "2026-06-16T12:05:00.000000Z",
                                category: {
                                    id: 1,
                                    name: "Market Review",
                                    slug: "market-review",
                                    status: "active"
                                },
                                tags: [
                                    {
                                        id: 1,
                                        name: "Finance",
                                        slug: "finance"
                                    }
                                ]
                            }
                        ]
                    }
                ]
            },
            {
                name: "Approval Workflows",
                desc: "Administrative verification, publishing approvals, rejection workflows, and audit history logs.",
                endpoints: [
                    {
                        method: "GET",
                        path: "/cms/admin/articles/pending",
                        desc: "Retrieves list of all articles currently pending moderator approval.",
                        auth: true,
                        body: {},
                        response: [
                            {
                                id: 10,
                                title: "Nigeria Market Cap",
                                slug: "nigeria-market-cap",
                                content: "### Content info",
                                summary: "Market capitalization details",
                                status: "pending",
                                reject_reason: null,
                                is_featured: false,
                                inputter_id: 2,
                                authoriser_id: null,
                                category_id: 1,
                                views_count: 0,
                                shares_count: 0,
                                created_at: "2026-06-16T12:00:00.000000Z",
                                updated_at: "2026-06-16T12:05:00.000000Z",
                                category: {
                                    id: 1,
                                    name: "Market Review",
                                    slug: "market-review",
                                    status: "active"
                                },
                                tags: [
                                    {
                                        id: 1,
                                        name: "Finance",
                                        slug: "finance"
                                    }
                                ]
                            }
                        ]
                    },
                    {
                        method: "GET",
                        path: "/approvals/pending/count",
                        desc: "Retrieves count of articles requiring moderation approval.",
                        auth: true,
                        body: {},
                        response: { pending_count: 5 }
                    },
                    {
                        method: "POST",
                        path: "/approvals/{id}/approve",
                        desc: "Approves a pending article and marks it published.",
                        auth: true,
                        body: {},
                        response: { message: "Article approved and published successfully." }
                    },
                    {
                        method: "POST",
                        path: "/approvals/{id}/reject",
                        desc: "Rejects a pending article submission stating the reason.",
                        auth: true,
                        body: { reason: "Needs more data references." },
                        response: { message: "Article rejected successfully." }
                    }
                ]
            },
            {
                name: "Analytics & Reporting",
                desc: "Dashboard traffic sources, top posts, and download exports.",
                endpoints: [
                    {
                        method: "GET",
                        path: "/analytics/dashboard",
                        desc: "Get total overview stats.",
                        auth: true,
                        body: {},
                        response: { total_articles: 15, published_articles: 10, pending_approvals: 2, total_views: 450, total_shares: 98 }
                    },
                    {
                        method: "GET",
                        path: "/analytics/traffic-sources",
                        desc: "Breakdown of traffic views distribution.",
                        auth: true,
                        body: {},
                        response: { direct: 180, social: 120, referral: 60 }
                    },
                    {
                        method: "GET",
                        path: "/analytics/export/csv",
                        desc: "Exports platform analytics to CSV file.",
                        auth: true,
                        body: {},
                        response: "Download: q_blog_analytics.csv"
                    }
                ]
            },
            {
                name: "System Utilities & Health",
                desc: "Exposes slug generation, reading time utilities, and health status indicators.",
                endpoints: [
                    {
                        method: "GET",
                        path: "/health",
                        desc: "Verifies application database status and timestamp.",
                        auth: false,
                        body: {},
                        response: { status: "UP", database: "connected", timestamp: "2026-06-02T09:25:00+01:00" }
                    },
                    {
                        method: "POST",
                        path: "/system/generate-slug",
                        desc: "Transforms standard title strings to SEO slug strings.",
                        auth: false,
                        body: { title: "Nigeria bond market review 2026" },
                        response: { slug: "nigeria-bond-market-review-2026" }
                    }
                ]
            }
        ];

        let selectedEndpoint = null;

        // Render sidebar menu
        function renderSidebar() {
            const menu = document.getElementById('sidebar-menu');
            let menuHtml = '';

            apiData.forEach((mod, index) => {
                menuHtml += `<div class="menu-title">${mod.name}</div>`;
                mod.endpoints.forEach((ep) => {
                    menuHtml += `
                        <div class="menu-item" onclick="selectEndpoint('${ep.method}', '${ep.path}', ${index})" data-path="${ep.path}" data-method="${ep.method}">
                            <span class="method-badge method-${ep.method}" style="font-size: 9px; padding: 2px 4px; width: 45px; text-align: center;">${ep.method}</span>
                            <span style="font-family: var(--font-mono); font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${ep.path}</span>
                        </div>
                    `;
                });
            });

            menu.innerHTML = menuHtml;
        }

        // Render document cards
        function renderDocs() {
            const view = document.getElementById('docs-view');
            let docsHtml = '';

            apiData.forEach((mod, modIdx) => {
                docsHtml += `
                    <div id="module-${modIdx}" class="module-section ${modIdx === 0 ? 'active' : ''}">
                        <div class="module-header">
                            <h2>${mod.name} Module</h2>
                            <p>${mod.desc}</p>
                        </div>
                `;

                mod.endpoints.forEach((ep, epIdx) => {
                    const authBadge = ep.auth ? '<span class="auth-badge required"><i class="bi bi-shield-lock-fill"></i> Basic Auth Required</span>' : '<span class="auth-badge"><i class="bi bi-unlock-fill"></i> Public</span>';
                    const hasBody = ep.method !== 'GET' && ep.method !== 'DELETE';
                    const querySection = ep.query ? `
                        <div style="margin-top: 10px; font-size: 12px; color: var(--text-muted);">
                            <strong>Query Template:</strong> <code>?${ep.query}</code>
                        </div>
                    ` : '';

                    docsHtml += `
                        <div class="endpoint-card">
                            <div class="endpoint-summary">
                                <div class="endpoint-meta">
                                    <span class="method-badge method-${ep.method}">${ep.method}</span>
                                    <span class="path-text">/api/v1${ep.path}</span>
                                </div>
                                ${authBadge}
                            </div>
                            <div class="endpoint-desc">${ep.desc}</div>
                            ${querySection}

                            <div class="tabs-header">
                                ${hasBody ? `<button class="tab-btn active" onclick="switchTab(event, 'req-body-${modIdx}-${epIdx}')">Request JSON</button>` : ''}
                                <button class="tab-btn ${!hasBody ? 'active' : ''}" onclick="switchTab(event, 'res-body-${modIdx}-${epIdx}')">Example Response</button>
                                <button class="btn btn-primary" style="margin-left: auto; padding: 4px 12px; font-size: 11px; border-radius: 4px;" onclick="loadConsole('${ep.method}', '${ep.path}', ${modIdx}, ${epIdx})">
                                    <i class="bi bi-terminal" style="margin-right: 4px;"></i>Load in Console
                                </button>
                            </div>

                            ${hasBody ? `
                            <div id="req-body-${modIdx}-${epIdx}" class="tab-content active">
                                <div class="code-wrapper">
                                    <button class="copy-btn" onclick="copyToClipboard('${modIdx}-${epIdx}-req')"><i class="bi bi-clipboard"></i></button>
                                    <pre><code id="code-req-${modIdx}-${epIdx}">${JSON.stringify(ep.body, null, 2)}</code></pre>
                                </div>
                            </div>
                            ` : ''}

                            <div id="res-body-${modIdx}-${epIdx}" class="tab-content ${!hasBody ? 'active' : ''}">
                                <div class="code-wrapper">
                                    <button class="copy-btn" onclick="copyToClipboard('${modIdx}-${epIdx}-res')"><i class="bi bi-clipboard"></i></button>
                                    <pre><code id="code-res-${modIdx}-${epIdx}">${JSON.stringify(ep.response, null, 2)}</code></pre>
                                </div>
                            </div>
                        </div>
                    `;
                });

                docsHtml += `</div>`;
            });

            view.innerHTML = docsHtml;
        }

        // Toggle UI tab
        function switchTab(event, tabId) {
            const container = event.target.closest('.endpoint-card');
            container.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            container.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            event.target.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        // Select endpoint & switch section view
        function selectEndpoint(method, path, modIdx) {
            // Highlight menu item
            document.querySelectorAll('.menu-item').forEach(item => {
                const epPath = item.getAttribute('data-path');
                const epMethod = item.getAttribute('data-method');
                if (epPath === path && epMethod === method) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            // Show current section module
            document.querySelectorAll('.module-section').forEach(sec => sec.classList.remove('active'));
            document.getElementById(`module-${modIdx}`).classList.add('active');
        }

        // Copy JSON payload
        function copyToClipboard(id) {
            let el;
            if (id.endsWith('-req')) {
                el = document.querySelector(`#code-req-${id.replace('-req', '')}`);
            } else {
                el = document.querySelector(`#code-res-${id.replace('-res', '')}`);
            }
            
            navigator.clipboard.writeText(el.innerText);
            alert("Copied code to clipboard!");
        }

        // Load parameter configuration to interactive console
        function loadConsole(method, path, modIdx, epIdx) {
            const ep = apiData[modIdx].endpoints[epIdx];
            selectedEndpoint = ep;

            const consoleMethod = document.getElementById('console-method');
            consoleMethod.innerText = method;
            consoleMethod.className = `method-badge method-${method}`;

            document.getElementById('console-url').value = `/api/v1${path}`;

            const bodyGroup = document.getElementById('console-body-group');
            if (method !== 'GET' && method !== 'DELETE') {
                bodyGroup.style.display = 'block';
                document.getElementById('console-payload').value = JSON.stringify(ep.body, null, 2);
            } else {
                bodyGroup.style.display = 'none';
            }

            const queryGroup = document.getElementById('console-query-group');
            if (ep.query) {
                queryGroup.style.display = 'block';
                document.getElementById('console-query').value = ep.query;
            } else {
                queryGroup.style.display = 'none';
                document.getElementById('console-query').value = '';
            }

            // Highlight in menu
            selectEndpoint(method, path, modIdx);
        }

        // Send API requests directly over AJAX
        async function sendRequest() {
            const path = document.getElementById('console-url').value;
            const method = document.getElementById('console-method').innerText;
            const query = document.getElementById('console-query').value;
            const bodyContent = document.getElementById('console-payload').value;

            const responseCode = document.getElementById('console-response');
            const statusPill = document.getElementById('response-status-pill');

            responseCode.innerText = "Loading response...";
            statusPill.style.display = 'none';

            // Replace endpoint path placeholders if present, e.g. {id} or {slug}
            let finalPath = path;
            if (finalPath.includes('{id}')) {
                const idInput = prompt("Enter value for {id}:", "1");
                if (idInput === null) return;
                finalPath = finalPath.replace('{id}', idInput);
            }
            if (finalPath.includes('{slug}')) {
                const slugInput = prompt("Enter value for {slug}:", "market-review");
                if (slugInput === null) return;
                finalPath = finalPath.replace('{slug}', slugInput);
            }
            if (finalPath.includes('{articleId}')) {
                const artInput = prompt("Enter value for {articleId}:", "1");
                if (artInput === null) return;
                finalPath = finalPath.replace('{articleId}', artInput);
            }

            // Inject dynamic base URL from Laravel
            const baseUrl = "{{ url('/') }}";
            let url = baseUrl + finalPath;
            if (query) {
                url += `?${query}`;
            }

            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };

            // Inject Custom HTTP Basic Auth if present
            const email = document.getElementById('auth-email').value;
            const password = document.getElementById('auth-password').value;
            if (email && password) {
                headers['Authorization'] = 'Basic ' + btoa(`${email}:${password}`);
            }

            const options = {
                method: method,
                headers: headers
            };

            if (method !== 'GET' && method !== 'DELETE' && bodyContent) {
                options.body = bodyContent;
            }

            try {
                const response = await fetch(url, options);
                const statusText = `${response.status} ${response.statusText}`;
                
                statusPill.innerText = statusText;
                statusPill.style.display = 'inline-block';
                statusPill.className = `status-pill ${response.ok ? 'status-success' : 'status-error'}`;

                // Handle binary PDF output
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/pdf')) {
                    responseCode.innerText = "[Binary PDF stream output received successfully]";
                    return;
                }

                const json = await response.json();
                responseCode.innerText = JSON.stringify(json, null, 2);
            } catch (err) {
                statusPill.innerText = "Error";
                statusPill.style.display = 'inline-block';
                statusPill.className = 'status-pill status-error';
                responseCode.innerText = `Network or request error:\n${err.message}`;
            }
        }

        // Simple search menu filter
        function filterMenu() {
            const val = document.getElementById('search-input').value.toLowerCase();
            document.querySelectorAll('.menu-item').forEach(item => {
                const txt = item.innerText.toLowerCase();
                if (txt.includes(val)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Initialize Page
        renderSidebar();
        renderDocs();
        loadConsole('GET', '/health', 4, 0); // Default to health endpoint
    </script>
</body>
</html>
