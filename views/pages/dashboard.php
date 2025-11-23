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
                                    style="padding: 0.5rem 1rem; background: <?= $runningOnly ? '#2d3748' : '#3b82f6' ?>; color: <?= $runningOnly ? '#9ca3af' : 'white' ?>; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
                                All Activities
                            </button>
                            <button id="runningOnlyBtn" onclick="switchHeatmapMode('running')"
                                    style="padding: 0.5rem 1rem; background: <?= $runningOnly ? '#3b82f6' : '#2d3748' ?>; color: <?= $runningOnly ? 'white' : '#9ca3af' ?>; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; transition: all 0.2s;">
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
                <!-- Unit Toggle -->
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <button id="milesBtn" onclick="switchUnits('miles')" style="padding: 0.5rem 1rem; background: #fc4c02; color: white; border: 2px solid #fc4c02; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        Miles
                    </button>
                    <button id="kmBtn" onclick="switchUnits('km')" style="padding: 0.5rem 1rem; background: white; color: #666; border: 2px solid #e2e8f0; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                        Kilometers
                    </button>
                </div>

                <!-- Running Summary Stats -->
                <div style="margin-top: 1rem;">
                    <h4 style="margin-bottom: 0.75rem; text-align: center;">🏃 Running Summary</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                        <!-- Total Runs -->
                        <div style="padding: 1rem; background: white; border-radius: 8px; border: 2px solid #fc4c02;">
                            <div style="font-size: 0.75rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;">
                                Total Runs
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #fc4c02;">
                                <?= $totalRuns ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                <?= $totalRuns === 1 ? 'run' : 'runs' ?> completed
                            </div>
                        </div>

                        <!-- Total Distance -->
                        <div style="padding: 1rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <div style="font-size: 0.75rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;">
                                Total Distance
                            </div>
                            <div class="distance-value" data-meters="<?= $totalRunningDistance ?>" style="font-size: 2rem; font-weight: 700; color: #2d3748;">
                                <?= number_format($totalRunningDistance / 1609.34, 1) ?>
                            </div>
                            <div class="distance-unit" style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                miles
                            </div>
                        </div>

                        <!-- Average Pace -->
                        <div style="padding: 1rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <div style="font-size: 0.75rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;">
                                Average Pace
                            </div>
                            <div class="pace-value" data-min-per-mile="<?= $averagePace ?>" style="font-size: 2rem; font-weight: 700; color: #48bb78;">
                                <?php
                                $paceMinutes = floor($averagePace);
                                $paceSeconds = round(($averagePace - $paceMinutes) * 60);
                                echo $paceMinutes . ':' . str_pad($paceSeconds, 2, '0', STR_PAD_LEFT);
                                ?>
                            </div>
                            <div class="pace-unit" style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                min/mile
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Records Section -->
                <div style="margin-top: 1.25rem;">
                    <h4 style="margin-bottom: 0.75rem; text-align: center;">🏆 Personal Records</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
                        <!-- Fastest Pace -->
                        <?php if ($fastestPace > 0): ?>
                            <div style="padding: 1rem; background: white; border-radius: 8px; border: 2px solid #f59e0b;">
                                <div style="font-size: 0.75rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;">
                                    ⚡ Fastest Pace
                                </div>
                                <div class="pace-value" data-min-per-mile="<?= $fastestPace ?>" style="font-size: 1.75rem; font-weight: 700; color: #f59e0b;">
                                    <?php
                                    $paceMinutes = floor($fastestPace);
                                    $paceSeconds = round(($fastestPace - $paceMinutes) * 60);
                                    echo $paceMinutes . ':' . str_pad($paceSeconds, 2, '0', STR_PAD_LEFT);
                                    ?>
                                </div>
                                <div class="pace-unit" style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                    min/mile
                                </div>
                                <?php if ($fastestPaceDate): ?>
                                    <div style="font-size: 0.7rem; color: #999; margin-top: 0.375rem;">
                                        <?= $fastestPaceDate->format('M j, Y') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Longest Run -->
                        <?php if ($longestRunDistance > 0): ?>
                            <div style="padding: 1rem; background: white; border-radius: 8px; border: 2px solid #8b5cf6;">
                                <div style="font-size: 0.75rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;">
                                    📏 Longest Run
                                </div>
                                <div class="distance-value" data-meters="<?= $longestRunDistance ?>" style="font-size: 1.75rem; font-weight: 700; color: #8b5cf6;">
                                    <?= number_format($longestRunDistance / 1609.34, 1) ?>
                                </div>
                                <div class="distance-unit" style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                    miles
                                </div>
                                <?php if ($longestRunDate): ?>
                                    <div style="font-size: 0.7rem; color: #999; margin-top: 0.375rem;">
                                        <?= $longestRunDate->format('M j, Y') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Runs Over 10K -->
                        <div style="padding: 1rem; background: white; border-radius: 8px; border: 2px solid #10b981;">
                            <div style="font-size: 0.75rem; color: #666; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem;">
                                🎯 Runs Over 10K
                            </div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #10b981;">
                                <?= $runsOver10K ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">
                                <?= $runsOver10K === 1 ? 'achievement' : 'achievements' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distance Distribution Histogram -->
                <?php if (!empty($distanceBins)): ?>
                    <div style="margin-top: 2rem;">
                        <h4 style="margin-bottom: 1rem; text-align: center;">📊 Distance Distribution</h4>
                        <div style="padding: 1.5rem; background: white; border-radius: 8px; border: 2px solid #e2e8f0;">
                            <canvas id="distanceHistogram" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                <?php endif; ?>
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
            if (mode === 'all') {
                // Reload page without running_only parameter, stay on heatmap tab
                window.location.href = '/dashboard#heatmap';
            } else if (mode === 'running') {
                // Reload page with running_only parameter, stay on heatmap tab
                window.location.href = '/dashboard?running_only=true#heatmap';
            }
        }

        // On page load, check if there's a hash and switch to that tab
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1); // Remove the #
            if (hash && ['overview', 'duration', 'heatmap', 'running'].includes(hash)) {
                switchTab(hash);
            }

            // Initialize distance histogram if it exists
            const histogramCanvas = document.getElementById('distanceHistogram');
            if (histogramCanvas) {
                const distanceBins = <?= json_encode($distanceBins ?? []) ?>;

                // Prepare data for Chart.js
                const labels = [];
                const data = [];

                // Find max bin to ensure continuous range
                const maxBin = Math.max(...Object.keys(distanceBins).map(k => parseInt(k)));

                for (let i = 0; i <= maxBin; i++) {
                    labels.push(i + '-' + (i + 1) + ' mi');
                    data.push(distanceBins[i] || 0);
                }

                new Chart(histogramCanvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Number of Runs',
                            data: data,
                            backgroundColor: '#fc4c02',
                            borderColor: '#fc4c02',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Runs'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Distance Range'
                                }
                            }
                        }
                    }
                });
            }
        });

        // Unit conversion functionality
        let currentUnit = 'miles';

        function switchUnits(unit) {
            if (currentUnit === unit) return;

            currentUnit = unit;

            // Update button styles
            const milesBtn = document.getElementById('milesBtn');
            const kmBtn = document.getElementById('kmBtn');

            if (unit === 'miles') {
                milesBtn.style.background = '#fc4c02';
                milesBtn.style.color = 'white';
                milesBtn.style.borderColor = '#fc4c02';
                kmBtn.style.background = 'white';
                kmBtn.style.color = '#666';
                kmBtn.style.borderColor = '#e2e8f0';
            } else {
                kmBtn.style.background = '#fc4c02';
                kmBtn.style.color = 'white';
                kmBtn.style.borderColor = '#fc4c02';
                milesBtn.style.background = 'white';
                milesBtn.style.color = '#666';
                milesBtn.style.borderColor = '#e2e8f0';
            }

            // Convert all distances
            document.querySelectorAll('.distance-value').forEach(elem => {
                const meters = parseFloat(elem.getAttribute('data-meters'));
                if (unit === 'miles') {
                    const miles = meters / 1609.34;
                    elem.textContent = miles.toFixed(1);
                } else {
                    const km = meters / 1000;
                    elem.textContent = km.toFixed(1);
                }
            });

            // Update distance units
            document.querySelectorAll('.distance-unit').forEach(elem => {
                elem.textContent = unit === 'miles' ? 'miles' : 'kilometers';
            });

            // Convert all paces
            document.querySelectorAll('.pace-value').forEach(elem => {
                const minPerMile = parseFloat(elem.getAttribute('data-min-per-mile'));
                let paceValue;

                if (unit === 'miles') {
                    paceValue = minPerMile;
                } else {
                    // Convert min/mile to min/km: min/mile * 0.621371 = min/km
                    paceValue = minPerMile * 0.621371;
                }

                const minutes = Math.floor(paceValue);
                const seconds = Math.round((paceValue - minutes) * 60);
                elem.textContent = minutes + ':' + String(seconds).padStart(2, '0');
            });

            // Update pace units
            document.querySelectorAll('.pace-unit').forEach(elem => {
                elem.textContent = unit === 'miles' ? 'min/mile' : 'min/km';
            });

            // Update histogram if it exists
            updateHistogram(unit);
        }

        function updateHistogram(unit) {
            const canvas = document.getElementById('distanceHistogram');
            if (!canvas || !canvas.chart) return;

            const chart = canvas.chart;
            const distanceBins = <?= json_encode($distanceBins ?? []) ?>;

            // Prepare data based on unit
            const labels = [];
            const data = [];

            if (unit === 'miles') {
                const maxBin = Math.max(...Object.keys(distanceBins).map(k => parseInt(k)));
                for (let i = 0; i <= maxBin; i++) {
                    labels.push(i + '-' + (i + 1) + ' mi');
                    data.push(distanceBins[i] || 0);
                }
            } else {
                // Convert bins to km (1 mile = 1.60934 km)
                const kmBins = {};
                for (const [mileBin, count] of Object.entries(distanceBins)) {
                    const kmBin = Math.floor(parseFloat(mileBin) * 1.60934);
                    kmBins[kmBin] = (kmBins[kmBin] || 0) + count;
                }

                const maxBin = Math.max(...Object.keys(kmBins).map(k => parseInt(k)));
                for (let i = 0; i <= maxBin; i++) {
                    labels.push(i + '-' + (i + 1) + ' km');
                    data.push(kmBins[i] || 0);
                }
            }

            chart.data.labels = labels;
            chart.data.datasets[0].data = data;
            chart.options.scales.x.title.text = 'Distance Range';
            chart.update();
        }

        // Store chart instance for later access
        window.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('distanceHistogram');
            if (canvas && window.Chart) {
                // Wait a bit for the chart to be created
                setTimeout(() => {
                    const chart = Chart.getChart(canvas);
                    if (chart) {
                        canvas.chart = chart;
                    }
                }, 100);
            }
        });
    </script>
</div>
