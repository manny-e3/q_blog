<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - Q-BLOG</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bg-dark: #070a13;
            --bg-card: rgba(18, 24, 38, 0.65);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --accent-teal: #0d9488;
            --accent-indigo: #6366f1;
            --accent-violet: #8b5cf6;
            --border-color: rgba(255, 255, 255, 0.08);
            --font-display: 'Outfit', sans-serif;
            --font-sans: 'Plus Jakarta Sans', sans-serif;
            --radius-lg: 16px;
            --radius-md: 10px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            text-decoration: none;
            color: inherit;
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

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: var(--transition);
        }

        .btn-back:hover {
            color: #fff;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 40px;
            z-index: 5;
            position: relative;
        }

        @media (max-width: 992px) {
            .container {
                grid-template-columns: 1fr;
                padding: 20px;
            }
        }

        /* Glassmorphism Card panels */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Header Info */
        .article-header {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 24px;
        }

        .category-badge {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--accent-teal);
            letter-spacing: 1px;
        }

        .article-title {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 700;
            line-height: 1.25;
            color: #fff;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 13px;
            color: var(--text-muted);
            align-items: center;
        }

        .meta-row span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Featured image frame */
        .featured-image-frame {
            width: 100%;
            aspect-ratio: 21/9;
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
            background-color: rgba(255, 255, 255, 0.01);
            margin-bottom: 30px;
        }

        .featured-image-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .featured-image-frame .fallback {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 14px;
            gap: 10px;
        }

        /* Article Body Content styling */
        .article-content {
            font-size: 17px;
            line-height: 1.8;
            color: #e5e7eb;
        }

        .article-content p {
            margin-bottom: 20px;
        }

        .article-content h1, 
        .article-content h2, 
        .article-content h3 {
            font-family: var(--font-display);
            color: #fff;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .article-content h1 { font-size: 24px; }
        .article-content h2 { font-size: 20px; }
        .article-content h3 { font-size: 18px; }

        .article-content ul, 
        .article-content ol {
            margin-bottom: 20px;
            padding-left: 24px;
        }

        .article-content li {
            margin-bottom: 8px;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: var(--radius-md);
            margin: 20px 0;
            display: block;
        }

        .article-content iframe {
            width: 100%;
            aspect-ratio: 16/9;
            border-radius: var(--radius-md);
            margin: 20px 0;
            border: none;
            display: block;
        }

        .article-content blockquote {
            border-left: 4px solid var(--accent-indigo);
            padding-left: 20px;
            margin: 20px 0;
            font-style: italic;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.02);
            padding: 16px 20px;
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
        }

        /* Tags wrap */
        .tags-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 30px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .tag-badge {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
        }

        /* Sidebar panels */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .stat-card {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-card i {
            font-size: 24px;
            color: var(--accent-teal);
            background: rgba(13, 148, 136, 0.1);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card.shares i {
            color: var(--accent-indigo);
            background: rgba(99, 102, 241, 0.1);
        }

        .stat-card-title {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 500;
        }

        .stat-card-value {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 700;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border: none;
            border-radius: var(--radius-md);
            padding: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            background: linear-gradient(135deg, var(--accent-teal), var(--accent-indigo));
            color: #fff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.25);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
        }

        .share-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .share-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-md);
            color: var(--text-main);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
        }

        .share-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent-indigo);
        }

        .share-btn i {
            font-size: 16px;
            color: var(--text-muted);
        }

        .share-btn:hover i {
            color: var(--accent-indigo);
        }

        /* Toast Notifications */
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

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <header>
        <a href="{{ url('/tester') }}" class="logo">
            <i class="bi bi-rocket-takeoff-fill"></i>
            <h1>Q-BLOG</h1>
        </a>
        <a href="{{ url('/tester') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Console
        </a>
    </header>

    <div class="container">
        <!-- Main Article Section -->
        <main style="display: flex; flex-direction: column;">
            <div class="glass-card" style="padding: 40px;">
                <div class="article-header">
                    <span class="category-badge">{{ $article->category ? $article->category->name : 'Uncategorized' }}</span>
                    <h1 class="article-title">{{ $article->title }}</h1>
                    
                    <div class="meta-row">
                        <span><i class="bi bi-person-fill"></i> {{ $article->inputter ? ($article->inputter['name'] ?? $article->inputter['email']) : 'Unknown Author' }}</span>
                        <span><i class="bi bi-calendar-event-fill"></i> {{ $article->created_at ? $article->created_at->format('M d, Y') : 'Recently' }}</span>
                    </div>
                </div>

                <div class="featured-image-frame">
                    @if ($article->featured_image)
                        <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" onerror="this.onerror=null; this.parentNode.innerHTML='<div class=fallback><i class=\'bi bi-file-earmark-image\'></i><span>Failed to load image</span></div>'">
                    @else
                        <div class="fallback">
                            <i class="bi bi-image"></i>
                            <span>No Featured Image Provided</span>
                        </div>
                    @endif
                </div>

                <div class="article-content">
                    @if (preg_match('/<[a-z][\s\S]*>/i', $article->content))
                        {!! $article->content !!}
                    @else
                        {!! nl2br(e($article->content)) !!}
                    @endif
                </div>

                @if ($article->tags && count($article->tags) > 0)
                    <div class="tags-wrap">
                        @foreach ($article->tags as $tag)
                            <span class="tag-badge">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </main>

        <!-- Sidebar Section -->
        <aside class="sidebar">
            <!-- Stats -->
            <div class="glass-card" style="display: flex; flex-direction: column; gap: 20px;">
                <div class="stat-card">
                    <i class="bi bi-eye-fill"></i>
                    <div>
                        <div class="stat-card-title">Views</div>
                        <div id="views-count" class="stat-card-value">{{ $article->views_count ?? 0 }}</div>
                    </div>
                </div>

                <div class="stat-card shares">
                    <i class="bi bi-share-fill"></i>
                    <div>
                        <div class="stat-card-title">Shares</div>
                        <div id="shares-count" class="stat-card-value">{{ $article->shares_count ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- PDF Download -->
            <div class="glass-card">
                <a href="{{ url('/api/v1/articles/' . $article->id . '/pdf') }}" class="btn-action">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Download Article (PDF)
                </a>
            </div>

            <!-- Share Buttons -->
            <div class="glass-card" style="display: flex; flex-direction: column; gap: 16px;">
                <h3 style="font-family: var(--font-display); font-size: 15px; font-weight: 600; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    Share this article
                </h3>
                <div class="share-list">
                    <div class="share-btn" onclick="trackShareEvent('LinkedIn')">
                        <i class="bi bi-linkedin"></i> Share on LinkedIn
                    </div>
                    <div class="share-btn" onclick="trackShareEvent('X')">
                        <i class="bi bi-twitter-x"></i> Share on X
                    </div>
                    <div class="share-btn" onclick="trackShareEvent('Facebook')">
                        <i class="bi bi-facebook"></i> Share on Facebook
                    </div>
                    <div class="share-btn" onclick="trackShareEvent('Instagram')">
                        <i class="bi bi-instagram"></i> Share on Instagram
                    </div>
                    <div class="share-btn" onclick="trackShareEvent('Copy Link')">
                        <i class="bi bi-link-45deg"></i> Copy Link Address
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        const articleId = "{{ $article->id }}";
        const apiBase = "{{ url('/api/v1') }}";

        // Toast notifications helper
        function showToastNotification(message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <i class="bi bi-check-circle-fill"></i>
                <span>${message}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // On Page Load: Track View automatically
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch(`${apiBase}/articles/${articleId}/view`, {
                    method: 'POST'
                });
                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('views-count').innerText = data.views_count;
                }
            } catch (err) {
                console.error('Failed to track view', err);
            }
        });

        // Trigger Social Share tracking
        async function trackShareEvent(platform) {
            try {
                const response = await fetch(`${apiBase}/articles/${articleId}/share`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ platform })
                });

                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('shares-count').innerText = data.shares_count;
                    
                    if (platform === 'Copy Link') {
                        navigator.clipboard.writeText(window.location.href);
                        showToastNotification('Article link copied to clipboard!');
                    } else {
                        showToastNotification(`Shared successfully on ${platform}!`);
                    }
                }
            } catch (err) {
                console.error('Failed to track share', err);
            }
        }
    </script>
</body>
</html>
