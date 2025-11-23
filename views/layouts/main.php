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

    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <h1><a href="/" style="color: white; text-decoration: none;">Strava Stats</a></h1>

            <?php if ($isAuthenticated && $athlete): ?>
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <?php if (!empty($athlete['profile'])): ?>
                            <img src="<?= htmlspecialchars($athlete['profile']) ?>"
                                 alt="<?= htmlspecialchars($athlete['firstname'] ?? 'Athlete') ?>"
                                 style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid white;">
                        <?php endif; ?>
                        <span style="color: white; font-weight: 500; white-space: nowrap;">
                            <?= htmlspecialchars(($athlete['firstname'] ?? '') . ' ' . ($athlete['lastname'] ?? '')) ?>
                        </span>
                    </div>
                    <a href="/signout"
                       style="color: white; text-decoration: none; padding: 8px 16px;
                              border: 2px solid white; border-radius: 4px; font-size: 14px;
                              font-weight: 600; background-color: rgba(255,255,255,0.1);
                              white-space: nowrap;"
                       onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                       onmouseout="this.style.backgroundColor='rgba(255,255,255,0.1)'">
                        Sign Out
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <?= $content ?? '' ?>
    </main>

    <footer class="container" style="margin-top: 2rem; padding: 1rem 0; text-align: center; color: #666;">
        <p>&copy; <?= date('Y') ?> Strava Activity Analyzer</p>
    </footer>

    <script type="module" src="<?= Vite::asset('resources/js/app.js') ?>"></script>
</body>
</html>
