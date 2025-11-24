<?php

declare(strict_types=1);

/**
 * Performance Profiling Script
 *
 * Measures execution time of various operations in the dashboard
 * to identify bottlenecks and optimization opportunities.
 */

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Services\StravaClient;
use App\Services\ActivityService;
use App\Services\HeatmapService;
use App\Services\UnitsService;
use App\Services\Logger;

class PerformanceProfiler
{
    private array $timings = [];
    private array $memoryUsage = [];

    public function startTimer(string $name): void
    {
        $this->timings[$name] = ['start' => microtime(true)];
        $this->memoryUsage[$name] = ['start' => memory_get_usage(true)];
    }

    public function endTimer(string $name): float
    {
        if (!isset($this->timings[$name]['start'])) {
            throw new Exception("Timer '$name' was not started");
        }

        $elapsed = microtime(true) - $this->timings[$name]['start'];
        $this->timings[$name]['elapsed'] = $elapsed;

        $memoryUsed = memory_get_usage(true) - $this->memoryUsage[$name]['start'];
        $this->memoryUsage[$name]['used'] = $memoryUsed;

        return $elapsed;
    }

    public function getTimings(): array
    {
        return $this->timings;
    }

    public function getMemoryUsage(): array
    {
        return $this->memoryUsage;
    }

    public function printReport(): void
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "PERFORMANCE PROFILING REPORT\n";
        echo str_repeat("=", 80) . "\n\n";

        echo "EXECUTION TIMES:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-40s %15s %15s\n", "Operation", "Time (ms)", "Memory (KB)");
        echo str_repeat("-", 80) . "\n";

        $totalTime = 0;
        foreach ($this->timings as $name => $timing) {
            if (isset($timing['elapsed'])) {
                $timeMs = $timing['elapsed'] * 1000;
                $memoryKb = ($this->memoryUsage[$name]['used'] ?? 0) / 1024;
                printf("%-40s %15.2f %15.2f\n", $name, $timeMs, $memoryKb);
                $totalTime += $timing['elapsed'];
            }
        }

        echo str_repeat("-", 80) . "\n";
        printf("%-40s %15.2f\n\n", "TOTAL TIME", $totalTime * 1000);

        echo "MEMORY USAGE:\n";
        echo str_repeat("-", 80) . "\n";
        printf("Peak Memory Usage: %.2f MB\n", memory_get_peak_usage(true) / 1024 / 1024);
        printf("Current Memory Usage: %.2f MB\n", memory_get_usage(true) / 1024 / 1024);
        echo str_repeat("-", 80) . "\n\n";

        // Performance targets
        echo "PERFORMANCE TARGETS:\n";
        echo str_repeat("-", 80) . "\n";
        $loadTime = $totalTime * 1000;
        $loadTarget = 2000; // 2 seconds
        $loadStatus = $loadTime < $loadTarget ? "✓ PASS" : "✗ FAIL";
        printf("Dashboard Load Time: %.2f ms (Target: < %d ms) %s\n", $loadTime, $loadTarget, $loadStatus);
        echo str_repeat("=", 80) . "\n\n";
    }
}

// Simulate dashboard load scenario
function profileDashboardLoad(string $accessToken, PerformanceProfiler $profiler): void
{
    echo "Profiling dashboard load...\n\n";

    // 1. Fetch activities from Strava API
    $profiler->startTimer('Strava API: Fetch athlete profile');
    $stravaClient = new StravaClient();
    $athlete = $stravaClient->getAthlete($accessToken);
    $profiler->endTimer('Strava API: Fetch athlete profile');

    if (!$athlete) {
        echo "Error: Could not fetch athlete profile\n";
        return;
    }

    // 2. Fetch activities (simulate fetching for 30 days)
    $profiler->startTimer('Strava API: Fetch activities (page 1)');
    $activitiesPage1 = $stravaClient->getActivities($accessToken, 1, 50);
    $profiler->endTimer('Strava API: Fetch activities (page 1)');

    if (!$activitiesPage1) {
        echo "Error: Could not fetch activities\n";
        return;
    }

    $allActivities = $activitiesPage1;

    // Fetch additional pages if needed (simulate realistic scenario)
    if (count($activitiesPage1) === 50) {
        $profiler->startTimer('Strava API: Fetch activities (page 2)');
        $activitiesPage2 = $stravaClient->getActivities($accessToken, 2, 50);
        $profiler->endTimer('Strava API: Fetch activities (page 2)');
        if ($activitiesPage2) {
            $allActivities = array_merge($allActivities, $activitiesPage2);
        }
    }

    echo "Fetched " . count($allActivities) . " activities\n";

    // 3. Parse activities
    $profiler->startTimer('Parse activities into models');
    $activities = ActivityService::parseActivities($allActivities);
    $profiler->endTimer('Parse activities into models');

    // 4. Filter by date range (30 days)
    $profiler->startTimer('Filter activities by date range');
    $endDate = new DateTime();
    $startDate = (clone $endDate)->modify('-30 days');
    $filteredActivities = ActivityService::filterActivitiesByDateRange($activities, $startDate, $endDate);
    $profiler->endTimer('Filter activities by date range');

    echo "Filtered to " . count($filteredActivities) . " activities in last 30 days\n";

    // 5. Calculate overview stats
    $profiler->startTimer('Calculate activity counts by type');
    $activityCounts = ActivityService::getActivityCountsByType($filteredActivities);
    $profiler->endTimer('Calculate activity counts by type');

    // 6. Calculate duration stats
    $profiler->startTimer('Calculate duration by type');
    $durationByType = ActivityService::getDurationByType($filteredActivities);
    $profiler->endTimer('Calculate duration by type');

    // 7. Generate heatmap
    $profiler->startTimer('Generate heatmap calendar');
    $heatmapData = HeatmapService::generateHeatmapData($filteredActivities, $startDate, $endDate);
    $profiler->endTimer('Generate heatmap calendar');

    // 8. Calculate workout statistics
    $profiler->startTimer('Calculate workout statistics');
    $workoutStats = HeatmapService::calculateWorkoutStatistics($filteredActivities, $startDate, $endDate);
    $profiler->endTimer('Calculate workout statistics');

    // 9. Calculate running stats
    $profiler->startTimer('Filter running activities');
    $runningActivities = array_filter($filteredActivities, function($activity) {
        return $activity->type === 'Run';
    });
    $profiler->endTimer('Filter running activities');

    if (count($runningActivities) > 0) {
        $profiler->startTimer('Calculate running statistics');
        $totalRuns = count($runningActivities);
        $totalDistance = array_sum(array_map(fn($a) => $a->distance, $runningActivities));
        $avgPace = ActivityService::calculateAveragePace($runningActivities);
        $fastestPace = ActivityService::getFastestPace($runningActivities);
        $longestRun = ActivityService::getLongestRun($runningActivities);
        $profiler->endTimer('Calculate running statistics');

        $profiler->startTimer('Generate distance histogram');
        $histogram = ActivityService::generateDistanceHistogram($runningActivities, 'mi');
        $profiler->endTimer('Generate distance histogram');
    }

    // 10. Calculate trends
    $profiler->startTimer('Calculate daily distance trends');
    $distanceTrend = ActivityService::getDailyDistanceTrend($filteredActivities, $startDate, $endDate);
    $profiler->endTimer('Calculate daily distance trends');

    if (count($runningActivities) > 0) {
        $profiler->startTimer('Calculate daily pace trends');
        $paceTrend = ActivityService::getDailyPaceTrend($runningActivities, $startDate, $endDate);
        $profiler->endTimer('Calculate daily pace trends');
    }

    // 11. Session storage simulation
    $profiler->startTimer('Serialize data for session storage');
    $sessionData = [
        'activities' => $allActivities,
        'athlete' => $athlete,
        'cached_at' => time(),
    ];
    $serialized = serialize($sessionData);
    $sessionSize = strlen($serialized);
    $profiler->endTimer('Serialize data for session storage');

    echo "Session data size: " . number_format($sessionSize / 1024, 2) . " KB\n\n";
}

// Main execution
try {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                     DASHBOARD PERFORMANCE PROFILER                         ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
    echo "\n";

    // Check if access token is provided
    if (!isset($argv[1])) {
        echo "Usage: php scripts/performance_profile.php <access_token>\n";
        echo "\nTo get your access token:\n";
        echo "1. Log in to the application\n";
        echo "2. Open browser developer tools\n";
        echo "3. Check Application > Cookies or Session Storage\n";
        echo "4. Copy the access_token value\n\n";
        echo "Alternatively, set STRAVA_TEST_TOKEN in .env file\n\n";
        exit(1);
    }

    $accessToken = $argv[1];
    $profiler = new PerformanceProfiler();

    // Profile dashboard load
    $profiler->startTimer('Total Dashboard Load');
    profileDashboardLoad($accessToken, $profiler);
    $profiler->endTimer('Total Dashboard Load');

    // Print report
    $profiler->printReport();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
