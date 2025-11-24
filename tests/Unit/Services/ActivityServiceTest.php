<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Activity;
use App\Services\ActivityService;
use PHPUnit\Framework\TestCase;
use DateTime;

class ActivityServiceTest extends TestCase
{
    private function createTestActivity(
        int $id,
        string $type,
        float $distance,
        int $movingTime,
        ?DateTime $startDate = null
    ): Activity {
        return new Activity(
            id: $id,
            type: $type,
            name: "Test Activity $id",
            startDate: $startDate ?? new DateTime(),
            distance: $distance,
            movingTime: $movingTime
        );
    }

    public function testGroupByType(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1800),
            $this->createTestActivity(2, 'Ride', 20000, 3600),
            $this->createTestActivity(3, 'Run', 8000, 2400),
            $this->createTestActivity(4, 'Swim', 1000, 1200),
            $this->createTestActivity(5, 'Run', 10000, 3000),
        ];

        $grouped = $service->groupByType($activities);

        $this->assertCount(3, $grouped);
        $this->assertArrayHasKey('Run', $grouped);
        $this->assertArrayHasKey('Ride', $grouped);
        $this->assertArrayHasKey('Swim', $grouped);
        $this->assertCount(3, $grouped['Run']);
        $this->assertCount(1, $grouped['Ride']);
        $this->assertCount(1, $grouped['Swim']);
    }

    public function testGroupByTypeWithEmptyArray(): void
    {
        $service = new ActivityService();
        $grouped = $service->groupByType([]);

        $this->assertSame([], $grouped);
    }

    public function testGetCountsByType(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1800),
            $this->createTestActivity(2, 'Ride', 20000, 3600),
            $this->createTestActivity(3, 'Run', 8000, 2400),
            $this->createTestActivity(4, 'Swim', 1000, 1200),
            $this->createTestActivity(5, 'Run', 10000, 3000),
            $this->createTestActivity(6, 'Ride', 25000, 4200),
        ];

        $counts = $service->getCountsByType($activities);

        $this->assertSame(3, $counts['Run']);
        $this->assertSame(2, $counts['Ride']);
        $this->assertSame(1, $counts['Swim']);

        // Check sorting (descending by count)
        $keys = array_keys($counts);
        $this->assertSame('Run', $keys[0]);
        $this->assertSame('Ride', $keys[1]);
        $this->assertSame('Swim', $keys[2]);
    }

    public function testGetCountsByTypeWithEmptyArray(): void
    {
        $service = new ActivityService();
        $counts = $service->getCountsByType([]);

        $this->assertSame([], $counts);
    }

    public function testGetMovingTimeByType(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1800),    // 30 min
            $this->createTestActivity(2, 'Ride', 20000, 3600),  // 60 min
            $this->createTestActivity(3, 'Run', 8000, 2400),    // 40 min
            $this->createTestActivity(4, 'Swim', 1000, 1200),   // 20 min
            $this->createTestActivity(5, 'Run', 10000, 3000),   // 50 min
        ];

        $times = $service->getMovingTimeByType($activities);

        $this->assertSame(7200, $times['Run']);   // 120 min total
        $this->assertSame(3600, $times['Ride']);  // 60 min
        $this->assertSame(1200, $times['Swim']);  // 20 min

        // Check sorting (descending by time)
        $keys = array_keys($times);
        $this->assertSame('Run', $keys[0]);
        $this->assertSame('Ride', $keys[1]);
        $this->assertSame('Swim', $keys[2]);
    }

    public function testGetMovingTimeByTypeWithEmptyArray(): void
    {
        $service = new ActivityService();
        $times = $service->getMovingTimeByType([]);

        $this->assertSame([], $times);
    }

    public function testGetDistanceByType(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1800),    // 5km
            $this->createTestActivity(2, 'Ride', 20000, 3600),  // 20km
            $this->createTestActivity(3, 'Run', 8000, 2400),    // 8km
            $this->createTestActivity(4, 'Swim', 1000, 1200),   // 1km
            $this->createTestActivity(5, 'Run', 10000, 3000),   // 10km
            $this->createTestActivity(6, 'Ride', 25000, 4200),  // 25km
        ];

        $distances = $service->getDistanceByType($activities);

        $this->assertSame(45000.0, $distances['Ride']);  // 45km total
        $this->assertSame(23000.0, $distances['Run']);   // 23km total
        $this->assertSame(1000.0, $distances['Swim']);   // 1km total

        // Check sorting (descending by distance)
        $keys = array_keys($distances);
        $this->assertSame('Ride', $keys[0]);
        $this->assertSame('Run', $keys[1]);
        $this->assertSame('Swim', $keys[2]);
    }

    public function testGetDistanceByTypeWithEmptyArray(): void
    {
        $service = new ActivityService();
        $distances = $service->getDistanceByType([]);

        $this->assertSame([], $distances);
    }

    public function testGroupByTypePreservesActivities(): void
    {
        $service = new ActivityService();

        $activity1 = $this->createTestActivity(1, 'Run', 5000, 1800);
        $activity2 = $this->createTestActivity(2, 'Run', 8000, 2400);

        $activities = [$activity1, $activity2];
        $grouped = $service->groupByType($activities);

        $this->assertSame($activity1, $grouped['Run'][0]);
        $this->assertSame($activity2, $grouped['Run'][1]);
    }

    public function testGetCountsByTypeSortsDescending(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1800),
            $this->createTestActivity(2, 'Ride', 20000, 3600),
            $this->createTestActivity(3, 'Ride', 25000, 4200),
            $this->createTestActivity(4, 'Ride', 30000, 5000),
            $this->createTestActivity(5, 'Swim', 1000, 1200),
            $this->createTestActivity(6, 'Swim', 1500, 1500),
        ];

        $counts = $service->getCountsByType($activities);

        // Ride: 3, Swim: 2, Run: 1
        $values = array_values($counts);
        $this->assertSame(3, $values[0]);
        $this->assertSame(2, $values[1]);
        $this->assertSame(1, $values[2]);
    }

    public function testGetMovingTimeByTypeSortsDescending(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1000),
            $this->createTestActivity(2, 'Ride', 20000, 5000),
            $this->createTestActivity(3, 'Swim', 1000, 3000),
        ];

        $times = $service->getMovingTimeByType($activities);

        $values = array_values($times);
        $this->assertSame(5000, $values[0]); // Ride
        $this->assertSame(3000, $values[1]); // Swim
        $this->assertSame(1000, $values[2]); // Run
    }

    public function testGetDistanceByTypeSortsDescending(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1800),
            $this->createTestActivity(2, 'Ride', 30000, 3600),
            $this->createTestActivity(3, 'Swim', 2000, 1200),
        ];

        $distances = $service->getDistanceByType($activities);

        $values = array_values($distances);
        $this->assertSame(30000.0, $values[0]); // Ride
        $this->assertSame(5000.0, $values[1]);  // Run
        $this->assertSame(2000.0, $values[2]);  // Swim
    }

    public function testGetCountsByTypeHandlesSingleType(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 1800),
            $this->createTestActivity(2, 'Run', 8000, 2400),
            $this->createTestActivity(3, 'Run', 10000, 3000),
        ];

        $counts = $service->getCountsByType($activities);

        $this->assertCount(1, $counts);
        $this->assertSame(3, $counts['Run']);
    }

    public function testGetMovingTimeByTypeAccumulatesCorrectly(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Run', 5000, 600),
            $this->createTestActivity(2, 'Run', 5000, 700),
            $this->createTestActivity(3, 'Run', 5000, 800),
        ];

        $times = $service->getMovingTimeByType($activities);

        $this->assertSame(2100, $times['Run']); // 600 + 700 + 800
    }

    public function testGetDistanceByTypeAccumulatesCorrectly(): void
    {
        $service = new ActivityService();

        $activities = [
            $this->createTestActivity(1, 'Ride', 10000, 1800),
            $this->createTestActivity(2, 'Ride', 15000, 2400),
            $this->createTestActivity(3, 'Ride', 20000, 3000),
        ];

        $distances = $service->getDistanceByType($activities);

        $this->assertSame(45000.0, $distances['Ride']); // 10k + 15k + 20k
    }
}
