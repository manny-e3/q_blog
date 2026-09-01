<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q-BLOG Article Tester Console</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Quill Rich Text Editor CDN -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    <style>
        :root {
            --bg-dark: #070a13;
            --bg-card: rgba(18, 24, 38, 0.65);
            --bg-input: rgba(10, 14, 23, 0.8);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --accent-teal: #0d9488;
            --accent-indigo: #6366f1;
            --accent-violet: #8b5cf6;
            --accent-rose: #f43f5e;
            --accent-amber: #f59e0b;
            --border-color: rgba(255, 255, 255, 0.08);
            --font-display: 'Outfit', sans-serif;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'Fira Code', monospace;
            --radius-lg: 16px;
            --radius-md: 10px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Quill Editor Dark Mode Override */
        .ql-toolbar.ql-snow {
            border-color: var(--border-color) !important;
            background: rgba(255, 255, 255, 0.03);
            border-top-left-radius: var(--radius-md);
            border-top-right-radius: var(--radius-md);
        }
        .ql-container.ql-snow {
            border-color: var(--border-color) !important;
            border-bottom-left-radius: var(--radius-md);
            border-bottom-right-radius: var(--radius-md);
            font-family: var(--font-sans);
            font-size: 14px;
        }
        .ql-editor {
            min-height: 180px;
            color: var(--text-main);
            background-color: var(--bg-input);
        }
        .ql-editor.ql-blank::before {
            color: var(--text-muted) !important;
            font-style: normal;
        }
        .ql-snow .ql-stroke {
            stroke: var(--text-muted) !important;
        }
        .ql-snow .ql-fill {
            fill: var(--text-muted) !important;
        }
        .ql-snow .ql-picker {
            color: var(--text-muted) !important;
        }
        .ql-snow .ql-picker-options {
            background-color: var(--bg-sidebar) !important;
            border-color: var(--border-color) !important;
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
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glow backgrounds */
        .ambient-glow-1 {
            position: absolute;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, rgba(0, 0, 0, 0) 70%);
            top: -200px;
            right: -100px;
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.08) 0%, rgba(0, 0, 0, 0) 70%);
            bottom: -150px;
            left: -150px;
            pointer-events: none;
            z-index: 0;
        }

        header {
            background-color: rgba(11, 15, 25, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            position: sticky;
            top: 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo i {
            font-size: 28px;
            background: linear-gradient(135deg, var(--accent-teal), var(--accent-indigo));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo h1 {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .main-container {
            display: grid;
            grid-template-columns: 480px 1fr;
            gap: 30px;
            padding: 30px 40px;
            flex-grow: 1;
            z-index: 5;
            position: relative;
        }

        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
            }
        }

        /* Glassmorphism Card Panels */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: fit-content;
        }

        .panel-title {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title i {
            color: var(--accent-teal);
        }

        /* Forms & Inputs */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .input-control {
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            font-size: 14px;
            font-family: var(--font-sans);
            transition: var(--transition);
            width: 100%;
        }

        .input-control:focus {
            outline: none;
            border-color: var(--accent-teal);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }

        textarea.input-control {
            resize: vertical;
            min-height: 120px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-group input {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: var(--accent-teal);
        }

        /* Buttons & Actions */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: var(--radius-md);
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-teal), var(--accent-indigo));
            color: #fff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }

        /* Upload Area */
        .upload-zone {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.01);
            position: relative;
        }

        .upload-zone:hover {
            border-color: var(--accent-teal);
            background: rgba(13, 148, 136, 0.02);
        }

        .upload-zone i {
            font-size: 32px;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: block;
        }

        .upload-zone span {
            font-size: 12px;
            color: var(--text-muted);
        }

        .upload-preview {
            margin-top: 10px;
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
            display: none;
            aspect-ratio: 16/9;
        }

        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-preview .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: var(--transition);
        }

        .upload-preview .remove-btn:hover {
            background: var(--accent-rose);
        }

        /* Workspace & Articles Grid */
        .workspace-panel {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .controls-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .filter-tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 4px;
            gap: 4px;
        }

        .filter-tab {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .filter-tab.active {
            background: var(--accent-teal);
            color: #fff;
        }

        .search-box {
            display: flex;
            align-items: center;
            position: relative;
            min-width: 240px;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .search-box input {
            padding-left: 40px;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        /* Article Card */
        .article-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            height: 100%;
        }

        .article-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.4);
        }

        .card-image {
            aspect-ratio: 16/9;
            background-color: rgba(255, 255, 255, 0.02);
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--border-color);
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-image .fallback {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 12px;
            gap: 8px;
        }

        .card-image .fallback i {
            font-size: 24px;
        }

        .card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 20px;
            backdrop-filter: blur(8px);
        }

        .badge-draft { background: rgba(156, 163, 175, 0.2); color: #e5e7eb; border: 1px solid rgba(156, 163, 175, 0.4); }
        .badge-pending { background: rgba(245, 158, 11, 0.2); color: #fef3c7; border: 1px solid rgba(245, 158, 11, 0.4); }
        .badge-published { background: rgba(13, 148, 136, 0.2); color: #ccfbf1; border: 1px solid rgba(13, 148, 136, 0.4); }
        .badge-rejected { background: rgba(244, 63, 94, 0.2); color: #ffe4e6; border: 1px solid rgba(244, 63, 94, 0.4); }

        .card-content {
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-grow: 1;
        }

        .card-category {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--accent-teal);
            letter-spacing: 0.5px;
        }

        .card-title {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 600;
            line-height: 1.4;
            color: var(--text-main);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 44px;
        }

        .card-summary {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 38px;
        }

        .card-footer {
            border-top: 1px solid var(--border-color);
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--text-muted);
        }

        .card-stats {
            display: flex;
            gap: 12px;
        }

        .card-stats span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Tags container */
        .tags-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: auto;
            padding-top: 10px;
        }

        .tag-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 10px;
            color: var(--text-muted);
        }

        /* Notification Banner */
        .toast-container {
            position: fixed;
            bottom: 30px;
            right: 40px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 100;
        }

        .toast {
            background: rgba(18, 24, 38, 0.9);
            backdrop-filter: blur(10px);
            border-left: 4px solid var(--accent-teal);
            border-radius: 8px;
            padding: 16px 20px;
            color: #fff;
            font-size: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s forwards;
            min-width: 300px;
            max-width: 400px;
        }

        .toast.toast-error {
            border-left-color: var(--accent-rose);
        }

        .toast.toast-warning {
            border-left-color: var(--accent-amber);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Modal styling */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(5, 7, 13, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            border-radius: var(--radius-lg);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }

        .modal.active .modal-container {
            transform: scale(1);
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: rgba(18, 24, 38, 0.95);
            backdrop-filter: blur(10px);
            z-index: 10;
        }

        .modal-header h2 {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 600;
        }

        .close-modal-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 24px;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal-btn:hover {
            color: #fff;
        }

        .modal-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .modal-image {
            width: 100%;
            border-radius: var(--radius-md);
            overflow: hidden;
            aspect-ratio: 16/9;
            background-color: rgba(255, 255, 255, 0.01);
            border: 1px solid var(--border-color);
        }

        .modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 13px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
        }

        .modal-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .modal-content-text {
            font-size: 15px;
            line-height: 1.6;
            color: #e5e7eb;
        }

        .modal-content-text p {
            margin-bottom: 14px;
        }

        .modal-content-text img {
            max-width: 100%;
            height: auto;
            border-radius: var(--radius-md);
            margin: 12px 0;
            display: block;
        }

        .modal-content-text iframe {
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: var(--radius-md);
            margin: 12px 0;
            border: none;
            display: block;
        }

        /* Loading Spinner */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
            display: inline-block;
        }

        .full-page-loading {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px;
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .full-page-loading .spinner {
            width: 32px;
            height: 32px;
            border-width: 3px;
            border-top-color: var(--accent-teal);
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Multi-select styling */
        .tags-selector-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 8px;
            max-height: 100px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 10px;
            background: var(--bg-input);
        }
    </style>
</head>
<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <header>
        <div class="logo">
            <i class="bi bi-rocket-takeoff-fill"></i>
            <h1>Q-BLOG</h1>
        </div>
    </header>

    <main class="main-container">
        <!-- Sidebar: Creation Form -->
        <aside class="glass-card">
            <div class="panel-title">
                <i class="bi bi-file-earmark-plus-fill"></i>
                <h2>Create New Article</h2>
            </div>

            <form id="article-form" onsubmit="submitArticle(event)" style="display: flex; flex-direction: column; gap: 16px;">
                <div class="form-group">
                    <label for="title">Article Title</label>
                    <input type="text" id="title" class="input-control" placeholder="e.g. Market Yields Analysis 2026" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Categories (Hold Ctrl/Cmd to select multiple)</label>
                    <select id="category_id" class="input-control" multiple required>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Publication Status</label>
                    <select id="status" class="input-control">
                        <option value="draft">Draft</option>
                        <option value="pending" selected>Pending Review</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="inputter_id">Inputter (Creator)</label>
                    <select id="inputter_id" class="input-control" required>
                        @foreach($inputters as $inputter)
                            <option value="{{ $inputter['id'] }}">{{ trim(($inputter['firstname'] ?? '') . ' ' . ($inputter['lastname'] ?? '')) ?: ($inputter['name'] ?? 'User ID ' . $inputter['id']) }} ({{ $inputter['email'] ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="authoriser_id">Authoriser (Reviewer)</label>
                    <select id="authoriser_id" class="input-control">
                        <option value="">Select Authoriser</option>
                        @foreach($authorisers as $authoriser)
                            <option value="{{ $authoriser['id'] }}">{{ trim(($authoriser['firstname'] ?? '') . ' ' . ($authoriser['lastname'] ?? '')) ?: ($authoriser['name'] ?? 'User ID ' . $authoriser['id']) }} ({{ $authoriser['email'] ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Tags</label>
                    <div id="tags-container" class="tags-selector-grid">
                        <span style="color: var(--text-muted); font-size: 12px; grid-column: 1/-1;">Loading tags...</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="summary">Short Summary</label>
                    <input type="text" id="summary" class="input-control" placeholder="Brief outline of the content">
                </div>

                <div class="form-group">
                    <label>Article Content</label>
                    <div id="editor"></div>
                </div>

                <!-- Featured Image Upload Options -->
                <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 12px;">
                    <label>Featured Image Source</label>
                    
                    <div style="display: flex; gap: 14px; margin-top: 6px;">
                        <label class="checkbox-group" style="text-transform: none; font-size: 12px; color: var(--text-main);">
                            <input type="radio" name="image-source" value="file" checked onclick="toggleImageSource('file')">
                            Upload
                        </label>
                        <label class="checkbox-group" style="text-transform: none; font-size: 12px; color: var(--text-main);">
                            <input type="radio" name="image-source" value="base64" onclick="toggleImageSource('base64')">
                            Base64
                        </label>
                        <label class="checkbox-group" style="text-transform: none; font-size: 12px; color: var(--text-main);">
                            <input type="radio" name="image-source" value="url" onclick="toggleImageSource('url')">
                            URL
                        </label>
                    </div>

                    <!-- Local Upload container -->
                    <div id="image-source-file" style="margin-top: 8px;">
                        <div class="upload-zone" onclick="document.getElementById('image-file-input').click()">
                            <i class="bi bi-image" style="font-size: 24px; margin-bottom: 6px;"></i>
                            <span style="font-size: 11px;">Click to upload image file</span>
                            <input type="file" id="image-file-input" style="display: none;" accept="image/*" onchange="handleFileSelect(event)">
                        </div>
                    </div>

                    <!-- Base64 container -->
                    <div id="image-source-base64" style="margin-top: 8px; display: none;">
                        <textarea id="image-base64-input" class="input-control" style="min-height: 70px; font-size: 12px;" placeholder="Paste base64 data URL"></textarea>
                    </div>

                    <!-- URL container -->
                    <div id="image-source-url" style="margin-top: 8px; display: none;">
                        <input type="text" id="image-url-input" class="input-control" placeholder="https://example.com/image.jpg">
                    </div>

                    <!-- Preview Thumbnail -->
                    <div id="preview-thumbnail-container" class="upload-preview">
                        <img id="preview-thumbnail-img" src="" alt="Thumbnail Preview">
                        <button type="button" class="remove-btn" onclick="clearFeaturedImage()"><i class="bi bi-x"></i></button>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px; border-top: 1px solid var(--border-color); padding-top: 14px;">
                    <label class="checkbox-group" style="text-transform: none; font-size: 13px; color: var(--text-main);">
                        <input type="checkbox" id="is_featured">
                        Pin as Featured Article
                    </label>
                    <button type="submit" id="submit-btn" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-send-fill"></i> Submit Article
                    </button>
                </div>
            </form>
        </aside>

        <!-- Right Side: Articles List section -->
        <section class="workspace-panel">
            <div class="controls-row">
                <div class="filter-tabs">
                    <div class="filter-tab active" id="tab-all" onclick="setFilter('all')">All</div>
                    <div class="filter-tab" id="tab-published" onclick="setFilter('published')">Published</div>
                    <div class="filter-tab" id="tab-draft" onclick="setFilter('draft')">Draft</div>
                    <div class="filter-tab" id="tab-pending" onclick="setFilter('pending')">Pending</div>
                    <div class="filter-tab" id="tab-rejected" onclick="setFilter('rejected')">Rejected</div>
                </div>

                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="search-input" class="input-control" placeholder="Search directory..." oninput="handleSearch(event)">
                </div>
            </div>

            <div id="articles-directory" class="articles-grid">
                <!-- Populated dynamically -->
            </div>
        </section>
    </main>

    <!-- Modal Reader -->
    <div id="reader-modal" class="modal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modal-title">Article Title</h2>
                <div style="display: flex; align-items: center; gap: 16px;">
                    <a id="modal-view-page-btn" href="#" target="_blank" class="btn btn-primary btn-small" style="text-decoration: none; padding: 6px 12px; font-size: 12px; height: fit-content; background: linear-gradient(135deg, var(--accent-teal), var(--accent-indigo)); border: none; border-radius: 6px; color: white;">
                        <i class="bi bi-box-arrow-up-right"></i> Open Page
                    </a>
                    <button class="close-modal-btn" onclick="closeModal()">&times;</button>
                </div>
            </div>
            <div class="modal-body">
                <div id="modal-image-container" class="modal-image">
                    <img id="modal-img" src="" alt="Featured Image">
                </div>
                <div class="modal-meta">
                    <span id="modal-author"><i class="bi bi-person-fill"></i> Author</span>
                    <span id="modal-category"><i class="bi bi-tag-fill"></i> Category</span>
                    <span id="modal-date"><i class="bi bi-calendar-event-fill"></i> Date</span>
                    <span id="modal-views"><i class="bi bi-eye-fill"></i> 0 Views</span>
                    <span id="modal-shares"><i class="bi bi-share-fill"></i> 0 Shares</span>
                    <span id="modal-status-badge"></span>
                </div>
                <div id="modal-content" class="modal-content-text">
                    <!-- HTML article content -->
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        // Server Environment Credentials & Base URL
        const authUsername = "{{ env('APP_API_USERNAME', 'sml_system_integrator') }}";
        const authPassword = "{{ env('APP_API_PASSWORD', 'Z@p7-Wx2!_mKq9_Rst5') }}";
        const apiBase = "{{ url('/api/v1') }}";
        const siteBase = "{{ url('/') }}";

        // Global State
        let categories = [];
        let tags = [];
        let articles = [];
        let activeFilter = 'all';
        let searchQuery = '';
        let uploadedImageFile = null;
        let quill;

        // Init on load
        window.addEventListener('DOMContentLoaded', () => {
            quill = new Quill('#editor', {
                theme: 'snow',
                placeholder: 'Write your article details here...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ]
                }
            });
            fetchCategories();
            fetchTags();
            refreshArticles();
        });

        // Toast notifications helper
        function showNotification(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type === 'error' ? 'toast-error' : type === 'warning' ? 'toast-warning' : ''}`;
            
            let icon = 'bi-check-circle-fill';
            if (type === 'error') icon = 'bi-exclamation-triangle-fill';
            if (type === 'warning') icon = 'bi-exclamation-circle-fill';

            toast.innerHTML = `
                <i class="bi ${icon}"></i>
                <span>${message}</span>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function getAuthHeaders() {
            return {
                'Authorization': 'Basic ' + btoa(authUsername + ':' + authPassword)
            };
        }

        // Fetch categories & tags
        async function fetchCategories() {
            const headers = getAuthHeaders();
            try {
                const response = await fetch(`${apiBase}/categories`, { headers });
                if (response.ok) {
                    categories = await response.json();
                    const select = document.getElementById('category_id');
                    select.innerHTML = '';
                    categories.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });
                }
            } catch (err) {
                console.error('Failed to load categories', err);
            }
        }

        async function fetchTags() {
            const headers = getAuthHeaders();
            try {
                const response = await fetch(`${apiBase}/tags`, { headers });
                if (response.ok) {
                    tags = await response.json();
                    const container = document.getElementById('tags-container');
                    container.innerHTML = '';
                    tags.forEach(t => {
                        container.innerHTML += `
                            <label class="checkbox-group" style="text-transform:none; font-size:12px; color:var(--text-main);">
                                <input type="checkbox" name="tags" value="${t.id}">
                                ${t.name}
                            </label>
                        `;
                    });
                }
            } catch (err) {
                console.error('Failed to load tags', err);
            }
        }

        // Toggle featured image source UI
        function toggleImageSource(source) {
            document.getElementById('image-source-file').style.display = source === 'file' ? 'block' : 'none';
            document.getElementById('image-source-base64').style.display = source === 'base64' ? 'block' : 'none';
            document.getElementById('image-source-url').style.display = source === 'url' ? 'block' : 'none';
            clearFeaturedImage();
        }

        // Drag/Drop and file input handlers
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                uploadedImageFile = file;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-thumbnail-container');
                    const img = document.getElementById('preview-thumbnail-img');
                    img.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function clearFeaturedImage() {
            uploadedImageFile = null;
            document.getElementById('image-file-input').value = '';
            document.getElementById('image-base64-input').value = '';
            document.getElementById('image-url-input').value = '';
            document.getElementById('preview-thumbnail-container').style.display = 'none';
            document.getElementById('preview-thumbnail-img').src = '';
        }

        // Load and display Articles
        async function refreshArticles() {
            const grid = document.getElementById('articles-directory');
            grid.innerHTML = `
                <div class="full-page-loading">
                    <span class="spinner"></span>
                    <span>Retrieving article directory...</span>
                </div>
            `;

            const headers = getAuthHeaders();
            
            try {
                // Since credentials are set silently, fetch list from Admin endpoints to get all articles
                const p1 = fetch(`${apiBase}/cms/admin/articles/published`, { headers }).then(r => r.json());
                const p2 = fetch(`${apiBase}/cms/admin/articles/unpublished`, { headers }).then(r => r.json());
                const p3 = fetch(`${apiBase}/cms/admin/articles/pending`, { headers }).then(r => r.json());
                const p4 = fetch(`${apiBase}/cms/admin/articles/rejected`, { headers }).then(r => r.json());
                
                const results = await Promise.all([p1, p2, p3, p4]);
                let fetchedArticles = [...results[0], ...results[1], ...results[2], ...results[3]];

                // Remove duplicates by ID
                const uniqueMap = {};
                fetchedArticles.forEach(a => { if (a && a.id) uniqueMap[a.id] = a; });
                articles = Object.values(uniqueMap).sort((a, b) => b.id - a.id);
                
                renderArticlesList();

            } catch (err) {
                console.error(err);
                grid.innerHTML = '<div class="full-page-loading"><i class="bi bi-exclamation-octagon" style="font-size:32px; color:var(--accent-rose);"></i><span>Failed to load articles.</span></div>';
            }
        }

        // Render matching articles
        function renderArticlesList() {
            const grid = document.getElementById('articles-directory');
            grid.innerHTML = '';

            let filtered = articles;

            // Apply filter tab
            if (activeFilter !== 'all') {
                filtered = filtered.filter(a => a.status === activeFilter);
            }

            // Apply search filter
            if (searchQuery.trim() !== '') {
                const query = searchQuery.toLowerCase();
                filtered = filtered.filter(a => 
                    a.title.toLowerCase().includes(query) || 
                    (a.summary && a.summary.toLowerCase().includes(query)) ||
                    a.content.toLowerCase().includes(query)
                );
            }

            if (filtered.length === 0) {
                grid.innerHTML = '<div class="full-page-loading"><span>No articles match the current filter/search.</span></div>';
                return;
            }

            filtered.forEach(art => {
                let badgeClass = 'badge-draft';
                if (art.status === 'pending') badgeClass = 'badge-pending';
                if (art.status === 'published') badgeClass = 'badge-published';
                if (art.status === 'rejected') badgeClass = 'badge-rejected';

                let imageHtml = `
                    <div class="fallback">
                        <i class="bi bi-image"></i>
                        <span>No Featured Image</span>
                    </div>
                `;

                if (art.featured_image) {
                    imageHtml = `<img src="${art.featured_image}" alt="Article Thumbnail" onerror="this.onerror=null; this.parentNode.innerHTML='<div class=fallback><i class=\'bi bi-file-earmark-image\'></i><span>Failed to load image</span></div>'">`;
                }

                const catName = art.categories && art.categories.length > 0 
                    ? art.categories.map(c => c.name).join(', ') 
                    : (art.category ? art.category.name : 'Uncategorized');
                const dateString = art.created_at ? new Date(art.created_at).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'}) : 'Recently';

                let tagPills = '';
                if (art.tags && art.tags.length > 0) {
                    art.tags.forEach(t => {
                        tagPills += `<span class="tag-pill">${t.name}</span>`;
                    });
                }

                grid.innerHTML += `
                    <article class="article-card" onclick="openArticleModal(${art.id})">
                        <div class="card-image">
                            ${imageHtml}
                            <span class="card-badge ${badgeClass}">${art.status}</span>
                        </div>
                        <div class="card-content">
                            <span class="card-category">${catName}</span>
                            <h3 class="card-title">${art.title}</h3>
                            <p class="card-summary">${art.summary || 'No summary description provided.'}</p>
                            <div class="tags-wrap">${tagPills}</div>
                        </div>
                        <div class="card-footer">
                            <span>${dateString}</span>
                            <div class="card-stats">
                                <span><i class="bi bi-eye"></i> ${art.views_count || 0}</span>
                                <span><i class="bi bi-share"></i> ${art.shares_count || 0}</span>
                            </div>
                        </div>
                    </article>
                `;
            });
        }

        // Filtering & Search functions
        function setFilter(filter) {
            const tabs = ['all', 'published', 'draft', 'pending', 'rejected'];
            tabs.forEach(t => {
                document.getElementById(`tab-${t}`).className = `filter-tab ${t === filter ? 'active' : ''}`;
            });
            activeFilter = filter;
            renderArticlesList();
        }

        // Handle Search
        function handleSearch(event) {
            searchQuery = event.target.value;
            renderArticlesList();
        }

        // Submit Form
        async function submitArticle(event) {
            event.preventDefault();

            const headers = getAuthHeaders();
            const btn = document.getElementById('submit-btn');
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner"></span> Creating...`;

            try {
                const contentHtml = quill.root.innerHTML;
                if (quill.getText().trim() === '') {
                    showNotification('Article content is required.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    return;
                }

                const formData = new FormData();
                formData.append('title', document.getElementById('title').value.trim());
                formData.append('content', contentHtml);
                formData.append('summary', document.getElementById('summary').value.trim());
                const selectedCategories = Array.from(document.getElementById('category_id').selectedOptions).map(opt => parseInt(opt.value));
                formData.append('category_id', JSON.stringify(selectedCategories));
                formData.append('status', document.getElementById('status').value);
                formData.append('is_featured', document.getElementById('is_featured').checked ? 'true' : 'false');
                
                const inputterId = document.getElementById('inputter_id').value;
                if (inputterId) {
                    formData.append('inputter_id', inputterId);
                }
                const authoriserId = document.getElementById('authoriser_id').value;
                if (authoriserId) {
                    formData.append('authoriser_id', authoriserId);
                }

                // Add selected tags
                const selectedTags = [];
                document.querySelectorAll('input[name="tags"]:checked').forEach(cb => {
                    selectedTags.push(parseInt(cb.value));
                });
                if (selectedTags.length > 0) {
                    formData.append('tags', JSON.stringify(selectedTags));
                }

                // Handle Featured Image Source
                const imageSource = document.querySelector('input[name="image-source"]:checked').value;
                if (imageSource === 'file') {
                    if (uploadedImageFile) {
                        formData.append('featured_image', uploadedImageFile);
                    }
                } else if (imageSource === 'base64') {
                    const b64Val = document.getElementById('image-base64-input').value.trim();
                    if (b64Val) {
                        formData.append('featured_image', b64Val);
                    }
                } else if (imageSource === 'url') {
                    const urlVal = document.getElementById('image-url-input').value.trim();
                    if (urlVal) {
                        formData.append('featured_image', urlVal);
                    }
                }

                const response = await fetch(`${apiBase}/cms/articles`, {
                    method: 'POST',
                    headers: headers,
                    body: formData
                });

                if (response.status === 201) {
                    const created = await response.json();
                    showNotification('Article created successfully!');
                    
                    // Reset Form
                    document.getElementById('article-form').reset();
                    quill.setContents([]);
                    clearFeaturedImage();
                    
                    // Reload
                    refreshArticles();
                } else {
                    const err = await response.json();
                    showNotification(err.message || 'Validation error while submitting article.', 'error');
                }

            } catch (err) {
                console.error(err);
                showNotification('Network communication error.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }

        // Modal opening/closing
        function openArticleModal(id) {
            const art = articles.find(a => a.id === id);
            if (!art) return;

            document.getElementById('modal-title').innerText = art.title;
            document.getElementById('modal-view-page-btn').href = `${siteBase}/articles/${art.slug}`;
            
            // Image
            const imgContainer = document.getElementById('modal-image-container');
            const img = document.getElementById('modal-img');
            if (art.featured_image) {
                img.src = art.featured_image;
                imgContainer.style.display = 'block';
            } else {
                imgContainer.style.display = 'none';
            }

            // Meta
            const authorName = art.inputter ? (art.inputter.name || art.inputter.email) : 'Unknown Author';
            document.getElementById('modal-author').innerHTML = `<i class="bi bi-person-fill"></i> ${authorName}`;
            
            const catName = art.categories && art.categories.length > 0 
                ? art.categories.map(c => c.name).join(', ') 
                : (art.category ? art.category.name : 'Uncategorized');
            document.getElementById('modal-category').innerHTML = `<i class="bi bi-tag-fill"></i> ${catName}`;
            
            const dateString = art.created_at ? new Date(art.created_at).toLocaleDateString(undefined, {month: 'long', day: 'numeric', year: 'numeric'}) : 'Recently';
            document.getElementById('modal-date').innerHTML = `<i class="bi bi-calendar-event-fill"></i> ${dateString}`;
            
            document.getElementById('modal-views').innerHTML = `<i class="bi bi-eye-fill"></i> ${art.views_count || 0} Views`;
            document.getElementById('modal-shares').innerHTML = `<i class="bi bi-share-fill"></i> ${art.shares_count || 0} Shares`;

            // Badge
            let badgeClass = 'badge-draft';
            if (art.status === 'pending') badgeClass = 'badge-pending';
            if (art.status === 'published') badgeClass = 'badge-published';
            if (art.status === 'rejected') badgeClass = 'badge-rejected';
            document.getElementById('modal-status-badge').className = `card-badge ${badgeClass}`;
            document.getElementById('modal-status-badge').style.position = 'relative';
            document.getElementById('modal-status-badge').style.top = '0';
            document.getElementById('modal-status-badge').style.left = '0';
            document.getElementById('modal-status-badge').innerText = art.status;

            // Content (Render Simple Markdown or HTML directly)
            let htmlContent = art.content;
            if (!/<[a-z][\s\S]*>/i.test(htmlContent)) {
                htmlContent = htmlContent.replace(/\n/g, '<br>');
                htmlContent = htmlContent.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                htmlContent = htmlContent.replace(/\*(.*?)\*/g, '<em>$1</em>');
            }
            document.getElementById('modal-content').innerHTML = htmlContent;

            // Open Modal
            document.getElementById('reader-modal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('reader-modal').classList.remove('active');
        }

        // Close modal on background click
        document.getElementById('reader-modal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('reader-modal')) {
                closeModal();
            }
        });
    </script>
</body>
</html>
