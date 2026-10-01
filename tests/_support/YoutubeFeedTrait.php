<?php

namespace Tests\Support;

use SimplePie\SimplePie;

/**
 * Builds YouTube channel feeds from inline Atom, so tests can work with
 * real SimplePie items without fetching anything.
 */
trait YoutubeFeedTrait
{
    /**
     * Parse an Atom feed holding the given entries.
     *
     * @param string ...$entries Child elements for each entry.
     */
    private function makeFeed(string ...$entries): SimplePie
    {
        $feed = new SimplePie();
        $feed->enable_cache(false);
        $feed->set_raw_data(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:yt="http://www.youtube.com/xml/schemas/2015"'
            . ' xmlns:media="http://search.yahoo.com/mrss/"><title>Channel</title>'
            . '<entry>' . implode('</entry><entry>', $entries) . '</entry></feed>',
        );
        $feed->init();

        return $feed;
    }

    /**
     * Parse an Atom entry with the given XML-encoded title into a feed item.
     *
     * @param string $entryXml Optional. Further child elements for the entry.
     */
    private function makeFeedItem(string $xmlTitle, string $entryXml = ''): object
    {
        return $this->makeFeed('<title>' . $xmlTitle . '</title>' . $entryXml)->get_item(0);
    }

    /**
     * Build the child elements YouTube sends with a video feed entry.
     */
    private function videoEntryXml(string $videoId = 'aggroTest01', bool $withThumbnail = true): string
    {
        $thumbnail = $withThumbnail
            ? '<media:thumbnail url="https://i1.ytimg.com/vi/' . $videoId . '/hqdefault.jpg" width="480" height="360"/>'
            : '';

        return '<yt:videoId>' . $videoId . '</yt:videoId>'
            . '<yt:channelId>UCaggroTestChannel</yt:channelId>'
            . '<author><name>Test Rider</name><uri>https://www.youtube.com/channel/UCaggroTestChannel</uri></author>'
            . '<published>2020-01-15T12:00:00+00:00</published>'
            . '<media:group>' . $thumbnail
            . '<media:community><media:statistics views="12345"/></media:community>'
            . '</media:group>';
    }
}
