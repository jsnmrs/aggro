-- MySQL dump 10.13  Distrib 5.6.43, for Linux (x86_64)
--
-- Host: localhost    Database: aggro
-- ------------------------------------------------------
-- Server version	5.6.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aggro_log`
--

DROP TABLE IF EXISTS `aggro_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aggro_log` (
  `log_id` int(8) NOT NULL AUTO_INCREMENT,
  `log_date` datetime NOT NULL,
  `log_message` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aggro_log`
--

LOCK TABLES `aggro_log` WRITE;
/*!40000 ALTER TABLE `aggro_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `aggro_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aggro_sources`
--

DROP TABLE IF EXISTS `aggro_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aggro_sources` (
  `source_id` int(6) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) NOT NULL DEFAULT '',
  `source_slug` varchar(255) NOT NULL DEFAULT '',
  `source_channel_id` varchar(255) DEFAULT NULL,
  `source_type` varchar(255) NOT NULL DEFAULT '',
  `source_date_updated` datetime NOT NULL,
  PRIMARY KEY (`source_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aggro_sources`
--

LOCK TABLES `aggro_sources` WRITE;
/*!40000 ALTER TABLE `aggro_sources` DISABLE KEYS */;
/*!40000 ALTER TABLE `aggro_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aggro_videos`
--

DROP TABLE IF EXISTS `aggro_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aggro_videos`
--

LOCK TABLES `aggro_videos` WRITE;
/*!40000 ALTER TABLE `aggro_videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `aggro_videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_featured`
--

DROP TABLE IF EXISTS `news_featured`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_featured`
--

LOCK TABLES `news_featured` WRITE;
/*!40000 ALTER TABLE `news_featured` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_featured` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_feeds`
--

DROP TABLE IF EXISTS `news_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_feeds`
--

LOCK TABLES `news_feeds` WRITE;
/*!40000 ALTER TABLE `news_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_outgoing`
--

DROP TABLE IF EXISTS `news_outgoing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_outgoing` (
  `outgoing_id` int(9) NOT NULL AUTO_INCREMENT,
  `outgoing_date` datetime NOT NULL,
  `outgoing_link` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `outgoing_text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `outgoing_ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `outgoing_referrer` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `story_hash` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`outgoing_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_outgoing`
--

LOCK TABLES `news_outgoing` WRITE;
/*!40000 ALTER TABLE `news_outgoing` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_outgoing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(8) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(50) NOT NULL DEFAULT '',
  `user_password` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-04-27  0:56:48
