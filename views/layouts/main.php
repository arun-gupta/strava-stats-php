<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Strava Activity Analyzer' ?></title>
    <link rel="stylesheet" href="/build/assets/app.css">
</head>
<body>
    <header>
        <div class="container">
            <h1><a href="/" style="color: white; text-decoration: none;">Strava Stats</a></h1>
        </div>
    </header>

    <main class="container">
        <?= $content ?? '' ?>
    </main>

    <footer class="container" style="margin-top: 2rem; padding: 1rem 0; text-align: center; color: #666;">
        <p>&copy; <?= date('Y') ?> Strava Activity Analyzer</p>
    </footer>

    <script type="module" src="/build/assets/app.js"></script>
</body>
</html>
