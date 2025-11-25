<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;

/**
 * Activity Model
 *
 * Represents a Strava activity with key metrics
 */
class Activity
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $name,
        public readonly DateTime $startDate,
        public readonly float $distance,        // meters
        public readonly int $movingTime,        // seconds
        public readonly ?float $averageSpeed = null,    // m/s
        public readonly ?float $maxSpeed = null,        // m/s
        public readonly ?float $elevationGain = null,   // meters
        public readonly ?float $averageHeartrate = null,
        public readonly ?float $maxHeartrate = null,
    ) {}

    /**
     * Create Activity from Strava API response
     *
     * @param array $data Raw activity data from Strava API
     * @return self
     */
    public static function fromStravaApi(array $data): self
    {
        // Strava's start_date_local contains the activity's local date/time in the timezone where it was performed
        // We extract ONLY the date portion (YYYY-MM-DD) and ignore time/timezone completely
        // This ensures an activity logged on a specific calendar date appears on that date, period.
        $startDateStr = $data['start_date_local'] ?? $data['start_date'] ?? 'now';

        // Extract only the date (YYYY-MM-DD) - we only care about the calendar date
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $startDateStr, $matches)) {
            // Create a date-only object by treating it as a naive date (no timezone conversion)
            // We'll format this as Y-m-d for comparisons, so timezone is irrelevant
            $startDate = new DateTime($matches[1]);
        } else {
            // Fallback for unexpected format
            $startDate = new DateTime($startDateStr);
        }

        return new self(
            id: $data['id'],
            type: $data['type'] ?? 'Unknown',
            name: $data['name'] ?? 'Untitled Activity',
            startDate: $startDate,
            distance: (float)($data['distance'] ?? 0),
            movingTime: (int)($data['moving_time'] ?? 0),
            averageSpeed: isset($data['average_speed']) ? (float)$data['average_speed'] : null,
            maxSpeed: isset($data['max_speed']) ? (float)$data['max_speed'] : null,
            elevationGain: isset($data['total_elevation_gain']) ? (float)$data['total_elevation_gain'] : null,
            averageHeartrate: isset($data['average_heartrate']) ? (float)$data['average_heartrate'] : null,
            maxHeartrate: isset($data['max_heartrate']) ? (float)$data['max_heartrate'] : null,
        );
    }

    /**
     * Get distance in kilometers
     *
     * @return float
     */
    public function getDistanceKm(): float
    {
        return $this->distance / 1000;
    }

    /**
     * Get distance in miles
     *
     * @return float
     */
    public function getDistanceMiles(): float
    {
        return $this->distance / 1609.34;
    }

    /**
     * Get moving time in hours
     *
     * @return float
     */
    public function getMovingTimeHours(): float
    {
        return $this->movingTime / 3600;
    }

    /**
     * Get moving time formatted as HH:MM:SS
     *
     * @return string
     */
    public function getMovingTimeFormatted(): string
    {
        $hours = floor($this->movingTime / 3600);
        $minutes = floor(($this->movingTime % 3600) / 60);
        $seconds = $this->movingTime % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get average pace in minutes per kilometer
     *
     * @return float|null
     */
    public function getPacePerKm(): ?float
    {
        if ($this->distance <= 0 || $this->movingTime <= 0) {
            return null;
        }

        $distanceKm = $this->getDistanceKm();
        return $this->movingTime / 60 / $distanceKm; // minutes per km
    }

    /**
     * Get average pace in minutes per mile
     *
     * @return float|null
     */
    public function getPacePerMile(): ?float
    {
        if ($this->distance <= 0 || $this->movingTime <= 0) {
            return null;
        }

        $distanceMiles = $this->getDistanceMiles();
        return $this->movingTime / 60 / $distanceMiles; // minutes per mile
    }

    /**
     * Get pace formatted as MM:SS per unit
     *
     * @param bool $imperial Use miles if true, kilometers if false
     * @return string|null
     */
    public function getPaceFormatted(bool $imperial = false): ?string
    {
        $pace = $imperial ? $this->getPacePerMile() : $this->getPacePerKm();

        if ($pace === null) {
            return null;
        }

        $minutes = floor($pace);
        $seconds = round(($pace - $minutes) * 60);

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Check if activity is a run
     *
     * @return bool
     */
    public function isRun(): bool
    {
        return in_array($this->type, ['Run', 'VirtualRun', 'TrailRun']);
    }

    /**
     * Check if activity is a ride
     *
     * @return bool
     */
    public function isRide(): bool
    {
        return in_array($this->type, ['Ride', 'VirtualRide', 'MountainBikeRide', 'GravelRide', 'EBikeRide']);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'start_date' => $this->startDate->format('Y-m-d H:i:s'),
            'distance' => $this->distance,
            'moving_time' => $this->movingTime,
            'average_speed' => $this->averageSpeed,
            'max_speed' => $this->maxSpeed,
            'elevation_gain' => $this->elevationGain,
            'average_heartrate' => $this->averageHeartrate,
            'max_heartrate' => $this->maxHeartrate,
        ];
    }
}
