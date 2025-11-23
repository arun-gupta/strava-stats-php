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
        <div class="container" style="display: flex; justify-content: center; align-items: center;">
            <h1><a href="/" style="color: white; text-decoration: none;">Strava Stats</a></h1>
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
