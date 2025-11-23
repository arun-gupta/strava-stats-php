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

        // Calculate streak statistics
        $currentStreak = 0;
        $longestStreak = 0;
        $totalActiveDays = 0;

        if (count($activities) > 0) {
            // Group activities by date (Y-m-d format)
            $activityDates = [];
            foreach ($activities as $activity) {
                $dateStr = $activity->startDate->format('Y-m-d');
                $activityDates[$dateStr] = true;
            }
            $totalActiveDays = count($activityDates);

            // Sort dates
            $sortedDates = array_keys($activityDates);
            sort($sortedDates);

            // Calculate current streak (working backwards from today)
            $today = new DateTime();
            $checkDate = clone $today;
            $currentStreak = 0;

            // Check if today or yesterday has activity (current streak is still active)
            $todayStr = $today->format('Y-m-d');
            $yesterdayStr = $today->modify('-1 day')->format('Y-m-d');

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

            // Calculate longest streak
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
                    $tempStreak = 1;
                }
            }
        }

        // Calculate rest days
        if (!isset($endDate)) {
            $endDate = new DateTime();
            $startDate = (new DateTime())->modify('-7 days');
        }
        $totalDays = $endDate->diff($startDate)->days + 1;
        $restDays = $totalDays - $totalActiveDays;

        // Group activities by date for calendar display
        $activitiesByDate = [];
        foreach ($activities as $activity) {
            $dateStr = $activity->startDate->format('Y-m-d');
            if (!isset($activitiesByDate[$dateStr])) {
                $activitiesByDate[$dateStr] = [];
            }
            $activitiesByDate[$dateStr][] = $activity;
        }

        // Generate calendar days (last 7 days)
        $calendarDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = (clone $endDate)->modify("-{$i} days");
            $dateStr = $date->format('Y-m-d');
            $calendarDays[] = [
                'date' => $date,
                'dateStr' => $dateStr,
                'hasActivity' => isset($activitiesByDate[$dateStr]),
                'activityCount' => isset($activitiesByDate[$dateStr]) ? count($activitiesByDate[$dateStr]) : 0,
            ];
        }

        // Calculate date range (last 7 days)
        if (!isset($endDate)) {
            $endDate = new DateTime();
            $startDate = (new DateTime())->modify('-7 days');
        }

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
        ]);

        $response->getBody()->write($html);
        return $response;
    })->add(new AuthMiddleware());
};
