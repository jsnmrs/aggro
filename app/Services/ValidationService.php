<?php

namespace App\Services;

class ValidationService
{
    /**
     * Sanitize slug input
     */
    public function sanitizeSlug(string $slug): string
    {
        // Remove any non-alphanumeric characters except hyphens
        $slug = preg_replace('/[^a-zA-Z0-9\-]/', '', $slug);

        // Remove multiple consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Trim hyphens from start and end
        return trim($slug, '-');
    }

    /**
     * Validate and sanitize video ID
     */
    public function validateVideoId(string $videoId, string $platform): ?string
    {
        switch ($platform) {
            case 'youtube':
                // YouTube IDs are 11 characters, alphanumeric with - and _
                if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
                    return $videoId;
                }
                break;

            case 'vimeo':
                // Vimeo IDs are numeric
                if (preg_match('/^\d+$/', $videoId)) {
                    return $videoId;
                }
                break;
        }

        return null;
    }

    /**
     * Validate gate parameter
     */
    public function validateGateKey(?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        // Only allow alphanumeric and specific characters
        return preg_match('/^[a-zA-Z0-9\-_]+$/', $key) === 1;
    }

    /**
     * Sanitize integer input
     *
     * @param mixed $value
     */
    public function sanitizeInt($value, int $min = 0, ?int $max = null): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        if ($value === false) {
            return $min;
        }

        if ($value < $min) {
            return $min;
        }

        if ($max !== null && $value > $max) {
            return $max;
        }

        return $value;
    }
}
