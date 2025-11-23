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

    <!-- Activity Summary -->
    <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #fc4c02;">
        <h3 style="margin-top: 0;">📊 Your Recent Activities (Last 7 Days)</h3>

        <?php if ($totalActivities > 0): ?>
            <p style="font-size: 1.2rem; margin: 1rem 0;">
                <strong>Total Activities: <?= $totalActivities ?></strong>
            </p>

            <div style="margin-top: 1.5rem;">
                <h4 style="margin-bottom: 0.5rem;">Activity Breakdown by Type:</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    <?php foreach ($activityCounts as $type => $count): ?>
                        <div style="padding: 1rem; background: white; border-radius: 6px; border: 1px solid #ddd;">
                            <div style="font-weight: 600; color: #333; margin-bottom: 0.25rem;">
                                <?= htmlspecialchars($type) ?>
                            </div>
                            <div style="font-size: 1.5rem; color: #fc4c02; font-weight: 700;">
                                <?= $count ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p style="color: #666;">
                No activities found in the last 7 days. Go log some activities on Strava and refresh this page!
            </p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 2rem; padding: 1.5rem; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #999;">
        <h3 style="margin-top: 0;">🚧 Coming Soon</h3>
        <ul style="margin-top: 1rem; line-height: 1.8;">
            <li><strong>Overview Chart</strong> - Interactive pie chart of activity distribution</li>
            <li><strong>Duration</strong> - Time spent on each activity type</li>
            <li><strong>Heatmap</strong> - Training consistency and streaks calendar</li>
            <li><strong>Trends</strong> - Mileage and pace trends over time</li>
            <li><strong>Running Stats</strong> - Personal records and distance distribution</li>
        </ul>
    </div>
</div>
