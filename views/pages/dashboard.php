<?php
// Get athlete data from session
$athlete = $_SESSION['athlete'] ?? null;
$firstName = $athlete['firstname'] ?? 'Athlete';
?>

<div style="padding: 2rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div>
            <h2 style="margin: 0;">Welcome back, <?= htmlspecialchars($firstName) ?>! 👋</h2>
            <p style="color: #666; margin-top: 0.5rem;">
                Your personalized activity dashboard is ready.
            </p>
        </div>
        <a href="/signout"
           style="padding: 10px 20px; background-color: #fc4c02; color: white; text-decoration: none;
                  border-radius: 4px; font-weight: 600; white-space: nowrap;">
            Sign Out
        </a>
    </div>

    <div style="margin-top: 2rem; padding: 1.5rem; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #fc4c02;">
        <h3 style="margin-top: 0;">🚧 Dashboard Under Construction</h3>
        <p style="margin-bottom: 0;">
            Your activity analytics and visualizations will appear here soon. We're building:
        </p>
        <ul style="margin-top: 1rem; line-height: 1.8;">
            <li><strong>Overview</strong> - Activity count distribution by sport type</li>
            <li><strong>Duration</strong> - Time spent on each activity type</li>
            <li><strong>Heatmap</strong> - Training consistency and streaks calendar</li>
            <li><strong>Trends</strong> - Mileage and pace trends over time</li>
            <li><strong>Running Stats</strong> - Personal records and distance distribution</li>
        </ul>
    </div>

    <div style="margin-top: 2rem; text-align: center; padding: 2rem; border: 2px dashed #ddd; border-radius: 8px;">
        <p style="color: #999; font-size: 1.2rem;">
            📊 Activity widgets coming soon...
        </p>
        <p style="color: #666; margin-top: 0.5rem;">
            Connect with Strava data to see your personalized analytics here.
        </p>
    </div>
</div>
