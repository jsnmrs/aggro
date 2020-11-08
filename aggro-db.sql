# ************************************************************
# MySQL dump
#
# MySQL 5.7.28-log
# Database: aggro_gator
# Generation Time: 2020-11-08 13:58:58 +0000
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
  `video_type` varchar(255) DEFAULT NULL,
  `video_source_id` varchar(255) DEFAULT NULL,
  `video_source_username` varchar(255) DEFAULT NULL,
  `video_source_user_slug` varchar(255) DEFAULT NULL,
  `video_source_url` varchar(255) DEFAULT NULL,
  `flag_archive` int(1) NOT NULL DEFAULT '0',
  `flag_bad` int(1) NOT NULL DEFAULT '0',
  `flag_tweet` int(1) NOT NULL DEFAULT '0',
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




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
