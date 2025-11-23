<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use DateTime;

/**
 * ActivityService
 *
 * Handles fetching and processing activities from Strava
 */
class ActivityService
{
    private StravaClient $stravaClient;

    public function __construct()
    {
        $this->stravaClient = new StravaClient();
    }

    /**
     * Fetch activities for a date range
     *
     * @param string $accessToken
     * @param DateTime|null $after Activities after this date
     * @param DateTime|null $before Activities before this date
     * @param int $maxActivities Maximum number of activities to fetch
     * @return Activity[]
     */
    public function fetchActivities(
        string $accessToken,
        ?DateTime $after = null,
        ?DateTime $before = null,
        int $maxActivities = 200
    ): array {
        $activities = [];
        $page = 1;
        $perPage = 30;

        while (count($activities) < $maxActivities) {
            $response = $this->stravaClient->getActivities($accessToken, $page, $perPage);

            // No more activities
            if ($response === null || empty($response)) {
                Logger::info('No more activities from API', ['page' => $page]);
                break;
            }

            Logger::info('Fetched activities from API', ['page' => $page, 'count' => count($response)]);

            // Parse activities
            foreach ($response as $activityData) {
                $activity = Activity::fromStravaApi($activityData);

                // Apply date filters
                if ($after !== null && $activity->startDate < $after) {
                    Logger::info('Activity filtered out (too old)', ['activity_date' => $activity->startDate->format('Y-m-d'), 'after_date' => $after->format('Y-m-d')]);
                    continue;
                }

                if ($before !== null && $activity->startDate > $before) {
                    Logger::info('Activity filtered out (too new)', ['activity_date' => $activity->startDate->format('Y-m-d'), 'before_date' => $before->format('Y-m-d')]);
                    continue;
                }

                $activities[] = $activity;

                // Stop if we've reached max
                if (count($activities) >= $maxActivities) {
                    break 2;
                }
            }

            // If we got fewer than perPage, we've reached the end
            if (count($response) < $perPage) {
                break;
            }

            $page++;
        }

        Logger::info('Fetched activities', [
            'count' => count($activities),
            'pages' => $page,
        ]);

        return $activities;
    }

    /**
     * Fetch recent activities (last 30 days)
     *
     * @param string $accessToken
     * @return Activity[]
     */
    public function fetchRecentActivities(string $accessToken): array
    {
        $after = new DateTime('-30 days');
        return $this->fetchActivities($accessToken, $after);
    }

    /**
     * Group activities by type
     *
     * @param Activity[] $activities
     * @return array<string, Activity[]>
     */
    public function groupByType(array $activities): array
    {
        $grouped = [];

        foreach ($activities as $activity) {
            $type = $activity->type;
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $activity;
        }

        return $grouped;
    }

    /**
     * Get activity counts by type
     *
     * @param Activity[] $activities
     * @return array<string, int>
     */
    public function getCountsByType(array $activities): array
    {
        $counts = [];

        foreach ($activities as $activity) {
            $type = $activity->type;
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        // Sort by count descending
        arsort($counts);

        return $counts;
    }

    /**
     * Get total moving time by type
     *
     * @param Activity[] $activities
     * @return array<string, int> Moving time in seconds
     */
    public function getMovingTimeByType(array $activities): array
    {
        $times = [];

        foreach ($activities as $activity) {
            $type = $activity->type;
            $times[$type] = ($times[$type] ?? 0) + $activity->movingTime;
        }

        // Sort by time descending
        arsort($times);

        return $times;
    }

    /**
     * Get total distance by type
     *
     * @param Activity[] $activities
     * @return array<string, float> Distance in meters
     */
    public function getDistanceByType(array $activities): array
    {
        $distances = [];

        foreach ($activities as $activity) {
            $type = $activity->type;
            $distances[$type] = ($distances[$type] ?? 0) + $activity->distance;
        }

        // Sort by distance descending
        arsort($distances);

        return $distances;
    }
}
