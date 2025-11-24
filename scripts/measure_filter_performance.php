<?php

declare(strict_types=1);

/**
 * Filter Performance Measurement Script
 *
 * Measures the performance of date range filtering operations
 * to ensure they meet the < 300ms target for cached data
 */

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use App\Services\ActivityService;
use App\Services\HeatmapService;

class FilterPerformanceTester
{
    private array $cachedActivities = [];
    private array $timings = [];

    public function __construct(array $activities)
    {
        $this->cachedActivities = ActivityService::parseActivities($activities);
    }

    public function testDateRangeFilter(string $testName, DateTime $startDate, DateTime $endDate): float
    {
        $start = microtime(true);

        // Filter activities
        $filtered = ActivityService::filterActivitiesByDateRange(
            $this->cachedActivities,
            $startDate,
            $endDate
        );

        // Calculate all stats (simulating full dashboard update)
        $activityCounts = ActivityService::getActivityCountsByType($filtered);
        $durationByType = ActivityService::getDurationByType($filtered);
        $heatmapData = HeatmapService::generateHeatmapData($filtered, $startDate, $endDate);
        $workoutStats = HeatmapService::calculateWorkoutStatistics($filtered, $startDate, $endDate);
        $distanceTrend = ActivityService::getDailyDistanceTrend($filtered, $startDate, $endDate);

        // Running stats if applicable
        $runningActivities = array_filter($filtered, fn($a) => $a->type === 'Run');
        if (count($runningActivities) > 0) {
            $avgPace = ActivityService::calculateAveragePace($runningActivities);
            $fastestPace = ActivityService::getFastestPace($runningActivities);
            $longestRun = ActivityService::getLongestRun($runningActivities);
            $histogram = ActivityService::generateDistanceHistogram($runningActivities, 'mi');
            $paceTrend = ActivityService::getDailyPaceTrend($runningActivities, $startDate, $endDate);
        }

        $elapsed = microtime(true) - $start;
        $this->timings[$testName] = $elapsed;

        return $elapsed;
    }

    public function runAllTests(): void
    {
        $now = new DateTime();

        echo "\nRunning filter performance tests with " . count($this->cachedActivities) . " cached activities...\n\n";

        // Test 1: 7 days filter
        $start7 = (clone $now)->modify('-7 days');
        $time7 = $this->testDateRangeFilter('7 days filter', $start7, $now);
        printf("  7 days:   %.2f ms %s\n", $time7 * 1000, $this->getStatus($time7));

        // Test 2: 30 days filter
        $start30 = (clone $now)->modify('-30 days');
        $time30 = $this->testDateRangeFilter('30 days filter', $start30, $now);
        printf("  30 days:  %.2f ms %s\n", $time30 * 1000, $this->getStatus($time30));

        // Test 3: 90 days filter
        $start90 = (clone $now)->modify('-90 days');
        $time90 = $this->testDateRangeFilter('90 days filter', $start90, $now);
        printf("  90 days:  %.2f ms %s\n", $time90 * 1000, $this->getStatus($time90));

        // Test 4: 6 months filter
        $start180 = (clone $now)->modify('-6 months');
        $time180 = $this->testDateRangeFilter('6 months filter', $start180, $now);
        printf("  6 months: %.2f ms %s\n", $time180 * 1000, $this->getStatus($time180));

        // Test 5: Year to date
        $startYTD = new DateTime($now->format('Y') . '-01-01');
        $timeYTD = $this->testDateRangeFilter('Year to date', $startYTD, $now);
        printf("  YTD:      %.2f ms %s\n", $timeYTD * 1000, $this->getStatus($timeYTD));

        // Test 6: Custom range (last month)
        $startCustom = (clone $now)->modify('-1 month');
        $endCustom = $now;
        $timeCustom = $this->testDateRangeFilter('Custom (1 month)', $startCustom, $endCustom);
        printf("  Custom:   %.2f ms %s\n", $timeCustom * 1000, $this->getStatus($timeCustom));

        // Calculate statistics
        $times = array_values($this->timings);
        $avgTime = array_sum($times) / count($times);
        $maxTime = max($times);
        $minTime = min($times);

        echo "\n" . str_repeat("-", 80) . "\n";
        printf("Average:  %.2f ms\n", $avgTime * 1000);
        printf("Min:      %.2f ms\n", $minTime * 1000);
        printf("Max:      %.2f ms\n", $maxTime * 1000);
        echo str_repeat("-", 80) . "\n\n";

        // Check if target is met
        $target = 0.300; // 300ms
        $passing = array_filter($times, fn($t) => $t < $target);
        $passRate = (count($passing) / count($times)) * 100;

        echo "PERFORMANCE TARGET: < 300ms\n";
        printf("Pass Rate: %.0f%% (%d/%d tests)\n", $passRate, count($passing), count($times));

        if ($passRate === 100.0) {
            echo "Status: ✓ ALL TESTS PASSED\n";
        } elseif ($passRate >= 80.0) {
            echo "Status: ⚠ MOSTLY PASSING (needs improvement)\n";
        } else {
            echo "Status: ✗ FAILING (optimization required)\n";
        }

        echo "\n";
    }

    private function getStatus(float $time): string
    {
        $target = 0.300; // 300ms
        if ($time < $target) {
            return "✓ PASS";
        } elseif ($time < $target * 1.5) {
            return "⚠ WARN";
        } else {
            return "✗ FAIL";
        }
    }
}

// Main execution
try {
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                   FILTER PERFORMANCE MEASUREMENT                           ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n";

    // Check if we have cached activities file or need to fetch
    if (!isset($argv[1])) {
        echo "\nUsage: php scripts/measure_filter_performance.php <access_token>\n\n";
        echo "This script measures the performance of date range filtering operations\n";
        echo "to ensure they meet the < 300ms target for cached data.\n\n";
        echo "To get your access token:\n";
        echo "1. Log in to the application\n";
        echo "2. Open browser developer tools\n";
        echo "3. Go to Application > Session Storage\n";
        echo "4. Copy the access_token value\n\n";
        exit(1);
    }

    $accessToken = $argv[1];

    // Fetch activities
    echo "\nFetching activities from Strava API...\n";
    $stravaClient = new \App\Services\StravaClient();

    $allActivities = [];
    $page = 1;
    $perPage = 50;

    do {
        echo "  Fetching page $page...";
        $activities = $stravaClient->getActivities($accessToken, $page, $perPage);

        if (!$activities || count($activities) === 0) {
            break;
        }

        echo " (" . count($activities) . " activities)\n";
        $allActivities = array_merge($allActivities, $activities);
        $page++;

        // Limit to reasonable amount for testing
        if ($page > 10) {
            break;
        }

    } while (count($activities) === $perPage);

    echo "\nTotal activities fetched: " . count($allActivities) . "\n";

    if (count($allActivities) === 0) {
        echo "Error: No activities found. Make sure your Strava account has activities.\n";
        exit(1);
    }

    // Run tests
    $tester = new FilterPerformanceTester($allActivities);
    $tester->runAllTests();

    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                      MEASUREMENT COMPLETE                                  ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
    echo "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
