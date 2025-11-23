<?php

namespace App\Helpers;

class Vite
{
    private static ?array $manifest = null;
    private static string $manifestPath = __DIR__ . '/../../public/build/.vite/manifest.json';

    /**
     * Get the URL for a Vite asset
     */
    public static function asset(string $entry): string
    {
        if (self::$manifest === null) {
            self::loadManifest();
        }

        // In development, return the entry path directly
        if (self::isDevelopment()) {
            return "http://localhost:5173/{$entry}";
        }

        // In production, get the hashed filename from manifest
        if (isset(self::$manifest[$entry])) {
            return '/build/' . self::$manifest[$entry]['file'];
        }

        // Fallback to original path
        return "/build/{$entry}";
    }

    /**
     * Get CSS assets for an entry
     */
    public static function css(string $entry): array
    {
        if (self::$manifest === null) {
            self::loadManifest();
        }

        if (isset(self::$manifest[$entry]['css'])) {
            return array_map(
                fn($file) => '/build/' . $file,
                self::$manifest[$entry]['css']
            );
        }

        return [];
    }

    /**
     * Load the Vite manifest file
     */
    private static function loadManifest(): void
    {
        if (file_exists(self::$manifestPath)) {
            self::$manifest = json_decode(file_get_contents(self::$manifestPath), true);
        } else {
            self::$manifest = [];
        }
    }

    /**
     * Check if we're in development mode
     */
    private static function isDevelopment(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'development') === 'development'
            && file_exists(__DIR__ . '/../../public/build/.vite/manifest.json') === false;
    }
}
