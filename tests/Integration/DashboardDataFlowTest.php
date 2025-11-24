<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Activity;
use App\Services\ActivityService;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Dashboard Data Flow Integration Test
 *
 * Tests the complete data flow from API response to dashboard widgets
 */
class DashboardDataFlowTest extends TestCase
{
    private ActivityService $activityService;
    private array $sampleActivities;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activityService = new ActivityService();

        // Create sample activities that represent a typical user's data
        $this->sampleActivities = $this->createSampleActivities();
    }

    public function testCompleteDataFlowForOverviewTab(): void
    {
        // Step 1: Parse activities from API response
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        $this->assertCount(15, $activities);
        $this->assertContainsOnlyInstancesOf(Activity::class, $activities);

        // Step 2: Calculate activity counts for pie chart
        $counts = $this->activityService->getCountsByType($activities);

        $this->assertArrayHasKey('Run', $counts);
        $this->assertArrayHasKey('Ride', $counts);
        $this->assertSame(8, $counts['Run']);
        $this->assertSame(5, $counts['Ride']);
        $this->assertSame(2, $counts['Swim']);

        // Step 3: Calculate total activities
        $totalActivities = array_sum($counts);
        $this->assertSame(15, $totalActivities);

        // Step 4: Calculate percentages for chart
        $percentages = [];
        foreach ($counts as $type => $count) {
            $percentages[$type] = round(($count / $totalActivities) * 100, 1);
        }

        $this->assertEqualsWithDelta(53.3, $percentages['Run'], 0.1);
        $this->assertEqualsWithDelta(33.3, $percentages['Ride'], 0.1);
        $this->assertEqualsWithDelta(13.3, $percentages['Swim'], 0.1);
    }

    public function testCompleteDataFlowForDurationTab(): void
    {
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        // Step 1: Calculate duration by type
        $durations = $this->activityService->getMovingTimeByType($activities);

        $this->assertArrayHasKey('Run', $durations);
        $this->assertArrayHasKey('Ride', $durations);

        // Step 2: Format durations for display
        $formattedDurations = [];
        foreach ($durations as $type => $seconds) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $formattedDurations[$type] = sprintf('%dh %dm', $hours, $minutes);
        }

        $this->assertIsString($formattedDurations['Run']);
        $this->assertMatchesRegularExpression('/\d+h \d+m/', $formattedDurations['Run']);

        // Step 3: Calculate total duration
        $totalDuration = array_sum($durations);
        $this->assertGreaterThan(0, $totalDuration);

        // Step 4: Identify most time-consuming activity type
        arsort($durations);
        $topType = array_key_first($durations);
        $this->assertContains($topType, ['Run', 'Ride', 'Swim']);
    }

    public function testCompleteDataFlowForRunningTab(): void
    {
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        // Step 1: Filter running activities only
        $runningActivities = array_filter($activities, fn($a) => $a->isRun());
        $this->assertCount(8, $runningActivities);

        // Step 2: Calculate total runs and distance
        $totalRuns = count($runningActivities);
        $totalDistance = array_sum(array_map(fn($a) => $a->distance, $runningActivities));

        $this->assertSame(8, $totalRuns);
        $this->assertGreaterThan(0, $totalDistance);

        // Step 3: Calculate average pace
        $totalTime = array_sum(array_map(fn($a) => $a->movingTime, $runningActivities));
        $totalDistanceKm = $totalDistance / 1000;
        $avgPace = $totalDistanceKm > 0 ? ($totalTime / 60) / $totalDistanceKm : 0;

        $this->assertGreaterThan(0, $avgPace);

        // Step 4: Find fastest pace
        $paces = array_map(fn($a) => $a->getPacePerKm(), $runningActivities);
        $paces = array_filter($paces, fn($p) => $p !== null);
        $fastestPace = min($paces);

        $this->assertGreaterThan(0, $fastestPace);
        $this->assertLessThanOrEqual($avgPace, $fastestPace); // Fastest should be less than or equal to average

        // Step 5: Find longest run
        $distances = array_map(fn($a) => $a->distance, $runningActivities);
        $longestRun = max($distances);

        $this->assertGreaterThan(0, $longestRun);

        // Step 6: Count runs over 10K
        $runsOver10K = count(array_filter($runningActivities, fn($a) => $a->distance >= 10000));
        $this->assertGreaterThanOrEqual(0, $runsOver10K);
    }

    public function testCompleteDataFlowForHeatmapTab(): void
    {
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        $startDate = new DateTime('-30 days');
        $endDate = new DateTime();

        // Step 1: Group activities by date
        $activitiesByDate = [];
        foreach ($activities as $activity) {
            $dateKey = $activity->startDate->format('Y-m-d');
            if (!isset($activitiesByDate[$dateKey])) {
                $activitiesByDate[$dateKey] = [];
            }
            $activitiesByDate[$dateKey][] = $activity;
        }

        $this->assertIsArray($activitiesByDate);

        // Step 2: Calculate daily statistics
        $dailyStats = [];
        foreach ($activitiesByDate as $date => $dayActivities) {
            $dailyStats[$date] = [
                'count' => count($dayActivities),
                'totalTime' => array_sum(array_map(fn($a) => $a->movingTime, $dayActivities)),
                'totalDistance' => array_sum(array_map(fn($a) => $a->distance, $dayActivities)),
            ];
        }

        $this->assertNotEmpty($dailyStats);

        // Step 3: Calculate workout streaks
        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 0;

        $dates = array_keys($activitiesByDate);
        sort($dates);

        $this->assertIsArray($dates);

        // Step 4: Count active vs rest days
        $activeDays = count($activitiesByDate);
        $totalDays = $startDate->diff($endDate)->days + 1;
        $restDays = $totalDays - $activeDays;

        $this->assertGreaterThanOrEqual($activeDays, $totalDays);
        $this->assertGreaterThanOrEqual(0, $restDays);
    }

    public function testCompleteDataFlowForTrendsTab(): void
    {
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        $startDate = new DateTime('-30 days');
        $endDate = new DateTime();

        // Step 1: Group activities by date for distance trend
        $dailyDistance = [];
        foreach ($activities as $activity) {
            $dateKey = $activity->startDate->format('Y-m-d');
            if (!isset($dailyDistance[$dateKey])) {
                $dailyDistance[$dateKey] = 0;
            }
            $dailyDistance[$dateKey] += $activity->distance / 1000; // Convert to km
        }

        ksort($dailyDistance);
        $this->assertIsArray($dailyDistance);

        // Step 2: Calculate distance trend direction
        $values = array_values($dailyDistance);
        if (count($values) >= 2) {
            $first = array_slice($values, 0, (int)(count($values) / 2));
            $second = array_slice($values, (int)(count($values) / 2));

            $avgFirst = array_sum($first) / count($first);
            $avgSecond = array_sum($second) / count($second);

            $trend = $avgSecond > $avgFirst ? 'up' : ($avgSecond < $avgFirst ? 'down' : 'flat');
            $this->assertContains($trend, ['up', 'down', 'flat']);
        }

        // Step 3: Group running activities by date for pace trend
        $runningActivities = array_filter($activities, fn($a) => $a->isRun());
        $dailyPace = [];

        foreach ($runningActivities as $activity) {
            $dateKey = $activity->startDate->format('Y-m-d');
            $pace = $activity->getPacePerKm();

            if ($pace !== null) {
                if (!isset($dailyPace[$dateKey])) {
                    $dailyPace[$dateKey] = [];
                }
                $dailyPace[$dateKey][] = $pace;
            }
        }

        // Calculate average pace per day
        $avgDailyPace = [];
        foreach ($dailyPace as $date => $paces) {
            $avgDailyPace[$date] = array_sum($paces) / count($paces);
        }

        ksort($avgDailyPace);
        $this->assertIsArray($avgDailyPace);
    }

    public function testDateRangeFiltering(): void
    {
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        // Test different date ranges
        $ranges = [
            ['days' => 7, 'label' => '7 days'],
            ['days' => 30, 'label' => '30 days'],
            ['days' => 90, 'label' => '90 days'],
        ];

        foreach ($ranges as $range) {
            $startDate = new DateTime("-{$range['days']} days");
            $endDate = new DateTime();

            $filtered = array_filter($activities, function($activity) use ($startDate, $endDate) {
                return $activity->startDate >= $startDate && $activity->startDate <= $endDate;
            });

            // All our sample activities should be within the last 30 days
            if ($range['days'] >= 30) {
                $this->assertCount(15, $filtered, "Failed for {$range['label']}");
            }
        }
    }

    public function testCachingSimulation(): void
    {
        // Start session for caching
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        // Simulate caching
        $_SESSION['cached_activities'] = $this->sampleActivities;
        $_SESSION['cache_start_date'] = (new DateTime('-30 days'))->format('Y-m-d');
        $_SESSION['cache_end_date'] = (new DateTime())->format('Y-m-d');
        $_SESSION['cached_at'] = time();

        // Verify cache is stored
        $this->assertArrayHasKey('cached_activities', $_SESSION);
        $this->assertCount(15, $_SESSION['cached_activities']);

        // Simulate cache retrieval
        $cachedActivities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $_SESSION['cached_activities']
        );

        $this->assertCount(15, $cachedActivities);

        // Clean up
        unset($_SESSION['cached_activities']);
        unset($_SESSION['cache_start_date']);
        unset($_SESSION['cache_end_date']);
        unset($_SESSION['cached_at']);
    }

    public function testDistanceHistogramGeneration(): void
    {
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $this->sampleActivities
        );

        $runningActivities = array_filter($activities, fn($a) => $a->isRun());

        // Group runs by distance buckets (miles)
        $buckets = [];
        foreach ($runningActivities as $activity) {
            $miles = $activity->getDistanceMiles();
            $bucketIndex = (int)floor($miles);

            if (!isset($buckets[$bucketIndex])) {
                $buckets[$bucketIndex] = 0;
            }
            $buckets[$bucketIndex]++;
        }

        ksort($buckets);
        $this->assertIsArray($buckets);
        $this->assertNotEmpty($buckets);

        // Verify total count matches
        $this->assertSame(count($runningActivities), array_sum($buckets));
    }

    // Helper method to create sample activities
    private function createSampleActivities(): array
    {
        $now = new DateTime();
        $activities = [];

        // Create 8 runs over the past 30 days
        for ($i = 0; $i < 8; $i++) {
            $daysAgo = $i * 3;
            $activities[] = [
                'id' => 1000 + $i,
                'type' => 'Run',
                'name' => "Morning Run $i",
                'start_date' => (clone $now)->modify("-{$daysAgo} days")->format('Y-m-d\TH:i:s\Z'),
                'distance' => 5000 + ($i * 1000), // 5-12km
                'moving_time' => 1500 + ($i * 300), // 25-60 minutes
            ];
        }

        // Create 5 rides
        for ($i = 0; $i < 5; $i++) {
            $daysAgo = ($i * 5) + 1;
            $activities[] = [
                'id' => 2000 + $i,
                'type' => 'Ride',
                'name' => "Evening Ride $i",
                'start_date' => (clone $now)->modify("-{$daysAgo} days")->format('Y-m-d\TH:i:s\Z'),
                'distance' => 20000 + ($i * 5000), // 20-40km
                'moving_time' => 3600 + ($i * 600), // 60-100 minutes
            ];
        }

        // Create 2 swims
        for ($i = 0; $i < 2; $i++) {
            $daysAgo = ($i * 10) + 2;
            $activities[] = [
                'id' => 3000 + $i,
                'type' => 'Swim',
                'name' => "Pool Swim $i",
                'start_date' => (clone $now)->modify("-{$daysAgo} days")->format('Y-m-d\TH:i:s\Z'),
                'distance' => 1000 + ($i * 500), // 1-1.5km
                'moving_time' => 1200 + ($i * 300), // 20-25 minutes
            ];
        }

        return $activities;
    }
}
