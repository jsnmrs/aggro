<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Exception;

/**
 * @internal
 */
final class ViewHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('view');
    }

    public function testHumanizeTimeMethodExists(): void
    {
        $this->assertTrue(function_exists('humanizeTime'));
    }

    public function testHumanizeTimeWithValidDate(): void
    {
        $date     = '2024-01-01 12:00:00';
        $timezone = 'America/New_York';

        $result = humanizeTime($date, $timezone);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testHumanizeTimeWithRecentDate(): void
    {
        $date     = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $timezone = 'UTC';

        $result = humanizeTime($date, $timezone);

        $this->assertIsString($result);
        $this->assertStringContainsString('ago', $result);
    }

    public function testHumanizeTimeWithOldDate(): void
    {
        $date     = '2020-01-01 12:00:00';
        $timezone = 'UTC';

        $result = humanizeTime($date, $timezone);

        $this->assertIsString($result);
        // Should contain either "ago" or formatted date
        $this->assertNotEmpty($result);
    }

    public function testHumanizeTimeWithDifferentTimezones(): void
    {
        $date      = '2024-01-01 12:00:00';
        $timezones = [
            'UTC',
            'America/New_York',
            'Europe/London',
            'Asia/Tokyo',
        ];

        foreach ($timezones as $timezone) {
            $result = humanizeTime($date, $timezone);
            $this->assertIsString($result);
            $this->assertNotEmpty($result);
        }
    }

    public function testHumanizeTimeWithInvalidDateThrowsException(): void
    {
        $this->expectException(Exception::class);
        humanizeTime('not-a-date', 'UTC');
    }

    public function testHumanizeTimeWithInvalidTimezoneThrowsException(): void
    {
        $this->expectException(Exception::class);
        humanizeTime('2024-01-01 12:00:00', 'Invalid/Timezone');
    }

    public function testTimeAgoRendersTimeElementWithOffset(): void
    {
        $result = timeAgo('2024-07-01 12:00:00', 'America/New_York', 'ago--muted');

        $this->assertStringStartsWith('<time class="ago--muted" datetime="2024-07-01T12:00:00-04:00">', $result);
        $this->assertStringEndsWith('</time>', $result);
        $this->assertStringContainsString('ago', $result);
    }

    public function testTimeAgoOmitsClassWhenNotGiven(): void
    {
        $result = timeAgo('2024-01-01 12:00:00', 'UTC');

        $this->assertStringStartsWith('<time datetime="2024-01-01T12:00:00+00:00">', $result);
        $this->assertStringNotContainsString('class=', $result);
    }

    public function testTimeAgoEscapesClass(): void
    {
        $result = timeAgo('2024-01-01 12:00:00', 'UTC', 'ago" onclick="x');

        $this->assertStringNotContainsString('onclick="x', $result);
        $this->assertStringContainsString('&quot;', $result);
    }

    public function testTimeAgoWithInvalidDateThrowsException(): void
    {
        $this->expectException(Exception::class);
        timeAgo('not-a-date', 'UTC');
    }

    public function testDisplayStoryMethodExists(): void
    {
        $this->assertTrue(function_exists('displayStory'));
    }

    public function testDisplayStoryWithValidRow(): void
    {
        $row = [
            1 => [
                'story_title'     => 'Test Story Title',
                'story_permalink' => 'https://example.com/story',
                'story_hash'      => 'abcd1234',
            ],
        ];
        $storyNum = 1;

        $result = displayStory($row, $storyNum);

        $this->assertIsString($result);
        $this->assertStringContainsString('Test Story Title', $result);
        $this->assertStringContainsString('https://example.com/story', $result);
    }

    public function testDisplayStoryWithDifferentStoryNumbers(): void
    {
        $row = [
            1 => ['story_title' => 'Story 1', 'story_permalink' => 'https://example.com/1', 'story_hash' => 'hash1'],
            5 => ['story_title' => 'Story 5', 'story_permalink' => 'https://example.com/5', 'story_hash' => 'hash5'],
        ];

        // Test existing story
        $result1 = displayStory($row, 1);
        $this->assertIsString($result1);
        $this->assertStringContainsString('Story 1', $result1);

        // Test non-existing story
        $result2 = displayStory($row, 10);
        $this->assertSame('', $result2);
    }

    public function testDisplayStoryHandlesSpecialCharacters(): void
    {
        $row = [
            1 => [
                'story_title'     => 'Story with "quotes" & <html>',
                'story_permalink' => 'https://example.com/story?param=value&other=test',
                'story_hash'      => 'hash123',
            ],
        ];

        $result = displayStory($row, 1);

        $this->assertIsString($result);
        // Should properly escape HTML/XML characters
        $this->assertStringContainsString('&quot;', $result);
        $this->assertStringContainsString('&lt;', $result);
    }

    public function testDisplayStoryWithEmptyTitle(): void
    {
        $row = [
            'site_name' => 'Example Site',
            1           => [
                'story_title'     => '',
                'story_permalink' => 'https://example.com/story',
                'story_hash'      => 'hash123',
            ],
        ];

        $result = displayStory($row, 1);

        $this->assertIsString($result);
        $this->assertStringContainsString('>Untitled post<span class="visually-hidden"> on Example Site</span></a>', $result);
        $this->assertStringNotContainsString('[missing title]', $result);
    }

    public function testDisplayStoryWithMissingTitle(): void
    {
        $row = [
            1 => [
                'story_permalink' => 'https://example.com/story',
                'story_hash'      => 'hash123',
            ],
        ];

        $result = displayStory($row, 1);

        $this->assertIsString($result);
        $this->assertStringContainsString('>Untitled post</a>', $result);
    }

    public function testStoryTitleEscapesTitle(): void
    {
        $this->assertSame('S&amp;M &quot;Bikes&quot;', storyTitle('S&M "Bikes"', 'Example Site'));
    }

    public function testStoryTitleFallbackNamesSite(): void
    {
        $this->assertSame('Untitled post<span class="visually-hidden"> on S&amp;M</span>', storyTitle('', 'S&M'));
        $this->assertSame('Untitled post<span class="visually-hidden"> on S&amp;M</span>', storyTitle(null, 'S&M'));
    }

    public function testStoryTitleFallbackWithoutSite(): void
    {
        $this->assertSame('Untitled post', storyTitle('', null));
        $this->assertSame('Untitled post', storyTitle(null, ''));
    }

    public function testVideoTitleEscapesTitle(): void
    {
        $this->assertSame('S&amp;M &quot;Game&quot; &lt;of&gt; Bike', videoTitle('S&M "Game" <of> Bike', 'Example Channel'));
    }

    public function testVideoTitleFallbackNamesSource(): void
    {
        $this->assertSame('Untitled video from S&amp;M', videoTitle('', 'S&M'));
        $this->assertSame('Untitled video from S&amp;M', videoTitle(null, 'S&M'));
    }

    public function testVideoTitleFallbackWithoutSource(): void
    {
        $this->assertSame('Untitled video', videoTitle('', null));
        $this->assertSame('Untitled video', videoTitle(null, ''));
    }

    public function testDisplayStoryWithSpecialStoryKey(): void
    {
        $row = [];

        $result = displayStory($row, 'story1');

        $this->assertSame('<li>No recent posts</li>', $result);
    }

    public function testAllFunctionsExist(): void
    {
        $expectedFunctions = [
            'humanizeTime',
            'timeAgo',
            'storyTitle',
            'videoTitle',
            'displayStory',
        ];

        foreach ($expectedFunctions as $function) {
            $this->assertTrue(function_exists($function), "Function {$function} does not exist");
        }
    }

    public function testFunctionReturnTypes(): void
    {
        // Test basic return types
        $result1 = humanizeTime('2024-01-01 12:00:00', 'UTC');
        $this->assertIsString($result1);

        $row     = [1 => ['story_title' => 'Test', 'story_permalink' => 'https://example.com', 'story_hash' => 'hash']];
        $result2 = displayStory($row, 1);
        $this->assertIsString($result2);
    }

    public function testHumanizeTimeEdgeCases(): void
    {
        // Test with future date
        $futureDate = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $result     = humanizeTime($futureDate, 'UTC');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);

        // Test with date at midnight
        $midnightDate = date('Y-m-d') . ' 00:00:00';
        $result       = humanizeTime($midnightDate, 'UTC');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}
