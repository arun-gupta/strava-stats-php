<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use App\Services\ActivityService;
use App\Services\View;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    // Home page
    $app->get('/', function (Request $request, Response $response) {
        $html = View::render('pages/home', [
            'layout' => 'main',
            'title' => 'Strava Activity Analyzer - Home',
        ]);

        $response->getBody()->write($html);
        return $response;
    });

    // Error page
    $app->get('/error', function (Request $request, Response $response) {
        $html = View::render('pages/error', [
            'layout' => 'main',
            'title' => 'Error - Strava Activity Analyzer',
        ]);

        $response->getBody()->write($html);
        return $response;
    });

    // OAuth routes
    $authController = new AuthController();
    $app->get('/auth/strava', [$authController, 'authorize']);
    $app->get('/auth/callback', [$authController, 'callback']);
    $app->get('/signout', [$authController, 'signout']);

    // Test endpoint to debug Strava API
    $app->get('/test-strava', function (Request $request, Response $response) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $accessToken = $_SESSION['access_token'] ?? null;

        if (!$accessToken) {
            $response->getBody()->write('No access token in session');
            return $response;
        }

        // Make a raw curl request to Strava
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.strava.com/api/v3/athlete/activities?per_page=5');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $output = [
            'http_code' => $httpCode,
            'error' => $error ?: null,
            'token_prefix' => substr($accessToken, 0, 10) . '...',
            'response' => $result,
        ];

        $response->getBody()->write('<pre>' . htmlspecialchars(json_encode($output, JSON_PRETTY_PRINT)) . '</pre>');
        return $response;
    });

    // Dashboard (protected with middleware)
    $app->get('/dashboard', function (Request $request, Response $response) {
        // Start session to get access token
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check query parameters
        $queryParams = $request->getQueryParams();
        $runningOnly = isset($queryParams['running_only']) && $queryParams['running_only'] === 'true';

        // Handle date range parameters
        $endDate = new DateTime();
        $endDate->setTime(23, 59, 59); // Set to end of day
        $startDate = null;
        $periodLabel = 'Last 7 Days'; // Default label

        // Check if query params exist, otherwise try to restore from session
        $hasQueryParams = isset($queryParams['start']) || isset($queryParams['range']) || isset($queryParams['days']);

        if (isset($queryParams['start']) && isset($queryParams['end'])) {
            // Custom date range
            $startDate = new DateTime($queryParams['start']);
            $startDate->setTime(0, 0, 0);
            $endDate = new DateTime($queryParams['end']);
            $endDate->setTime(23, 59, 59);
            $periodLabel = 'Custom Range';

            // Store in session
            $_SESSION['date_range'] = [
                'type' => 'custom',
                'start' => $queryParams['start'],
                'end' => $queryParams['end']
            ];
        } elseif (isset($queryParams['range']) && $queryParams['range'] === 'ytd') {
            // Year to date
            $startDate = new DateTime(date('Y') . '-01-01');
            $startDate->setTime(0, 0, 0);
            $periodLabel = 'Year to Date';

            // Store in session
            $_SESSION['date_range'] = [
                'type' => 'ytd'
            ];
        } elseif (isset($queryParams['days'])) {
            // Preset days
            $days = (int)$queryParams['days'];
            // For "Last N days", we want N days including today
            // Clone endDate and go back (N-1) days to get N total days
            $startDate = (clone $endDate)->modify('-' . ($days - 1) . ' days');
            $startDate->setTime(0, 0, 0);
            if ($days == 30) {
                $periodLabel = 'Last 30 Days';
            } elseif ($days == 90) {
                $periodLabel = 'Last 90 Days';
            } elseif ($days == 180) {
                $periodLabel = 'Last 6 Months';
            } else {
                $periodLabel = 'Last ' . $days . ' Days';
            }

            // Store in session
            $_SESSION['date_range'] = [
                'type' => 'days',
                'days' => $days
            ];
        } elseif (!$hasQueryParams && isset($_SESSION['date_range'])) {
            // Restore from session if no query params
            $savedRange = $_SESSION['date_range'];

            if ($savedRange['type'] === 'custom') {
                $startDate = new DateTime($savedRange['start']);
                $startDate->setTime(0, 0, 0);
                $endDate = new DateTime($savedRange['end']);
                $endDate->setTime(23, 59, 59);
                $periodLabel = 'Custom Range';
            } elseif ($savedRange['type'] === 'ytd') {
                $startDate = new DateTime(date('Y') . '-01-01');
                $startDate->setTime(0, 0, 0);
                $periodLabel = 'Year to Date';
            } elseif ($savedRange['type'] === 'days') {
                $days = $savedRange['days'];
                $startDate = (clone $endDate)->modify('-' . ($days - 1) . ' days');
                $startDate->setTime(0, 0, 0);
                if ($days == 30) {
                    $periodLabel = 'Last 30 Days';
                } elseif ($days == 90) {
                    $periodLabel = 'Last 90 Days';
                } elseif ($days == 180) {
                    $periodLabel = 'Last 6 Months';
                } else {
                    $periodLabel = 'Last ' . $days . ' Days';
                }
            }
        } else {
            // Default: last 7 days (6 days ago + today = 7 days)
            $startDate = (clone $endDate)->modify('-6 days');
            $startDate->setTime(0, 0, 0);

            // Store default in session
            $_SESSION['date_range'] = [
                'type' => 'days',
                'days' => 7
            ];
        }

        $accessToken = $_SESSION['access_token'] ?? null;
        $activities = [];
        $activityCounts = [];
        $movingTimeByType = [];
        $error = null;

        // Fetch activities if we have a token
        if ($accessToken) {
            try {
                $activityService = new ActivityService();

                // Check if we have cached activities and if the cache is still valid
                $cacheKey = 'activities_cache';
                $cacheStartDateKey = 'activities_cache_start_date';
                $needsRefetch = false;

                if (!isset($_SESSION[$cacheKey]) || !isset($_SESSION[$cacheStartDateKey])) {
                    // No cache exists
                    $needsRefetch = true;
                } else {
                    // Check if cached start date is earlier than or equal to current start date
                    $cachedStartDate = new DateTime($_SESSION[$cacheStartDateKey]);
                    if ($cachedStartDate > $startDate) {
                        // Cached data doesn't go back far enough
                        $needsRefetch = true;
                    }
                }

                if ($needsRefetch) {
                    // Fetch activities from the start date of the selected range
                    $allActivities = $activityService->fetchActivities($accessToken, $startDate);

                    // Cache the activities and start date
                    $_SESSION[$cacheKey] = $allActivities;
                    $_SESSION[$cacheStartDateKey] = $startDate->format('Y-m-d');
                } else {
                    // Use cached activities
                    $allActivities = $_SESSION[$cacheKey];
                }

                // Filter activities by date range
                $activities = array_filter($allActivities, function($activity) use ($startDate, $endDate) {
                    $activityDate = $activity->startDate;
                    $startDateStr = $startDate->format('Y-m-d');
                    $endDateStr = $endDate->format('Y-m-d');
                    $activityDateStr = $activityDate->format('Y-m-d');

                    return $activityDateStr >= $startDateStr && $activityDateStr <= $endDateStr;
                });
                $activities = array_values($activities); // Re-index array

                $activityCounts = $activityService->getCountsByType($activities);
                $movingTimeByType = $activityService->getMovingTimeByType($activities);
            } catch (\Exception $e) {
                // Log the error
                \App\Services\Logger::error('Failed to fetch activities', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Provide helpful error message based on error type
                if (strpos($e->getMessage(), '401') !== false) {
                    $error = 'Your session has expired. Please sign out and sign in again to reconnect with Strava.';
                } elseif (strpos($e->getMessage(), '429') !== false) {
                    $error = 'Strava API rate limit reached. Please wait a few minutes and try again.';
                } elseif (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'timed out') !== false) {
                    $error = 'Request timed out. Strava may be slow to respond. Please try again in a moment.';
                } elseif (strpos($e->getMessage(), 'Failed to connect') !== false || strpos($e->getMessage(), 'network') !== false) {
                    $error = 'Unable to connect to Strava. Please check your internet connection and try again.';
                } else {
                    $error = 'Unable to load activities from Strava. Please refresh the page to try again.';
                }
            }
        }

        // Calculate total moving time
        $totalMovingTime = 0;
        foreach ($activities as $activity) {
            $totalMovingTime += $activity->movingTime;
        }

        // Calculate insights
        $longestDurationType = '';
        $longestDuration = 0;
        foreach ($movingTimeByType as $type => $seconds) {
            if ($seconds > $longestDuration) {
                $longestDuration = $seconds;
                $longestDurationType = $type;
            }
        }

        // Calculate weekly average
        $weeklyAverage = 0;
        if (count($activities) > 0) {
            // Calculate total seconds in the time range (use already calculated startDate and endDate)
            $daysDiff = $endDate->diff($startDate)->days;
            $weeks = max(1, $daysDiff / 7); // At least 1 week
            $weeklyAverage = $totalMovingTime / $weeks;
        }

        // Find most active day of week
        $dayOfWeekCounts = [];
        foreach ($activities as $activity) {
            $dayName = $activity->startDate->format('l'); // "Monday", "Tuesday", etc.
            if (!isset($dayOfWeekCounts[$dayName])) {
                $dayOfWeekCounts[$dayName] = 0;
            }
            $dayOfWeekCounts[$dayName]++;
        }
        $mostActiveDay = '';
        if (count($dayOfWeekCounts) > 0) {
            arsort($dayOfWeekCounts);
            $mostActiveDay = array_key_first($dayOfWeekCounts);
        }

        // Filter activities for heatmap if running-only mode is enabled
        // This only affects the heatmap calculations, not other tabs
        $heatmapActivities = $activities;
        if ($runningOnly) {
            $heatmapActivities = array_filter($activities, function($activity) {
                return $activity->type === 'Run';
            });
            $heatmapActivities = array_values($heatmapActivities); // Re-index array
        }

        // Calculate streak statistics (using heatmap-filtered activities)
        $currentStreak = 0;
        $longestStreak = 0;
        $totalActiveDays = 0;
        $daysSinceLastActivity = 0;
        $longestGap = 0;
        $totalGapDays = 0;

        if (count($heatmapActivities) > 0) {
            // Group activities by date (Y-m-d format) within the 7-day window
            $activityDates = [];
            $startDateStr = $startDate->format('Y-m-d');
            $endDateStr = $endDate->format('Y-m-d');

            foreach ($heatmapActivities as $activity) {
                $dateStr = $activity->startDate->format('Y-m-d');

                // Only count activities within our 7-day display window (compare date strings, not timestamps)
                if ($dateStr >= $startDateStr && $dateStr <= $endDateStr) {
                    $activityDates[$dateStr] = true;
                }
            }
            $totalActiveDays = count($activityDates);

            // Sort dates
            $sortedDates = array_keys($activityDates);
            sort($sortedDates);

            // Calculate days since last activity
            $today = new DateTime();
            $lastActivityDate = new DateTime(end($sortedDates));
            $daysSinceLastActivity = $today->diff($lastActivityDate)->days;

            // Calculate current streak (working backwards from today)
            $checkDate = clone $today;
            $currentStreak = 0;

            // Check if today or yesterday has activity (current streak is still active)
            $todayStr = $today->format('Y-m-d');
            $yesterdayStr = (clone $today)->modify('-1 day')->format('Y-m-d');

            if (isset($activityDates[$todayStr]) || isset($activityDates[$yesterdayStr])) {
                // Start from the most recent activity date
                $checkDate = isset($activityDates[$todayStr]) ? new DateTime($todayStr) : new DateTime($yesterdayStr);

                while (true) {
                    $checkStr = $checkDate->format('Y-m-d');
                    if (isset($activityDates[$checkStr])) {
                        $currentStreak++;
                        $checkDate->modify('-1 day');
                    } else {
                        break;
                    }
                }
            }

            // Calculate longest streak and gaps
            $tempStreak = 1;
            $longestStreak = 1;

            for ($i = 1; $i < count($sortedDates); $i++) {
                $prevDate = new DateTime($sortedDates[$i - 1]);
                $currDate = new DateTime($sortedDates[$i]);
                $diff = $prevDate->diff($currDate)->days;

                if ($diff === 1) {
                    $tempStreak++;
                    $longestStreak = max($longestStreak, $tempStreak);
                } else {
                    // Found a gap
                    $gapDays = $diff - 1; // Subtract 1 because diff includes both endpoints
                    $longestGap = max($longestGap, $gapDays);
                    $totalGapDays += $gapDays;
                    $tempStreak = 1;
                }
            }
        }

        // Calculate rest days (use dynamic date range)
        $totalDays = (int)$endDate->diff($startDate)->days + 1; // +1 to include both start and end dates
        $restDays = $totalDays - $totalActiveDays;

        // Filter running activities and calculate running stats
        $runningActivities = array_filter($activities, function($activity) {
            return $activity->type === 'Run';
        });

        $totalRuns = count($runningActivities);
        $totalRunningDistance = 0;
        $totalRunningTime = 0;

        foreach ($runningActivities as $run) {
            $totalRunningDistance += $run->distance;
            $totalRunningTime += $run->movingTime;
        }

        // Calculate average pace (min/mile)
        $averagePace = 0;
        if ($totalRunningDistance > 0) {
            // Convert meters to miles: 1 mile = 1609.34 meters
            $totalMiles = $totalRunningDistance / 1609.34;
            // Pace in minutes per mile
            $averagePace = ($totalRunningTime / 60) / $totalMiles;
        }

        // Calculate Personal Records (PRs)
        $fastestPace = 0;
        $fastestPaceDate = null;
        $longestRunDistance = 0;
        $longestRunDate = null;
        $runsOver10K = 0;

        foreach ($runningActivities as $run) {
            // Fastest pace
            if ($run->distance > 0) {
                $runMiles = $run->distance / 1609.34;
                $runPace = ($run->movingTime / 60) / $runMiles; // min/mile

                if ($fastestPace === 0 || $runPace < $fastestPace) {
                    $fastestPace = $runPace;
                    $fastestPaceDate = $run->startDate;
                }
            }

            // Longest run
            if ($run->distance > $longestRunDistance) {
                $longestRunDistance = $run->distance;
                $longestRunDate = $run->startDate;
            }

            // Runs over 10K (10,000 meters = 10K)
            if ($run->distance >= 10000) {
                $runsOver10K++;
            }
        }

        // Calculate distance distribution by 1-mile bins for histogram
        $distanceBins = [];
        foreach ($runningActivities as $run) {
            $miles = $run->distance / 1609.34;
            $bin = floor($miles); // 0-1, 1-2, 2-3, etc.

            if (!isset($distanceBins[$bin])) {
                $distanceBins[$bin] = 0;
            }
            $distanceBins[$bin]++;
        }

        // Sort bins by distance
        ksort($distanceBins);

        // Group activities by date for calendar display (use heatmap-filtered activities)
        $activitiesByDate = [];
        foreach ($heatmapActivities as $activity) {
            $dateStr = $activity->startDate->format('Y-m-d');
            if (!isset($activitiesByDate[$dateStr])) {
                $activitiesByDate[$dateStr] = [];
            }
            $activitiesByDate[$dateStr][] = $activity;
        }

        // Calculate time spent per day
        $timeByDate = [];
        foreach ($activitiesByDate as $dateStr => $dayActivities) {
            $totalSeconds = 0;
            foreach ($dayActivities as $activity) {
                $totalSeconds += $activity->movingTime;
            }
            $timeByDate[$dateStr] = $totalSeconds;
        }

        // Calculate distance per day for trend chart (all activities, not heatmap-filtered)
        $distanceByDate = [];
        foreach ($activities as $activity) {
            $dateStr = $activity->startDate->format('Y-m-d');
            if (!isset($distanceByDate[$dateStr])) {
                $distanceByDate[$dateStr] = 0;
            }
            $distanceByDate[$dateStr] += $activity->distance;
        }

        // Calculate average pace per day for running activities
        $paceByDate = [];
        foreach ($activities as $activity) {
            if ($activity->type === 'Run' && $activity->distance > 0) {
                $dateStr = $activity->startDate->format('Y-m-d');
                if (!isset($paceByDate[$dateStr])) {
                    $paceByDate[$dateStr] = [
                        'totalTime' => 0,
                        'totalDistance' => 0,
                    ];
                }
                $paceByDate[$dateStr]['totalTime'] += $activity->movingTime;
                $paceByDate[$dateStr]['totalDistance'] += $activity->distance;
            }
        }

        // Convert to average pace (min/mile) for each day
        $averagePaceByDate = [];
        foreach ($paceByDate as $dateStr => $data) {
            if ($data['totalDistance'] > 0) {
                $miles = $data['totalDistance'] / 1609.34;
                $minutes = $data['totalTime'] / 60;
                $averagePaceByDate[$dateStr] = $minutes / $miles; // min/mile
            }
        }

        // Calculate trend insights for distance
        $distanceTrendInsight = '';
        $distanceValues = array_values($distanceByDate);
        if (count($distanceValues) >= 2) {
            $firstHalf = array_slice($distanceValues, 0, (int)(count($distanceValues) / 2));
            $secondHalf = array_slice($distanceValues, (int)(count($distanceValues) / 2));

            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);

            $percentChange = (($secondAvg - $firstAvg) / $firstAvg) * 100;

            if (abs($percentChange) < 5) {
                $distanceTrendInsight = 'Distance staying steady';
            } elseif ($percentChange > 0) {
                $distanceTrendInsight = 'Distance increasing by ' . round(abs($percentChange)) . '%';
            } else {
                $distanceTrendInsight = 'Distance decreasing by ' . round(abs($percentChange)) . '%';
            }
        }

        // Calculate trend insights for pace
        $paceTrendInsight = '';
        $paceValues = array_values($averagePaceByDate);
        if (count($paceValues) >= 2) {
            $firstHalf = array_slice($paceValues, 0, (int)(count($paceValues) / 2));
            $secondHalf = array_slice($paceValues, (int)(count($paceValues) / 2));

            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);

            $percentChange = (($secondAvg - $firstAvg) / $firstAvg) * 100;

            if (abs($percentChange) < 3) {
                $paceTrendInsight = 'Pace staying consistent';
            } elseif ($percentChange < 0) {
                // Negative change = faster pace = improvement
                $paceTrendInsight = 'Pace improving by ' . round(abs($percentChange)) . '%';
            } else {
                $paceTrendInsight = 'Pace slowing by ' . round(abs($percentChange)) . '%';
            }
        }

        // Generate calendar days for the selected date range with intensity levels
        $calendarDays = [];
        $daysDiff = (int)$endDate->diff($startDate)->days;
        for ($i = $daysDiff; $i >= 0; $i--) {
            $date = (clone $endDate)->modify("-{$i} days");
            $dateStr = $date->format('Y-m-d');
            $hasActivity = isset($activitiesByDate[$dateStr]);
            $timeSpent = $timeByDate[$dateStr] ?? 0;

            // Determine intensity level based on time spent
            $intensity = 'none'; // No activity
            $color = '#2d3748'; // Dark gray

            if ($timeSpent > 0) {
                $hours = $timeSpent / 3600;
                if ($hours < 1) {
                    $intensity = 'light';
                    $color = '#fbbf24'; // Yellow
                } elseif ($hours < 2) {
                    $intensity = 'medium';
                    $color = '#f97316'; // Orange
                } else {
                    $intensity = 'heavy';
                    $color = '#fc4c02'; // Strava orange
                }
            }

            // Get activity details for this day
            $dayActivitiesDetails = [];
            if (isset($activitiesByDate[$dateStr])) {
                foreach ($activitiesByDate[$dateStr] as $activity) {
                    $dayActivitiesDetails[] = [
                        'name' => $activity->name,
                        'type' => $activity->type,
                        'distance' => $activity->distance,
                        'movingTime' => $activity->movingTime,
                    ];
                }
            }

            $calendarDays[] = [
                'date' => $date,
                'dateStr' => $dateStr,
                'hasActivity' => $hasActivity,
                'activityCount' => isset($activitiesByDate[$dateStr]) ? count($activitiesByDate[$dateStr]) : 0,
                'timeSpent' => $timeSpent,
                'intensity' => $intensity,
                'color' => $color,
                'activities' => $dayActivitiesDetails,
            ];
        }

        // Date range already set above

        $html = View::render('pages/dashboard', [
            'layout' => 'main',
            'title' => 'Dashboard - Strava Activity Analyzer',
            'activities' => $activities,
            'activityCounts' => $activityCounts,
            'movingTimeByType' => $movingTimeByType,
            'totalActivities' => count($activities),
            'totalMovingTime' => $totalMovingTime,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'error' => $error,
            'longestDurationType' => $longestDurationType,
            'weeklyAverage' => $weeklyAverage,
            'mostActiveDay' => $mostActiveDay,
            'currentStreak' => $currentStreak,
            'longestStreak' => $longestStreak,
            'totalActiveDays' => $totalActiveDays,
            'restDays' => $restDays,
            'calendarDays' => $calendarDays,
            'daysSinceLastActivity' => $daysSinceLastActivity,
            'longestGap' => $longestGap,
            'totalGapDays' => $totalGapDays,
            'totalRuns' => $totalRuns,
            'totalRunningDistance' => $totalRunningDistance,
            'averagePace' => $averagePace,
            'runningOnly' => $runningOnly,
            'fastestPace' => $fastestPace,
            'fastestPaceDate' => $fastestPaceDate,
            'longestRunDistance' => $longestRunDistance,
            'longestRunDate' => $longestRunDate,
            'runsOver10K' => $runsOver10K,
            'distanceBins' => $distanceBins,
            'distanceByDate' => $distanceByDate,
            'averagePaceByDate' => $averagePaceByDate,
            'distanceTrendInsight' => $distanceTrendInsight,
            'paceTrendInsight' => $paceTrendInsight,
            'periodLabel' => $periodLabel,
            'savedDateRange' => $_SESSION['date_range'] ?? null,
        ]);

        $response->getBody()->write($html);
        return $response;
    })->add(new AuthMiddleware());
};
