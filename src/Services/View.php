<?php

declare(strict_types=1);

namespace App\Services;

class View
{
    private static string $viewsPath = __DIR__ . '/../../views';

    public static function render(string $view, array $data = []): string
    {
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Load the view file
        $viewFile = self::$viewsPath . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        // Capture the view content
        $content = self::renderPartial($viewFile, $data);

        // If there's a layout, wrap the content
        if (isset($data['layout'])) {
            $layoutFile = self::$viewsPath . '/layouts/' . $data['layout'] . '.php';
            if (file_exists($layoutFile)) {
                $data['content'] = $content;
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }

        return ob_get_clean();
    }

    private static function renderPartial(string $file, array $data = []): string
    {
        extract($data);
        ob_start();
        include $file;
        return ob_get_clean();
    }
}
