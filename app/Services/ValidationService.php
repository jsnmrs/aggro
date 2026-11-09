<?php

namespace App\Services;

class ValidationService
{
    /**
     * Sanitize slug input
     */
    public function sanitizeSlug(string $slug): string
    {
        // Remove any non-alphanumeric characters except hyphens and underscores
        $slug = preg_replace('/[^a-zA-Z0-9\-_]/', '', $slug);

        // Remove multiple consecutive hyphens (but preserve single leading/trailing hyphens)
        return preg_replace('/--+/', '-', $slug);
        // Don't trim hyphens as they may be part of video IDs
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
}
