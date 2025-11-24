<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Activity;
use DateTime;
use PHPUnit\Framework\TestCase;

class ActivityTest extends TestCase
{
    public function testActivityCreation(): void
    {
        $date = new DateTime('2024-01-15 10:30:00');
        $activity = new Activity(
            id: 123456,
            type: 'Run',
            name: 'Morning Run',
            startDate: $date,
            distance: 5000.0,  // 5km in meters
            movingTime: 1800,  // 30 minutes in seconds
            averageSpeed: 2.78, // ~2.78 m/s
            maxSpeed: 4.0,
            elevationGain: 100.0,
            averageHeartrate: 145.0,
            maxHeartrate: 165.0
        );

        $this->assertSame(123456, $activity->id);
        $this->assertSame('Run', $activity->type);
        $this->assertSame('Morning Run', $activity->name);
        $this->assertEquals($date, $activity->startDate);
        $this->assertSame(5000.0, $activity->distance);
        $this->assertSame(1800, $activity->movingTime);
        $this->assertSame(2.78, $activity->averageSpeed);
        $this->assertSame(4.0, $activity->maxSpeed);
        $this->assertSame(100.0, $activity->elevationGain);
        $this->assertSame(145.0, $activity->averageHeartrate);
        $this->assertSame(165.0, $activity->maxHeartrate);
    }

    public function testFromStravaApiWithFullData(): void
    {
        $apiData = [
            'id' => 987654,
            'type' => 'Ride',
            'name' => 'Evening Ride',
            'start_date' => '2024-01-20T18:00:00Z',
            'distance' => 20000.0,
            'moving_time' => 3600,
            'average_speed' => 5.56,
            'max_speed' => 12.0,
            'total_elevation_gain' => 300.0,
            'average_heartrate' => 135.0,
            'max_heartrate' => 160.0,
        ];

        $activity = Activity::fromStravaApi($apiData);

        $this->assertSame(987654, $activity->id);
        $this->assertSame('Ride', $activity->type);
        $this->assertSame('Evening Ride', $activity->name);
        $this->assertSame(20000.0, $activity->distance);
        $this->assertSame(3600, $activity->movingTime);
        $this->assertSame(5.56, $activity->averageSpeed);
        $this->assertSame(12.0, $activity->maxSpeed);
        $this->assertSame(300.0, $activity->elevationGain);
        $this->assertSame(135.0, $activity->averageHeartrate);
        $this->assertSame(160.0, $activity->maxHeartrate);
    }

    public function testFromStravaApiWithMinimalData(): void
    {
        $apiData = [
            'id' => 111222,
            'distance' => 3000.0,
            'moving_time' => 900,
        ];

        $activity = Activity::fromStravaApi($apiData);

        $this->assertSame(111222, $activity->id);
        $this->assertSame('Unknown', $activity->type);
        $this->assertSame('Untitled Activity', $activity->name);
        $this->assertSame(3000.0, $activity->distance);
        $this->assertSame(900, $activity->movingTime);
        $this->assertNull($activity->averageSpeed);
        $this->assertNull($activity->maxSpeed);
        $this->assertNull($activity->elevationGain);
        $this->assertNull($activity->averageHeartrate);
        $this->assertNull($activity->maxHeartrate);
    }

    public function testGetDistanceKm(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 5000.0,  // 5km in meters
            movingTime: 1800
        );

        $this->assertSame(5.0, $activity->getDistanceKm());
    }

    public function testGetDistanceMiles(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 1609.34,  // 1 mile in meters
            movingTime: 600
        );

        $this->assertEqualsWithDelta(1.0, $activity->getDistanceMiles(), 0.01);
    }

    public function testGetMovingTimeHours(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 10000.0,
            movingTime: 3600  // 1 hour
        );

        $this->assertSame(1.0, $activity->getMovingTimeHours());
    }

    public function testGetMovingTimeFormattedWithHours(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 10000.0,
            movingTime: 3665  // 1:01:05
        );

        $this->assertSame('1:01:05', $activity->getMovingTimeFormatted());
    }

    public function testGetMovingTimeFormattedWithoutHours(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 5000.0,
            movingTime: 1825  // 30:25
        );

        $this->assertSame('30:25', $activity->getMovingTimeFormatted());
    }

    public function testGetPacePerKm(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 5000.0,  // 5km
            movingTime: 1500   // 25 minutes (5:00 per km)
        );

        $pace = $activity->getPacePerKm();
        $this->assertNotNull($pace);
        $this->assertEqualsWithDelta(5.0, $pace, 0.01);
    }

    public function testGetPacePerMile(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 1609.34,  // 1 mile
            movingTime: 480     // 8 minutes (8:00 per mile)
        );

        $pace = $activity->getPacePerMile();
        $this->assertNotNull($pace);
        $this->assertEqualsWithDelta(8.0, $pace, 0.01);
    }

    public function testGetPacePerKmReturnsNullForZeroDistance(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 0.0,
            movingTime: 600
        );

        $this->assertNull($activity->getPacePerKm());
    }

    public function testGetPacePerMileReturnsNullForZeroTime(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 5000.0,
            movingTime: 0
        );

        $this->assertNull($activity->getPacePerMile());
    }

    public function testGetPaceFormattedMetric(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 5000.0,  // 5km
            movingTime: 1530   // 25:30 (5:06 per km)
        );

        $this->assertSame('5:06', $activity->getPaceFormatted(false));
    }

    public function testGetPaceFormattedImperial(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 1609.34,  // 1 mile
            movingTime: 510     // 8:30 per mile
        );

        $this->assertSame('8:30', $activity->getPaceFormatted(true));
    }

    public function testGetPaceFormattedReturnsNullForInvalidData(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 0.0,
            movingTime: 0
        );

        $this->assertNull($activity->getPaceFormatted());
    }

    public function testIsRun(): void
    {
        $runTypes = ['Run', 'VirtualRun', 'TrailRun'];

        foreach ($runTypes as $type) {
            $activity = new Activity(
                id: 1,
                type: $type,
                name: 'Test',
                startDate: new DateTime(),
                distance: 5000.0,
                movingTime: 1800
            );

            $this->assertTrue($activity->isRun(), "Failed for type: $type");
        }
    }

    public function testIsNotRun(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Ride',
            name: 'Test',
            startDate: new DateTime(),
            distance: 20000.0,
            movingTime: 3600
        );

        $this->assertFalse($activity->isRun());
    }

    public function testIsRide(): void
    {
        $rideTypes = ['Ride', 'VirtualRide', 'MountainBikeRide', 'GravelRide', 'EBikeRide'];

        foreach ($rideTypes as $type) {
            $activity = new Activity(
                id: 1,
                type: $type,
                name: 'Test',
                startDate: new DateTime(),
                distance: 20000.0,
                movingTime: 3600
            );

            $this->assertTrue($activity->isRide(), "Failed for type: $type");
        }
    }

    public function testIsNotRide(): void
    {
        $activity = new Activity(
            id: 1,
            type: 'Run',
            name: 'Test',
            startDate: new DateTime(),
            distance: 5000.0,
            movingTime: 1800
        );

        $this->assertFalse($activity->isRide());
    }

    public function testToArray(): void
    {
        $date = new DateTime('2024-01-15 10:30:00');
        $activity = new Activity(
            id: 123456,
            type: 'Run',
            name: 'Morning Run',
            startDate: $date,
            distance: 5000.0,
            movingTime: 1800,
            averageSpeed: 2.78,
            maxSpeed: 4.0,
            elevationGain: 100.0,
            averageHeartrate: 145.0,
            maxHeartrate: 165.0
        );

        $array = $activity->toArray();

        $this->assertSame(123456, $array['id']);
        $this->assertSame('Run', $array['type']);
        $this->assertSame('Morning Run', $array['name']);
        $this->assertSame('2024-01-15 10:30:00', $array['start_date']);
        $this->assertSame(5000.0, $array['distance']);
        $this->assertSame(1800, $array['moving_time']);
        $this->assertSame(2.78, $array['average_speed']);
        $this->assertSame(4.0, $array['max_speed']);
        $this->assertSame(100.0, $array['elevation_gain']);
        $this->assertSame(145.0, $array['average_heartrate']);
        $this->assertSame(165.0, $array['max_heartrate']);
    }

    public function testToArrayWithNullOptionalFields(): void
    {
        $date = new DateTime('2024-01-15 10:30:00');
        $activity = new Activity(
            id: 123456,
            type: 'Run',
            name: 'Morning Run',
            startDate: $date,
            distance: 5000.0,
            movingTime: 1800
        );

        $array = $activity->toArray();

        $this->assertNull($array['average_speed']);
        $this->assertNull($array['max_speed']);
        $this->assertNull($array['elevation_gain']);
        $this->assertNull($array['average_heartrate']);
        $this->assertNull($array['max_heartrate']);
    }
}
