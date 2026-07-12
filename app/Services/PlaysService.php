<?php

namespace App\Services;

use App\Models\UtilityModels;
use App\Repositories\VideoRepository;

/**
 * Service for refreshing video play counts from public endpoints.
 */
class PlaysService
{
    protected $videoRepository;
    protected $utilityModel;

    public function __construct(?VideoRepository $videoRepository = null, ?UtilityModels $utilityModel = null)
    {
        $this->videoRepository = $videoRepository ?? new VideoRepository();
        $this->utilityModel    = $utilityModel ?? new UtilityModels();
    }

    /**
     * Refresh play counts for a batch of videos.
     *
     * Videos are processed oldest-refresh-first so the whole table,
     * including archived videos, cycles over time. Requests are spaced
     * out to stay polite to the public endpoints.
     *
     * @return bool
     *              Refresh batch complete.
     *
     * @see sendLog()
     */
    public function refreshPlays()
    {
        helper(['aggro', 'youtube', 'vimeo']);

        $storageConfig = config('Storage');
        $videos        = $this->videoRepository->getVideosForPlaysRefresh($storageConfig->playsBatchSize);
        $updateCount   = 0;

        foreach ($videos as $index => $video) {
            if ($index > 0 && $storageConfig->playsRequestDelay > 0) {
                sleep($storageConfig->playsRequestDelay);
            }

            $httpStatus = null;
            $plays      = $this->fetchPlays($video, $httpStatus);

            if ($plays === false) {
                if ($httpStatus === 404) {
                    $this->videoRepository->flagVideoBad($video->video_id);
                    log_message('warning', 'Flagged video ' . $video->video_id . ' as bad — source returned 404.');

                    continue;
                }

                $this->videoRepository->recordPlaysIssue($video->video_id);

                continue;
            }

            if ($plays === null) {
                $this->videoRepository->stampPlaysChecked($video->video_id);

                continue;
            }

            if ($this->videoRepository->updateVideoPlays($video->video_id, $plays)) {
                $updateCount++;
            }
        }

        $message = $updateCount . ' video play counts updated.';
        $this->utilityModel->sendLog($message);

        return true;
    }

    /**
     * Fetch the current play count for a video from its public endpoint.
     *
     * @param object   $video
     *                              Row with video_id and video_type.
     * @param int|null &$httpStatus
     *                              Optional. Populated with the HTTP response code.
     *
     * @param-out int $httpStatus
     *
     * @return false|int|null
     *                        Play count, null when the source hides stats, or false on fetch failure.
     */
    protected function fetchPlays(object $video, ?int &$httpStatus = null)
    {
        if ($video->video_type === 'vimeo') {
            return $this->normalizePlays(vimeo_get_plays($video->video_id, $httpStatus));
        }

        return $this->normalizePlays(youtube_get_plays($video->video_id, $httpStatus));
    }

    /**
     * Cast a helper play count to int while preserving failure states.
     *
     * @param false|string|null $plays
     *                                 Raw helper return value.
     *
     * @return false|int|null
     *                        Play count, null when the source hides stats, or false on fetch failure.
     */
    private function normalizePlays($plays)
    {
        if ($plays === false || $plays === null) {
            return $plays;
        }

        return (int) $plays;
    }
}
