<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * Format description text to preserve line breaks and basic formatting
     */
    public static function formatDescription($text)
    {
        if (empty($text)) {
            return '';
        }

        // Convert line breaks to HTML
        $text = nl2br(htmlspecialchars($text));

        // Convert basic markdown-like syntax
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

        // Convert bullet points
        $text = preg_replace('/^•\s*(.*?)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);

        // Wrap consecutive list items in ul tags
        $text = preg_replace('/(<li>.*?<\/li>)+/s', '<ul>$0</ul>', $text);

        return $text;
    }

    /**
     * Truncate text with ellipsis
     */
    public static function truncate($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Get first paragraph of description
     */
    public static function getFirstParagraph($text)
    {
        if (empty($text)) {
            return '';
        }

        $lines = explode("\n", $text);
        $firstLine = trim($lines[0]);

        if (empty($firstLine)) {
            // Find first non-empty line
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $firstLine = $line;
                    break;
                }
            }
        }

        return self::truncate($firstLine, 150);
    }
}
