<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Strava Activity Analyzer' ?></title>
    <?php
    use App\Helpers\Vite;

    // Load CSS files if they exist
    foreach (Vite::css('resources/js/app.js') as $cssFile): ?>
        <link rel="stylesheet" href="<?= $cssFile ?>">
    <?php endforeach; ?>
</head>
<body>
    <?php
    // Start session to check authentication
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $isAuthenticated = isset($_SESSION['access_token']);
    $athlete = $_SESSION['athlete'] ?? null;

    // Ensure athlete is an array (handle legacy sessions)
    if (is_string($athlete)) {
        $athlete = null;
    }
    ?>

    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" style="position: absolute; left: -9999px; z-index: 999; padding: 1rem; background: #fc4c02; color: white; text-decoration: none; font-weight: 600;"
       onfocus="this.style.left='10px'; this.style.top='10px';"
       onblur="this.style.left='-9999px'; this.style.top='auto';">
        Skip to main content
    </a>

    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <h1><a href="/" style="color: white; text-decoration: none;">Strava Activity Analyzer</a></h1>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="https://github.com/arun-gupta/strava-stats-php" target="_blank" rel="noopener noreferrer"
                   style="display: flex; align-items: center; color: white; text-decoration: none; transition: opacity 0.2s;"
                   onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                    <svg height="32" viewBox="0 0 16 16" version="1.1" width="32" aria-hidden="true" style="fill: currentColor;">
                        <path d="M8 0c4.42 0 8 3.58 8 8a8.013 8.013 0 0 1-5.45 7.59c-.4.08-.55-.17-.55-.38 0-.27.01-1.13.01-2.2 0-.75-.25-1.23-.54-1.48 1.78-.2 3.65-.88 3.65-3.95 0-.88-.31-1.59-.82-2.15.08-.2.36-1.02-.08-2.12 0 0-.67-.22-2.2.82-.64-.18-1.32-.27-2-.27-.68 0-1.36.09-2 .27-1.53-1.03-2.2-.82-2.2-.82-.44 1.1-.16 1.92-.08 2.12-.51.56-.82 1.28-.82 2.15 0 3.06 1.86 3.75 3.64 3.95-.23.2-.44.55-.51 1.07-.46.21-1.61.55-2.33-.66-.15-.24-.6-.83-1.23-.82-.67.01-.27.38.01.53.34.19.73.9.82 1.13.16.45.68 1.31 2.69.94 0 .67.01 1.3.01 1.49 0 .21-.15.45-.55.38A7.995 7.995 0 0 1 0 8c0-4.42 3.58-8 8-8Z"></path>
                    </svg>
                </a>
                <?php if ($isAuthenticated && $athlete): ?>
                    <?php
                    // Use profile_medium from Strava API (fallback to profile if available)
                    $profilePhoto = $athlete['profile_medium'] ?? $athlete['profile'] ?? null;
                    ?>
                    <?php if ($profilePhoto): ?>
                        <img src="<?= htmlspecialchars($profilePhoto) ?>"
                             alt="<?= htmlspecialchars($athlete['firstname'] ?? 'Athlete') ?>"
                             style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid white;">
                    <?php endif; ?>
                    <span style="color: white; font-weight: 500; font-size: 0.9rem;">
                        <?= htmlspecialchars($athlete['firstname'] ?? 'Athlete') ?>
                    </span>
                    <a href="/signout"
                       style="padding: 8px 16px; background-color: white; color: #fc4c02; text-decoration: none;
                              border-radius: 4px; font-weight: 600; white-space: nowrap;">
                        Sign Out
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main id="main-content" class="container">
        <?= $content ?? '' ?>
    </main>

    <footer class="container" style="margin-top: 2rem; padding: 1rem 0; text-align: center; color: #666;">
        <p>&copy; <?= date('Y') ?> Strava Activity Analyzer</p>
    </footer>

    <script type="module" src="<?= Vite::asset('resources/js/app.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Timezone detection -->
    <script>
        // Detect user's timezone and store in cookie for server-side access
        (function() {
            const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            // Check if timezone cookie exists
            const getCookie = (name) => {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            };

            const currentCookie = getCookie('user_timezone');

            // Set cookie and reload if timezone not set or changed
            if (userTimezone && userTimezone !== currentCookie) {
                document.cookie = `user_timezone=${userTimezone}; path=/; max-age=31536000; SameSite=Lax`;

                // Send to server API as well
                fetch('/api/timezone', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ timezone: userTimezone })
                }).then(() => {
                    // Reload page to use correct timezone on server
                    if (!currentCookie) {
                        window.location.reload();
                    }
                }).catch(err => {
                    console.error('Failed to set timezone:', err);
                });
            }
        })();
    </script>
</body>
</html>
