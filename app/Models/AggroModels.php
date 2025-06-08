<?php

namespace App\Models;

use App\Repositories\ChannelRepository;
use App\Repositories\VideoRepository;
use App\Services\ArchiveService;
use App\Services\ThumbnailService;
use CodeIgniter\Model;

/**
 * All interactions with aggro_* tables.
 */
class AggroModels extends Model
{
    protected $videoRepository;
    protected $channelRepository;
    protected $archiveService;
    protected $thumbnailService;

    public function __construct()
    {
        parent::__construct();
        $this->videoRepository   = new VideoRepository();
        $this->channelRepository = new ChannelRepository();
        $this->archiveService    = new ArchiveService();
        $this->thumbnailService  = new ThumbnailService();
    }

    /**
     * Add video metadata to aggro_videos.
     *
     * @return bool
     *              Video added.
     *
     * @see sendLog()
     */
    public function addVideo(array $video)
    {
        return $this->videoRepository->addVideo($video);
    }

    /**
     * Archive videos older than 31 days by setting archive flag in video table.
     *
     * Write count of archived videos to log.
     *
     * @return bool
     *              Archive complete.
     *
     * @see sendLog()
     */
    public function archiveVideos()
    {
        return $this->archiveService->archiveVideos();
    }

    /**
     * Check thumbnails.
     *
     * @return bool
     *              Thumbnail check complete.
     */
    public function checkThumbs()
    {
        return $this->thumbnailService->checkThumbs();
    }

    /**
     * Check if video exists in video table.
     *
     * @param string $videoid
     *                        Videoid to check.
     *
     * @return bool
     *              Video exists in video table.
     */
    public function checkVideo($videoid)
    {
        return $this->videoRepository->checkVideo($videoid);
    }

    /**
     * Clean thumbnail directory.
     *
     * @return bool
     *              Cleanup complete.
     */
    public function cleanThumbs()
    {
        return $this->thumbnailService->cleanThumbs();
    }

    /**
     * Get list of video channels that haven't been updated within timeframe.
     *
     * @param string $stale
     *                      Max age in minutes.
     * @param string $type
     *                      youtube, vimeo.
     * @param string $limit
     *                      Limit results returned.
     *
     * @return array|false
     *                     Channel data from table or FALSE.
     */
    public function getChannels($stale = '30', $type = 'youtube', $limit = '10')
    {
        return $this->channelRepository->getChannels($stale, $type, $limit);
    }

    /**
     * Get single video.
     *
     * @param string $slug
     *                     Video id.
     *
     * @return array|false
     *                     Video data from table or FALSE.
     */
    public function getVideo($slug)
    {
        return $this->videoRepository->getVideo($slug);
    }

    /**
     * Get all videos.
     *
     * @param string $range
     *                        - Year.
     *                        - Month.
     *                        - Week.
     * @param string $perpage
     *                        Results per page.
     * @param string $offset
     *                        Result starting offset.
     *
     * @return array
     *               Video data from table.
     */
    public function getVideos($range = 'month', $perpage = '10', $offset = '0')
    {
        return $this->videoRepository->getVideos($range, $perpage, $offset);
    }

    /**
     * Get all videos total.
     *
     * @return int
     *             Total number of active videos.
     */
    public function getVideosTotal()
    {
        return $this->videoRepository->getVideosTotal();
    }

    /**
     * Update video source last fetch timestamp.
     *
     * @param string $sourceSlug
     *                           Source slug.
     *
     * @return void
     */
    public function updateChannel($sourceSlug)
    {
        $this->channelRepository->updateChannel($sourceSlug);
    }
}
