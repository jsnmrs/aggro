# ************************************************************
# MySQL dump
# Database: aggro_gator
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table aggro_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `aggro_log`;

CREATE TABLE `aggro_log` (
  `log_id` int(8) NOT NULL AUTO_INCREMENT,
  `log_date` datetime NOT NULL,
  `log_message` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table aggro_sources
# ------------------------------------------------------------

DROP TABLE IF EXISTS `aggro_sources`;

CREATE TABLE `aggro_sources` (
  `source_id` int(6) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) NOT NULL DEFAULT '',
  `source_slug` varchar(255) NOT NULL DEFAULT '',
  `source_channel_id` varchar(255) DEFAULT NULL,
  `source_type` varchar(255) NOT NULL DEFAULT '',
  `source_date_updated` datetime NOT NULL,
  PRIMARY KEY (`source_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table aggro_videos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `aggro_videos`;

CREATE TABLE `aggro_videos` (
  `aggro_id` int(10) NOT NULL AUTO_INCREMENT,
  `aggro_date_added` datetime DEFAULT NULL,
  `aggro_date_updated` datetime DEFAULT NULL,
  `video_id` varchar(25) NOT NULL DEFAULT '',
  `video_plays` int(25) NOT NULL DEFAULT '0',
  `video_title` varchar(255) DEFAULT NULL,
  `video_thumbnail_url` varchar(255) DEFAULT NULL,
  `video_date_uploaded` datetime DEFAULT NULL,
  `video_width` int(6) NOT NULL DEFAULT '0',
  `video_height` int(6) NOT NULL DEFAULT '0',
  `video_aspect_ratio` float NOT NULL DEFAULT '0',
  `video_duration` int(10) NOT NULL DEFAULT '0',
  `video_type` varchar(255) DEFAULT NULL,
  `video_source_id` varchar(255) DEFAULT NULL,
  `video_source_username` varchar(255) DEFAULT NULL,
  `video_source_user_slug` varchar(255) DEFAULT NULL,
  `video_source_url` varchar(255) DEFAULT NULL,
  `flag_archive` int(1) NOT NULL DEFAULT '0',
  `flag_bad` int(1) NOT NULL DEFAULT '0',
  `flag_favorite` int(1) NOT NULL DEFAULT '0',
  `notes` text,
  PRIMARY KEY (`aggro_id`),
  UNIQUE KEY `videoid` (`video_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table news_featured
# ------------------------------------------------------------

DROP TABLE IF EXISTS `news_featured`;

CREATE TABLE `news_featured` (
  `story_id` int(11) NOT NULL AUTO_INCREMENT,
  `story_title` varchar(255) NOT NULL,
  `story_permalink` varchar(255) NOT NULL,
  `story_hash` varchar(255) NOT NULL,
  `story_date` datetime NOT NULL,
  `site_id` int(11) NOT NULL,
  PRIMARY KEY (`story_id`),
  UNIQUE KEY `permalink` (`story_permalink`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `news_featured` WRITE;
/*!40000 ALTER TABLE `news_featured` DISABLE KEYS */;

INSERT INTO `news_featured` (`story_id`, `story_title`, `story_permalink`, `story_hash`, `story_date`, `site_id`)
VALUES
  (1,'BMXFEED: 2020 ROADTRIP – BNGBNGMCR | Ride UK BMX','https://bmxfeed.com/video/NSVIEl6OIzA/','2f69b425dfd72beb8bf5cc7ac1b923cd7b6e4fcb','2020-11-27 13:05:17',1),
  (2,'BMXFEED: WIN THIS $1400 BIKE  PART TWO - NATHAN RIDES IT','https://bmxfeed.com/video/421pYyzGoTU/','3ed5a45f03b34a444f66bbe9e8618f763470093e','2020-11-27 13:05:16',1),
  (3,'BMXFEED: BMX IN A GROCERY STORE - TATE ROSKELLEY - GT','https://bmxfeed.com/video/ly5DAElCAXg/','6d6abad832f1edc6aa0e9f50d16f7b5b1bec19c8','2020-11-27 12:20:17',1);

/*!40000 ALTER TABLE `news_featured` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table news_feeds
# ------------------------------------------------------------

DROP TABLE IF EXISTS `news_feeds`;

CREATE TABLE `news_feeds` (
  `site_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `site_slug` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `site_url` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `site_feed` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `site_category` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `site_date_added` datetime DEFAULT NULL,
  `site_date_updated` datetime DEFAULT NULL,
  `site_date_last_fetch` datetime NOT NULL,
  `site_date_last_post` datetime NOT NULL,
  `flag_featured` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `flag_stream` tinyint(1) NOT NULL DEFAULT '0',
  `flag_spoof` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='bmxfeed feeds table';

LOCK TABLES `news_feeds` WRITE;
/*!40000 ALTER TABLE `news_feeds` DISABLE KEYS */;

INSERT INTO `news_feeds` (`site_id`, `site_name`, `site_slug`, `site_url`, `site_feed`, `site_category`, `site_date_added`, `site_date_updated`, `site_date_last_fetch`, `site_date_last_post`, `flag_featured`, `flag_stream`, `flag_spoof`)
VALUES
  (1,'BMXfeed','bmxfeed','https://bmxfeed.com','https://bmxfeed.com/rss','news','2006-12-24 12:00:00','2006-12-24 12:00:00','2020-11-27 14:31:18','2020-11-27 13:05:17',1,1,0);

/*!40000 ALTER TABLE `news_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `watch`
--

DROP TABLE IF EXISTS `watch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `watch` (
  `watch_id` int NOT NULL AUTO_INCREMENT,
  `video_id` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sortorder` int NOT NULL DEFAULT '0',
  `completed` date NOT NULL,
  PRIMARY KEY (`watch_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `watch`
--

LOCK TABLES `watch` WRITE;
/*!40000 ALTER TABLE `watch` DISABLE KEYS */;
INSERT INTO `watch` VALUES (1,'82538293','Diggest: Joe Rich.',1,'0000-00-00');
/*!40000 ALTER TABLE `watch` ENABLE KEYS */;
UNLOCK TABLES;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
