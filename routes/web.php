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

        // Check if running-only mode is enabled
        $queryParams = $request->getQueryParams();
        $runningOnly = isset($queryParams['running_only']) && $queryParams['running_only'] === 'true';

        $accessToken = $_SESSION['access_token'] ?? null;
        $activities = [];
        $activityCounts = [];
        $movingTimeByType = [];
        $error = null;

        // Fetch activities if we have a token
        if ($accessToken) {
            try {
                $activityService = new ActivityService();
                $activities = $activityService->fetchRecentActivities($accessToken);

                $activityCounts = $activityService->getCountsByType($activities);
                $movingTimeByType = $activityService->getMovingTimeByType($activities);
            } catch (\Exception $e) {
                // Log the error
                \App\Services\Logger::error('Failed to fetch activities', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Set user-friendly error message
                $error = 'Unable to load activities from Strava. Please try again.';
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
            // Calculate total seconds in the time range
            $endDate = new DateTime();
            $startDate = (new DateTime())->modify('-7 days');
            $daysDiff = $endDate->diff($startDate)->days;
            $weeks = max(1, $daysDiff / 7); // At least 1 week
            $weeklyAverage = $totalMovingTime / $weeks;
        } else {
            $endDate = new DateTime();
            $startDate = (new DateTime())->modify('-7 days');
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

        // Calculate date range for the display window (last 7 days)
        if (!isset($endDate)) {
            $endDate = new DateTime();
            $startDate = (new DateTime())->modify('-6 days'); // -6 days + today = 7 days
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

        // Calculate rest days (dates are already set above)
        $totalDays = 7;
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

        // Generate calendar days (last 7 days) with intensity levels
        $calendarDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = (clone $endDate)->modify("-{$i} days");
            $dateStr = $date->format('Y-m-d');

            // Update startDate to match the first calendar day
            if ($i === 6) {
                $startDate = clone $date;
            }
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
        ]);

        $response->getBody()->write($html);
        return $response;
    })->add(new AuthMiddleware());
};
