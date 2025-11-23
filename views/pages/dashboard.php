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

    <!-- Error Message -->
    <?php if (isset($error) && $error): ?>
        <div style="margin-top: 2rem; padding: 1.5rem; background: #fff3f3; border-radius: 8px; border-left: 4px solid #e53e3e;">
            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                <div style="font-size: 1.5rem;">⚠️</div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 0.5rem 0; color: #c53030;">Unable to Load Activities</h3>
                    <p style="margin: 0 0 1rem 0; color: #742a2a;"><?= htmlspecialchars($error) ?></p>
                    <button onclick="window.location.reload()"
                            style="padding: 8px 16px; background-color: #fc4c02; color: white; border: none;
                                   border-radius: 4px; font-weight: 600; cursor: pointer;">
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div style="margin-top: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
        <!-- Date Range Card -->
        <div style="padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                📅 Date Range
            </div>
            <div style="font-size: 1.25rem; font-weight: 600; color: #333;">
                <?= $startDate->format('M j') ?> - <?= $endDate->format('M j, Y') ?>
            </div>
            <div style="font-size: 0.875rem; color: #666; margin-top: 0.25rem;">
                Last 7 Days
            </div>
        </div>

        <!-- Total Activities Card -->
        <div style="padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                🏃 Total Activities
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #fc4c02;">
                <?= $totalActivities ?>
            </div>
            <div style="font-size: 0.875rem; color: #666; margin-top: 0.25rem;">
                <?= $totalActivities === 1 ? 'Activity' : 'Activities' ?> logged
            </div>
        </div>

        <!-- Total Moving Time Card -->
        <div style="padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
            <div style="font-size: 0.875rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                ⏱️ Total Moving Time
            </div>
            <div style="font-size: 2rem; font-weight: 700; color: #fc4c02;">
                <?php
                $hours = floor($totalMovingTime / 3600);
                $minutes = floor(($totalMovingTime % 3600) / 60);
                echo $hours . 'h ' . $minutes . 'm';
                ?>
            </div>
            <div style="font-size: 0.875rem; color: #666; margin-top: 0.25rem;">
                Time in motion
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div style="margin-top: 2rem;">
        <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0;">
            <button id="overviewTab" class="tab-button active" onclick="switchTab('overview')"
                    style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid #fc4c02;
                           color: #fc4c02; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                📊 Overview
            </button>
            <button id="durationTab" class="tab-button" onclick="switchTab('duration')"
                    style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent;
                           color: #666; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                ⏱️ Duration
            </button>
        </div>
    </div>

    <!-- Overview Tab Content -->
    <div id="overviewContent" class="tab-content" style="display: block;">
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #fc4c02;">
            <?php if ($totalActivities > 0): ?>
            <!-- Activity Chart -->
            <div style="margin-top: 2rem;">
                <h4 style="margin-bottom: 1rem; text-align: center;">Activity Distribution</h4>
                <div style="max-width: 400px; margin: 0 auto; position: relative;">
                    <!-- Loading skeleton -->
                    <div id="chartLoading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px;">
                        <div style="width: 50px; height: 50px; border: 4px solid #f0f0f0; border-top: 4px solid #fc4c02; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                        <p style="margin-top: 1rem; color: #666;">Loading chart...</p>
                    </div>
                    <canvas id="activityChart" style="display: none;"></canvas>
                </div>
            </div>

            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>

            <!-- Activity Breakdown Cards -->
            <div style="margin-top: 2rem;">
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

            <!-- Pass data to JavaScript -->
            <script>
                window.activityData = <?= json_encode($activityCounts) ?>;
                console.log('Activity data set:', window.activityData);
                console.log('Canvas element exists:', document.getElementById('activityChart'));
            </script>
        <?php else: ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📊</div>
                <h3 style="color: #333; margin-bottom: 0.5rem;">No Activities Yet</h3>
                <p style="color: #666; margin-bottom: 1.5rem; max-width: 400px; margin-left: auto; margin-right: auto;">
                    We couldn't find any activities in the last 7 days. Start tracking your workouts on Strava to see your stats here!
                </p>
                <a href="https://www.strava.com/activities/new"
                   target="_blank"
                   style="display: inline-block; padding: 12px 24px; background-color: #fc4c02; color: white;
                          text-decoration: none; border-radius: 4px; font-weight: 600; margin-bottom: 1.5rem;">
                    Log an Activity on Strava
                </a>

                <!-- Example Chart -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: white; border-radius: 8px; border: 2px dashed #ddd;">
                    <h4 style="color: #666; margin-top: 0; margin-bottom: 1rem;">What you'll see:</h4>
                    <div style="max-width: 300px; margin: 0 auto; opacity: 0.6;">
                        <canvas id="exampleChart"></canvas>
                    </div>
                    <p style="color: #999; font-size: 0.9rem; margin-top: 1rem; margin-bottom: 0;">
                        Your activity distribution chart will appear here
                    </p>
                </div>
            </div>

            <!-- Pass example data for empty state chart -->
            <script>
                window.exampleData = {
                    'Run': 6,
                    'Ride': 3,
                    'Swim': 2
                };
            </script>
            <?php endif; ?>
        </div>
    </div>

    <!-- Duration Tab Content -->
    <div id="durationContent" class="tab-content" style="display: none;">
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #fc4c02;">
            <?php if ($totalActivities > 0): ?>
                <!-- Duration Pie Chart -->
                <div style="margin-top: 1rem;">
                    <h4 style="margin-bottom: 1rem; text-align: center;">Time Spent by Activity Type</h4>
                    <div style="max-width: 400px; margin: 0 auto;">
                        <canvas id="durationChart"></canvas>
                    </div>
                </div>

                <!-- Duration Breakdown Cards -->
                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">Duration Breakdown:</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <?php foreach ($movingTimeByType as $type => $seconds): ?>
                            <?php
                            $hours = floor($seconds / 3600);
                            $minutes = floor(($seconds % 3600) / 60);
                            ?>
                            <div style="padding: 1rem; background: white; border-radius: 6px; border: 1px solid #ddd;">
                                <div style="font-weight: 600; color: #333; margin-bottom: 0.5rem;">
                                    <?= htmlspecialchars($type) ?>
                                </div>
                                <div style="font-size: 1.5rem; color: #fc4c02; font-weight: 700;">
                                    <?= $hours ?>h <?= $minutes ?>m
                                </div>
                                <div style="font-size: 0.875rem; color: #666; margin-top: 0.25rem;">
                                    Moving time
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pass duration data to JavaScript -->
                <script>
                    window.durationData = <?= json_encode($movingTimeByType) ?>;
                </script>

                <!-- Total Time Summary -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #fc4c02;">
                    <div style="text-align: center;">
                        <div style="font-size: 0.875rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                            Total Moving Time
                        </div>
                        <div style="font-size: 2.5rem; font-weight: 700; color: #fc4c02;">
                            <?php
                            $totalHours = floor($totalMovingTime / 3600);
                            $totalMinutes = floor(($totalMovingTime % 3600) / 60);
                            echo $totalHours . 'h ' . $totalMinutes . 'm';
                            ?>
                        </div>
                        <div style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;">
                            Across all <?= $totalActivities ?> activities
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">⏱️</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;">No Duration Data</h3>
                    <p style="font-size: 1.1rem;">Start logging activities to see your time breakdown</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 2rem; padding: 1.5rem; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #999;">
        <h3 style="margin-top: 0;">🚧 Coming Soon</h3>
        <ul style="margin-top: 1rem; line-height: 1.8;">
            <li><strong>Heatmap</strong> - Training consistency and streaks calendar</li>
            <li><strong>Trends</strong> - Mileage and pace trends over time</li>
            <li><strong>Running Stats</strong> - Personal records and distance distribution</li>
        </ul>
    </div>

    <!-- Tab Switching Script -->
    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.style.borderBottomColor = 'transparent';
                button.style.color = '#666';
            });

            // Show selected tab content
            document.getElementById(tabName + 'Content').style.display = 'block';

            // Add active class to selected button
            const activeButton = document.getElementById(tabName + 'Tab');
            activeButton.style.borderBottomColor = '#fc4c02';
            activeButton.style.color = '#fc4c02';
        }
    </script>
</div>
