<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Activity;
use App\Services\ActivityService;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Activity Fetching Integration Test
 *
 * Tests activity fetching and parsing with simulated Strava API responses
 */
class ActivityFetchingTest extends TestCase
{
    private ActivityService $activityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activityService = new ActivityService();
    }

    public function testParseMultipleActivitiesFromApiResponse(): void
    {
        // Simulate Strava API response
        $apiResponse = [
            [
                'id' => 123456,
                'type' => 'Run',
                'name' => 'Morning Run',
                'start_date' => '2024-01-15T06:30:00Z',
                'distance' => 5000.0,
                'moving_time' => 1800,
                'average_speed' => 2.78,
            ],
            [
                'id' => 123457,
                'type' => 'Ride',
                'name' => 'Evening Ride',
                'start_date' => '2024-01-15T18:00:00Z',
                'distance' => 25000.0,
                'moving_time' => 3600,
                'average_speed' => 6.94,
            ],
        ];

        // Parse activities
        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        $this->assertCount(2, $activities);
        $this->assertInstanceOf(Activity::class, $activities[0]);
        $this->assertInstanceOf(Activity::class, $activities[1]);

        // Verify first activity
        $this->assertSame(123456, $activities[0]->id);
        $this->assertSame('Run', $activities[0]->type);
        $this->assertSame(5000.0, $activities[0]->distance);

        // Verify second activity
        $this->assertSame(123457, $activities[1]->id);
        $this->assertSame('Ride', $activities[1]->type);
        $this->assertSame(25000.0, $activities[1]->distance);
    }

    public function testGroupActivitiesByType(): void
    {
        $apiResponse = [
            ['id' => 1, 'type' => 'Run', 'name' => 'Run 1', 'start_date' => '2024-01-15T06:00:00Z', 'distance' => 5000, 'moving_time' => 1800],
            ['id' => 2, 'type' => 'Ride', 'name' => 'Ride 1', 'start_date' => '2024-01-15T08:00:00Z', 'distance' => 20000, 'moving_time' => 3600],
            ['id' => 3, 'type' => 'Run', 'name' => 'Run 2', 'start_date' => '2024-01-15T18:00:00Z', 'distance' => 8000, 'moving_time' => 2400],
            ['id' => 4, 'type' => 'Swim', 'name' => 'Swim 1', 'start_date' => '2024-01-16T06:00:00Z', 'distance' => 1000, 'moving_time' => 1200],
        ];

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        $grouped = $this->activityService->groupByType($activities);

        $this->assertCount(3, $grouped);
        $this->assertArrayHasKey('Run', $grouped);
        $this->assertArrayHasKey('Ride', $grouped);
        $this->assertArrayHasKey('Swim', $grouped);
        $this->assertCount(2, $grouped['Run']);
        $this->assertCount(1, $grouped['Ride']);
        $this->assertCount(1, $grouped['Swim']);
    }

    public function testCalculateActivityStatistics(): void
    {
        $apiResponse = [
            ['id' => 1, 'type' => 'Run', 'name' => 'Run 1', 'start_date' => '2024-01-15T06:00:00Z', 'distance' => 5000, 'moving_time' => 1500],
            ['id' => 2, 'type' => 'Run', 'name' => 'Run 2', 'start_date' => '2024-01-16T06:00:00Z', 'distance' => 10000, 'moving_time' => 3000],
            ['id' => 3, 'type' => 'Ride', 'name' => 'Ride 1', 'start_date' => '2024-01-17T08:00:00Z', 'distance' => 30000, 'moving_time' => 4500],
        ];

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        // Test counts
        $counts = $this->activityService->getCountsByType($activities);
        $this->assertSame(2, $counts['Run']);
        $this->assertSame(1, $counts['Ride']);

        // Test total distance
        $distances = $this->activityService->getDistanceByType($activities);
        $this->assertSame(15000.0, $distances['Run']);
        $this->assertSame(30000.0, $distances['Ride']);

        // Test total moving time
        $times = $this->activityService->getMovingTimeByType($activities);
        $this->assertSame(4500, $times['Run']);
        $this->assertSame(4500, $times['Ride']);
    }

    public function testHandleEmptyApiResponse(): void
    {
        $apiResponse = [];

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        $this->assertEmpty($activities);

        $counts = $this->activityService->getCountsByType($activities);
        $this->assertEmpty($counts);
    }

    public function testHandleIncompleteApiResponse(): void
    {
        // Simulate API response with minimal/missing fields
        $apiResponse = [
            [
                'id' => 999,
                'distance' => 5000,
                'moving_time' => 1800,
                // Missing: type, name, start_date
            ],
        ];

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        $this->assertCount(1, $activities);
        $this->assertSame(999, $activities[0]->id);
        $this->assertSame('Unknown', $activities[0]->type);
        $this->assertSame('Untitled Activity', $activities[0]->name);
        $this->assertSame(5000.0, $activities[0]->distance);
    }

    public function testFilterActivitiesByDateRange(): void
    {
        $apiResponse = [
            ['id' => 1, 'type' => 'Run', 'name' => 'Run 1', 'start_date' => '2024-01-10T06:00:00Z', 'distance' => 5000, 'moving_time' => 1800],
            ['id' => 2, 'type' => 'Run', 'name' => 'Run 2', 'start_date' => '2024-01-15T06:00:00Z', 'distance' => 8000, 'moving_time' => 2400],
            ['id' => 3, 'type' => 'Run', 'name' => 'Run 3', 'start_date' => '2024-01-20T06:00:00Z', 'distance' => 10000, 'moving_time' => 3000],
            ['id' => 4, 'type' => 'Run', 'name' => 'Run 4', 'start_date' => '2024-01-25T06:00:00Z', 'distance' => 12000, 'moving_time' => 3600],
        ];

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        // Filter for activities between Jan 12 and Jan 22
        $startDate = new DateTime('2024-01-12');
        $endDate = new DateTime('2024-01-22');

        $filtered = array_filter($activities, function($activity) use ($startDate, $endDate) {
            return $activity->startDate >= $startDate && $activity->startDate <= $endDate;
        });

        $filtered = array_values($filtered); // Re-index

        $this->assertCount(2, $filtered);
        $this->assertSame(2, $filtered[0]->id); // Jan 15
        $this->assertSame(3, $filtered[1]->id); // Jan 20
    }

    public function testCalculateRunningStatistics(): void
    {
        $apiResponse = [
            ['id' => 1, 'type' => 'Run', 'name' => 'Run 1', 'start_date' => '2024-01-15T06:00:00Z', 'distance' => 5000, 'moving_time' => 1500], // 5:00/km
            ['id' => 2, 'type' => 'Run', 'name' => 'Run 2', 'start_date' => '2024-01-16T06:00:00Z', 'distance' => 10000, 'moving_time' => 2700], // 4:30/km
            ['id' => 3, 'type' => 'Ride', 'name' => 'Ride 1', 'start_date' => '2024-01-17T08:00:00Z', 'distance' => 30000, 'moving_time' => 4500],
        ];

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        // Filter running activities
        $runningActivities = array_filter($activities, fn($a) => $a->isRun());

        $this->assertCount(2, $runningActivities);

        // Calculate total running distance
        $totalDistance = array_sum(array_map(fn($a) => $a->distance, $runningActivities));
        $this->assertSame(15000.0, $totalDistance);

        // Calculate total running time
        $totalTime = array_sum(array_map(fn($a) => $a->movingTime, $runningActivities));
        $this->assertSame(4200, $totalTime);

        // Find fastest pace
        $paces = array_map(fn($a) => $a->getPacePerKm(), $runningActivities);
        $fastestPace = min(array_filter($paces, fn($p) => $p !== null));
        $this->assertEqualsWithDelta(4.5, $fastestPace, 0.01);

        // Find longest run
        $distances = array_map(fn($a) => $a->distance, $runningActivities);
        $longestRun = max($distances);
        $this->assertSame(10000.0, $longestRun);
    }

    public function testHandleMultipleActivityTypes(): void
    {
        $apiResponse = [
            ['id' => 1, 'type' => 'Run', 'name' => 'Run', 'start_date' => '2024-01-15T06:00:00Z', 'distance' => 5000, 'moving_time' => 1800],
            ['id' => 2, 'type' => 'Ride', 'name' => 'Ride', 'start_date' => '2024-01-15T08:00:00Z', 'distance' => 20000, 'moving_time' => 3600],
            ['id' => 3, 'type' => 'Swim', 'name' => 'Swim', 'start_date' => '2024-01-15T10:00:00Z', 'distance' => 1000, 'moving_time' => 1200],
            ['id' => 4, 'type' => 'VirtualRun', 'name' => 'Virtual Run', 'start_date' => '2024-01-15T18:00:00Z', 'distance' => 8000, 'moving_time' => 2400],
            ['id' => 5, 'type' => 'Walk', 'name' => 'Walk', 'start_date' => '2024-01-16T06:00:00Z', 'distance' => 3000, 'moving_time' => 2700],
        ];

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $apiResponse
        );

        $counts = $this->activityService->getCountsByType($activities);

        $this->assertCount(5, $counts);
        $this->assertSame(1, $counts['Run']);
        $this->assertSame(1, $counts['Ride']);
        $this->assertSame(1, $counts['Swim']);
        $this->assertSame(1, $counts['VirtualRun']);
        $this->assertSame(1, $counts['Walk']);
    }

    public function testHandlePaginatedApiResponse(): void
    {
        // Simulate first page
        $page1 = [
            ['id' => 1, 'type' => 'Run', 'name' => 'Run 1', 'start_date' => '2024-01-15T06:00:00Z', 'distance' => 5000, 'moving_time' => 1800],
            ['id' => 2, 'type' => 'Run', 'name' => 'Run 2', 'start_date' => '2024-01-14T06:00:00Z', 'distance' => 8000, 'moving_time' => 2400],
        ];

        // Simulate second page
        $page2 = [
            ['id' => 3, 'type' => 'Ride', 'name' => 'Ride 1', 'start_date' => '2024-01-13T08:00:00Z', 'distance' => 20000, 'moving_time' => 3600],
            ['id' => 4, 'type' => 'Run', 'name' => 'Run 3', 'start_date' => '2024-01-12T06:00:00Z', 'distance' => 10000, 'moving_time' => 3000],
        ];

        // Merge pages
        $allActivities = array_merge($page1, $page2);

        $activities = array_map(
            fn($data) => Activity::fromStravaApi($data),
            $allActivities
        );

        $this->assertCount(4, $activities);

        $counts = $this->activityService->getCountsByType($activities);
        $this->assertSame(3, $counts['Run']);
        $this->assertSame(1, $counts['Ride']);
    }

    public function testActivityDataIntegrity(): void
    {
        $apiResponse = [
            [
                'id' => 123456789,
                'type' => 'Run',
                'name' => 'Morning Run with Hills',
                'start_date' => '2024-01-15T06:30:00Z',
                'distance' => 10500.5,
                'moving_time' => 3125,
                'average_speed' => 3.36,
                'max_speed' => 5.2,
                'total_elevation_gain' => 125.5,
                'average_heartrate' => 152.3,
                'max_heartrate' => 178.0,
            ],
        ];

        $activity = Activity::fromStravaApi($apiResponse[0]);

        // Verify all data is preserved
        $this->assertSame(123456789, $activity->id);
        $this->assertSame('Run', $activity->type);
        $this->assertSame('Morning Run with Hills', $activity->name);
        $this->assertSame(10500.5, $activity->distance);
        $this->assertSame(3125, $activity->movingTime);
        $this->assertSame(3.36, $activity->averageSpeed);
        $this->assertSame(5.2, $activity->maxSpeed);
        $this->assertSame(125.5, $activity->elevationGain);
        $this->assertSame(152.3, $activity->averageHeartrate);
        $this->assertSame(178.0, $activity->maxHeartrate);

        // Verify calculated values
        $this->assertEqualsWithDelta(10.5005, $activity->getDistanceKm(), 0.0001);
        $this->assertEqualsWithDelta(6.524, $activity->getDistanceMiles(), 0.01);
        $this->assertEqualsWithDelta(0.868, $activity->getMovingTimeHours(), 0.001);
    }
}
