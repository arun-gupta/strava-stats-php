<div style="padding: 1rem 0;">
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
            <button id="heatmapTab" class="tab-button" onclick="switchTab('heatmap')"
                    style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent;
                           color: #666; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                🔥 Heatmap
            </button>
            <button id="runningTab" class="tab-button" onclick="switchTab('running')"
                    style="padding: 0.75rem 1.5rem; background: none; border: none; border-bottom: 3px solid transparent;
                           color: #666; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                🏃 Running Stats
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

                <!-- Insights Section -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: #fff9e6; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <h4 style="margin-top: 0; margin-bottom: 1rem; color: #92400e;">💡 Insights</h4>
                    <div style="display: grid; gap: 1rem;">
                        <?php if ($longestDurationType): ?>
                            <div style="padding: 1rem; background: white; border-radius: 6px;">
                                <div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Most Time Spent</div>
                                <div style="font-size: 1.25rem; font-weight: 600; color: #fc4c02;">
                                    <?= htmlspecialchars($longestDurationType) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($weeklyAverage > 0): ?>
                            <div style="padding: 1rem; background: white; border-radius: 6px;">
                                <div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Weekly Average</div>
                                <div style="font-size: 1.25rem; font-weight: 600; color: #fc4c02;">
                                    <?php
                                    $avgHours = floor($weeklyAverage / 3600);
                                    $avgMinutes = round(($weeklyAverage % 3600) / 60);
                                    echo $avgHours . 'h ' . $avgMinutes . 'm';
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($mostActiveDay): ?>
                            <div style="padding: 1rem; background: white; border-radius: 6px;">
                                <div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Most Active On</div>
                                <div style="font-size: 1.25rem; font-weight: 600; color: #fc4c02;">
                                    <?= htmlspecialchars($mostActiveDay) ?>s
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

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

    <!-- Heatmap Tab Content -->
    <div id="heatmapContent" class="tab-content" style="display: none;">
        <div style="margin-top: 2rem; padding: 1.5rem; background: #1a1d29; border-radius: 8px;">
            <?php if ($totalActivities > 0): ?>
                <!-- Activity Calendar Section -->
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 style="margin: 0; color: #fff;">Activity Calendar</h4>
                        <div style="display: flex; gap: 0.5rem;">
                            <button id="allActivitiesBtn" onclick="switchHeatmapMode('all')"
                                    style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
                                All Activities
                            </button>
                            <button id="runningOnlyBtn" onclick="switchHeatmapMode('running')"
                                    style="padding: 0.5rem 1rem; background: #2d3748; color: #9ca3af; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
                                Running Only
                            </button>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem; font-size: 0.875rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background: #2d3748; border-radius: 2px;"></div>
                            <span style="color: #9ca3af;">No Activity</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background: #fbbf24; border-radius: 2px;"></div>
                            <span style="color: #9ca3af;">&lt; 1h</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background: #f97316; border-radius: 2px;"></div>
                            <span style="color: #9ca3af;">1-2h</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 16px; height: 16px; background: #fc4c02; border-radius: 2px;"></div>
                            <span style="color: #9ca3af;">2h+</span>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div style="display: flex; gap: 4px;">
                        <?php foreach ($calendarDays as $day): ?>
                            <?php
                            $tooltipText = $day['date']->format('M j, Y');
                            if ($day['hasActivity']) {
                                $hours = floor($day['timeSpent'] / 3600);
                                $minutes = floor(($day['timeSpent'] % 3600) / 60);
                                $tooltipText .= ' | ' . $hours . 'h ' . $minutes . 'm total';
                                $tooltipText .= ' | ' . $day['activityCount'] . ' ' . ($day['activityCount'] === 1 ? 'activity' : 'activities');
                                $tooltipText .= ' | ';
                                $activityParts = [];
                                foreach ($day['activities'] as $activity) {
                                    $actHours = floor($activity['movingTime'] / 3600);
                                    $actMinutes = floor(($activity['movingTime'] % 3600) / 60);
                                    $distance = round($activity['distance'] / 1000, 1);
                                    $activityParts[] = $activity['type'] . ': ' . $distance . 'km, ' . $actHours . 'h ' . $actMinutes . 'm';
                                }
                                $tooltipText .= implode(' | ', $activityParts);
                            } else {
                                $tooltipText .= ' | No activity';
                            }
                            ?>
                            <div class="heatmap-cell"
                                 style="width: 60px; height: 60px; background: <?= htmlspecialchars($day['color']) ?>;
                                        border-radius: 4px; display: flex; align-items: center; justify-content: center;
                                        position: relative; cursor: pointer; transition: all 0.2s ease-in-out;"
                                 title="<?= htmlspecialchars($tooltipText) ?>"
                                 onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.3)';"
                                 onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Workout Statistics Section -->
                <div>
                    <h4 style="margin: 0 0 1.5rem 0; color: #fff;">Workout Statistics</h4>
                    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 2rem;">
                        <!-- Workout Days -->
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Workout Days
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #fff;">
                                <?= $totalActiveDays ?>
                            </div>
                        </div>

                        <!-- Missed Days -->
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Missed Days
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #fff;">
                                <?= $restDays ?>
                            </div>
                        </div>

                        <!-- Current Streak -->
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Current Streak
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #fff;">
                                <?= $currentStreak ?>
                            </div>
                        </div>

                        <!-- Days Since Last -->
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Days Since Last
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #fff;">
                                <?= $daysSinceLastActivity ?>
                            </div>
                        </div>

                        <!-- Longest Gap -->
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Longest Gap
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #fff;">
                                <?= $longestGap ?>
                            </div>
                        </div>

                        <!-- Total Gap Days -->
                        <div style="text-align: center;">
                            <div style="font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Total Gap Days
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #fff;">
                                <?= $totalGapDays ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🔥</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;">No Streak Data Yet</h3>
                    <p style="font-size: 1.1rem;">Start logging activities to build your streak!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Running Stats Tab Content -->
    <div id="runningContent" class="tab-content" style="display: none;">
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #fc4c02;">
            <?php if ($totalRuns > 0): ?>
                <!-- Running Summary Stats -->
                <div style="margin-top: 1rem;">
                    <h4 style="margin-bottom: 1rem; text-align: center;">🏃 Running Summary</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <!-- Total Runs -->
                        <div style="padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #fc4c02;">
                            <div style="font-size: 0.875rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Total Runs
                            </div>
                            <div style="font-size: 2.5rem; font-weight: 700; color: #fc4c02;">
                                <?= $totalRuns ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #666; margin-top: 0.25rem;">
                                <?= $totalRuns === 1 ? 'run' : 'runs' ?> completed
                            </div>
                        </div>

                        <!-- Total Distance -->
                        <div style="padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <div style="font-size: 0.875rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Total Distance
                            </div>
                            <div style="font-size: 2.5rem; font-weight: 700; color: #2d3748;">
                                <?= number_format($totalRunningDistance / 1000, 1) ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #666; margin-top: 0.25rem;">
                                kilometers
                            </div>
                        </div>

                        <!-- Average Pace -->
                        <div style="padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <div style="font-size: 0.875rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                                Average Pace
                            </div>
                            <div style="font-size: 2.5rem; font-weight: 700; color: #48bb78;">
                                <?php
                                $paceMinutes = floor($averagePace);
                                $paceSeconds = round(($averagePace - $paceMinutes) * 60);
                                echo $paceMinutes . ':' . str_pad($paceSeconds, 2, '0', STR_PAD_LEFT);
                                ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #666; margin-top: 0.25rem;">
                                min/km
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🏃</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;">No Running Data Yet</h3>
                    <p style="font-size: 1.1rem;">Start logging runs to see your running statistics!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 2rem; padding: 1.5rem; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #999;">
        <h3 style="margin-top: 0;">🚧 Coming Soon</h3>
        <ul style="margin-top: 1rem; line-height: 1.8;">
            <li><strong>Trends</strong> - Mileage and pace trends over time</li>
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

        // Heatmap mode switching
        function switchHeatmapMode(mode) {
            const allBtn = document.getElementById('allActivitiesBtn');
            const runningBtn = document.getElementById('runningOnlyBtn');

            if (mode === 'all') {
                // Style active button
                allBtn.style.background = '#3b82f6';
                allBtn.style.color = 'white';
                runningBtn.style.background = '#2d3748';
                runningBtn.style.color = '#9ca3af';
            } else if (mode === 'running') {
                // Show coming soon message
                alert('Running Only mode coming soon! This will filter the heatmap to show only running activities.');

                // Keep "All Activities" selected
                allBtn.style.background = '#3b82f6';
                allBtn.style.color = 'white';
                runningBtn.style.background = '#2d3748';
                runningBtn.style.color = '#9ca3af';
            }
        }
    </script>
</div>
