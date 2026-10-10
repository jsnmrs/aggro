-- Fixture dump for CI and fresh local installs. Regenerate with: ddev fixture


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `aggro_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aggro_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_date` datetime NOT NULL,
  `log_message` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1669037 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `watch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `watch` (
  `watch_id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` varchar(25) NOT NULL,
  `notes` text NOT NULL,
  `sortorder` int(11) NOT NULL DEFAULT 0,
  `completed` date NOT NULL,
  PRIMARY KEY (`watch_id`),
  KEY `fk_watch_video` (`video_id`),
  CONSTRAINT `fk_watch_video` FOREIGN KEY (`video_id`) REFERENCES `aggro_videos` (`video_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `aggro_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aggro_sources` (
  `source_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) NOT NULL DEFAULT '',
  `source_slug` varchar(255) NOT NULL DEFAULT '',
  `source_channel_id` varchar(255) DEFAULT NULL,
  `source_type` varchar(255) NOT NULL DEFAULT '',
  `source_date_updated` datetime NOT NULL,
  `source_fail_count` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`source_id`),
  KEY `idx_source_type_date` (`source_type`,`source_date_updated`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `aggro_sources` WRITE;
/*!40000 ALTER TABLE `aggro_sources` DISABLE KEYS */;
INSERT INTO `aggro_sources` VALUES (5,'YouTube BSD','yt-bsd','UCG89NxV6p_7UieCd6EPY75Q','youtube','2026-10-09 18:25:11',0);
INSERT INTO `aggro_sources` VALUES (6,'YouTube Fit','yt-fit','UCEQr272sFLulvnv4iV06x_A','youtube','2026-10-09 18:25:12',0);
INSERT INTO `aggro_sources` VALUES (7,'YouTube Subrosa','yt-subrosa','UC1mS5upidopyedyx-PVOKAw','youtube','2026-10-09 18:45:13',0);
INSERT INTO `aggro_sources` VALUES (8,'YouTube Cinema','yt-cinema','UCNLec6yKTHYI0IxF1dV79ww','youtube','2026-10-09 18:15:10',0);
INSERT INTO `aggro_sources` VALUES (9,'YouTube Shadow','yt-shadow','UCJ0b_1_El3khcbVAYfpQncQ','youtube','2026-10-09 18:00:11',0);
INSERT INTO `aggro_sources` VALUES (10,'YouTube Eclat','yt-eclat','UCqAboz3vCG5TdLRiyTtcXXA','youtube','2026-10-09 18:50:12',0);
INSERT INTO `aggro_sources` VALUES (11,'YouTube Federal','yt-federal','UCg-C5SGBn1qxMlxWbJWLQrg','youtube','2026-10-09 18:59:08',0);
INSERT INTO `aggro_sources` VALUES (12,'YouTube Animal','yt-animal','UCr60e2IA3bcsUwIVi8XokBA','youtube','2026-10-09 17:40:09',0);
INSERT INTO `aggro_sources` VALUES (13,'YouTube Props','yt-props','UClzXqNF98-g5CuivuW53hbQ','youtube','2026-10-09 17:55:09',0);
INSERT INTO `aggro_sources` VALUES (14,'YouTube SandM','yt-sandm','UCufV3snTuldPBVIq9UtL2ng','youtube','2026-10-09 18:30:12',0);
INSERT INTO `aggro_sources` VALUES (15,'YouTube Woozy','yt-woozy','UCbX0ZHJ10u3HUNMb9x9JxYw','youtube','2026-10-09 18:30:12',0);
INSERT INTO `aggro_sources` VALUES (16,'YouTube RideBMX','yt-ridebmx','UCdJBLqPpsyNSPmAhVmD3HSg','youtube','2026-10-09 17:50:09',0);
INSERT INTO `aggro_sources` VALUES (17,'YouTube VitalBMX','yt-vitalbmx','UCCCTVDXsyHLCjjOJQplirSA','youtube','2026-10-09 18:35:11',0);
INSERT INTO `aggro_sources` VALUES (18,'YouTube Colony','yt-colony','UC2bGSgCvxF2dM9Yig3z2gxA','youtube','2026-10-09 17:40:09',0);
INSERT INTO `aggro_sources` VALUES (19,'YouTube Volume','yt-volume','UCpTs-33BH4YnCSTCUBlLidw','youtube','2026-10-09 17:55:09',0);
INSERT INTO `aggro_sources` VALUES (20,'YouTube FBM','yt-fbm','UCyjH_Bmb0B19zoG_uMNbsrg','youtube','2026-10-09 17:45:10',0);
INSERT INTO `aggro_sources` VALUES (21,'YouTube Kink','yt-kink','UCTcAhfF9BLSDoJawNXZ7t-Q','youtube','2026-10-09 18:10:10',0);
INSERT INTO `aggro_sources` VALUES (22,'YouTube Odyssey','yt-odyssey','UCJHbVOD6qHuwkTth4Nn38PQ','youtube','2026-10-09 18:20:10',0);
INSERT INTO `aggro_sources` VALUES (23,'YouTube United','yt-united','UCFnp89P-9c8iRr2qjoOq-Lw','youtube','2026-10-09 18:20:11',0);
INSERT INTO `aggro_sources` VALUES (24,'YouTube Tim Knoll','yt-timknoll','UC0pHfUqcepR5Nq7FRZWLicg','youtube','2026-10-09 18:40:13',0);
INSERT INTO `aggro_sources` VALUES (25,'YouTube DUB','yt-dub','UC1hhgHZPmpSRHJxdMHCqneQ','youtube','2026-10-09 18:40:13',0);
INSERT INTO `aggro_sources` VALUES (26,'Vimeo Diggest','vimeo-diggest','thediggest','vimeo','2026-10-09 18:59:03',0);
INSERT INTO `aggro_sources` VALUES (28,'YouTube BMX Union','yt-bmxunion','UCHLRliWm-dlLpD_16dKrzRg','youtube','2026-10-09 18:30:12',0);
INSERT INTO `aggro_sources` VALUES (29,'YouTube Dig','yt-dig','UCuFLeyZaC1yMXPjKafghtIw','youtube','2026-10-09 18:00:11',0);
INSERT INTO `aggro_sources` VALUES (30,'YouTube Merritt','yt-merritt','UCWWCwqQj0MIGZahGrOo4j0Q','youtube','2026-10-09 18:40:13',0);
INSERT INTO `aggro_sources` VALUES (31,'YouTube Profile','yt-profile','UC1K6CuWe6lUj_ikgjwEcKTA','youtube','2026-10-09 18:25:11',0);
INSERT INTO `aggro_sources` VALUES (32,'YouTube Madera','yt-madera','UCo5P_VcTbZqz4AdGsMRWNtg','youtube','2026-10-09 18:00:11',0);
INSERT INTO `aggro_sources` VALUES (33,'YouTube Scotty Cramner','yt-scottycramner','UCxS2lX7728bTnmK1t21bYlA','youtube','2026-10-09 18:40:13',0);
INSERT INTO `aggro_sources` VALUES (34,'YouTube Empire','yt-empire','UCbG3Z6xacVvGczFaXcDvB2A','youtube','2026-10-09 18:25:11',0);
INSERT INTO `aggro_sources` VALUES (35,'YouTube RideUK','yt-rideuk','UCs0nji0Wy6WRV630Dzxhxzw','youtube','2026-10-09 18:40:14',0);
INSERT INTO `aggro_sources` VALUES (36,'YouTube Snakebite','yt-snakebite','UC8npzcQ9nw14vODWm33heHw','youtube','2026-10-09 17:55:10',0);
INSERT INTO `aggro_sources` VALUES (37,'Vimeo Neil Waddington','vimeo-neilwadd','user13129876','vimeo','2026-10-09 18:59:05',0);
INSERT INTO `aggro_sources` VALUES (38,'YouTube Demolition','yt-demolition','UCBN0xKuGw_ee1Hap7K0-DXQ','youtube','2026-10-09 18:25:11',0);
INSERT INTO `aggro_sources` VALUES (39,'YouTube Trey Jones','yt-treyjones','UCr6LYZGbXGyUQKSciAcA22g','youtube','2026-10-09 18:45:13',0);
INSERT INTO `aggro_sources` VALUES (40,'YouTube Freedom','yt-freedom','UCyPFwLmziUkiw4gw9QkI8_w','youtube','2026-10-09 18:30:12',0);
INSERT INTO `aggro_sources` VALUES (41,'YouTube Fly Bikes','yt-flybikes','UC9h7gL_K1ntBr68gDkRcf1Q','youtube','2026-10-09 18:35:11',0);
INSERT INTO `aggro_sources` VALUES (42,'YouTube Sunday','yt-sunday','UCsMczRyPB91lkNug1-2vGcQ','youtube','2026-10-09 18:35:11',0);
INSERT INTO `aggro_sources` VALUES (43,'YouTube Cult','yt-cult','UCUXFXlTfxxykngI4m_LmYWg','youtube','2026-10-09 17:45:09',0);
INSERT INTO `aggro_sources` VALUES (44,'YouTube Haro','yt-haro','UCaJlQVNBNDcK6y_xzyz2a1A','youtube','2026-10-09 18:45:14',0);
INSERT INTO `aggro_sources` VALUES (45,'Vimeo Heresy','vimeo-heresy','heresybmx','vimeo','2026-10-09 18:59:07',0);
INSERT INTO `aggro_sources` VALUES (46,'YouTube Ryan Howard','yt-ryanhoward','UCuWckoNW0j7QwEABiLU8P9g','youtube','2026-10-09 18:45:14',0);
INSERT INTO `aggro_sources` VALUES (47,'YouTube Source','yt-source','UCr1vAo8ZyBFeKTpbLyVDr9g','youtube','2026-10-09 18:50:12',0);
INSERT INTO `aggro_sources` VALUES (48,'YouTube Our BMX','yt-ourbmx','UCuSZUuRMLzOP0I6ItdA6uAQ','youtube','2026-10-09 17:45:10',0);
INSERT INTO `aggro_sources` VALUES (49,'YouTube Studies Club','yt-studies-club','UCG_-HPmpYbR_MBQTEme4jLQ','youtube','2026-10-09 18:50:12',0);
INSERT INTO `aggro_sources` VALUES (50,'YouTube Palaver','yt-palaver','UCByAPnqyK4xaHCTz9nDlnrg','youtube','2026-10-09 18:59:08',0);
INSERT INTO `aggro_sources` VALUES (51,'YouTube Taj','yt-taj','UCjmNysPmNS_g3_RzPt2e25A','youtube','2026-10-09 17:50:09',0);
INSERT INTO `aggro_sources` VALUES (53,'YouTube Burn Slow','yt-burnslow','UCQJ6hAiXPUrhSGZZ87cuTbQ','youtube','2026-10-09 18:05:10',0);
INSERT INTO `aggro_sources` VALUES (54,'YouTube Pusher','yt-pusher','UC_iqEilYDFna3H1FARgARvg','youtube','2026-10-09 17:50:09',0);
INSERT INTO `aggro_sources` VALUES (55,'YouTube Fast and Loose','yet-fastloose','UCfV0hrErcW9z2R4wyatWy1w','youtube','2026-10-09 17:50:09',0);
INSERT INTO `aggro_sources` VALUES (57,'YouTube GT','yt-gt','UCUHYVA13z3SiBSi_u4p3hqQ','youtube','2026-10-09 17:50:10',0);
INSERT INTO `aggro_sources` VALUES (58,'YouTube Mavro','yt-mavro','UCW9yPSTV289Qpf-kDKFQIVQ','youtube','2026-10-09 17:55:09',0);
INSERT INTO `aggro_sources` VALUES (59,'YouTube Vans BMX','yt-vans-bmx','PL7F9E124C9CC70619','youtube','2026-10-09 18:00:10',0);
INSERT INTO `aggro_sources` VALUES (60,'YouTube Monster BMX','yt-monster-bmx','PL044C788448C3B3FC','youtube','2026-10-09 18:35:10',0);
INSERT INTO `aggro_sources` VALUES (61,'YouTube We The People','yt-wtp','UCkJ8F2VXvwJzsg-NkFg4f2g','youtube','2026-10-09 18:59:08',0);
INSERT INTO `aggro_sources` VALUES (62,'YouTube Tree','yt-tree','UCvVA6U1-A4TO0hBzHBdAzuw','youtube','2026-10-09 18:05:10',0);
INSERT INTO `aggro_sources` VALUES (63,'YouTube Powers BMX','yt-powers','UCOXspPzdIOXSmOp-b0unB3A','youtube','2026-10-09 18:10:10',0);
INSERT INTO `aggro_sources` VALUES (64,'YouTube Primo','yt-primo','UCEh_E7g7isCtH2Jht0SbDPQ','youtube','2026-10-09 17:55:09',0);
INSERT INTO `aggro_sources` VALUES (65,'YouTube Hyper','yt-hyper','UCKe03vm0ChdjRhkLopPpnrA','youtube','2026-10-09 18:00:10',0);
INSERT INTO `aggro_sources` VALUES (66,'YouTube Relic','yt-relic','UC4KSH8nZqojmbVuEjUFzLgw','youtube','2026-10-09 18:10:10',0);
INSERT INTO `aggro_sources` VALUES (67,'YouTube Endless','yt-endless','UCnJGOBDBLM1iNfyaEzgGvYQ','youtube','2026-10-09 18:35:11',0);
INSERT INTO `aggro_sources` VALUES (68,'YouTube Fiend','yt-fiend','UCk0J3k5HbHJ-GP0DZwmz6UA','youtube','2026-10-09 18:45:13',0);
INSERT INTO `aggro_sources` VALUES (69,'YouTube USL','yt-usl','UCVtdR0HHu4j2dSOtHgYKdvQ','youtube','2026-10-09 18:05:09',0);
INSERT INTO `aggro_sources` VALUES (70,'YouTube Street Watch','yt-streetwatch','UCvva-0RN6JYpC2cogLF9svg','youtube','2026-10-09 18:50:12',0);
INSERT INTO `aggro_sources` VALUES (71,'YouTube Brakeless TV','yt-brakelesstv','UC4TZ2hTU2bsepM47pui7vVw','youtube','2026-10-09 18:50:12',0);
INSERT INTO `aggro_sources` VALUES (72,'YouTube Jungle Workshop','yt-jungleworkshop','UCBfsEs7hFXC1W7pajLmQXtg','youtube','2026-10-09 18:10:10',0);
INSERT INTO `aggro_sources` VALUES (74,'YouTube Dennis Enarson','yt-enarson','UCcNSdIT5yN5OXkoLO2NIs7A','youtube','2026-10-09 18:05:10',0);
INSERT INTO `aggro_sources` VALUES (76,'YouTube Yawn','yt-yawn','UCIwwoxrAunuqk7WBeSsdolQ','youtube','2026-10-09 18:15:10',0);
INSERT INTO `aggro_sources` VALUES (77,'YouTube Kevin Peraza','yt-peraza','UChn3aMcSR2iDrJnY0txE2-A','youtube','2026-10-09 18:15:10',0);
INSERT INTO `aggro_sources` VALUES (78,'YouTube Calvin Kosovich','yt-kosovich','UCTS7DUd1ggDmA64AyWWVYFw','youtube','2026-10-09 18:05:09',0);
INSERT INTO `aggro_sources` VALUES (79,'YouTube Boddy Kanode','yt-kanode','UCOPLpJqkOBVw2oqD68dFHbQ','youtube','2026-10-09 18:15:10',0);
INSERT INTO `aggro_sources` VALUES (80,'YouTube Grant Castelluzzo','yt-grant-c','UCv-eFgHL_hcOg7xYSc3O0iA','youtube','2026-10-09 18:15:11',0);
INSERT INTO `aggro_sources` VALUES (81,'YouTube War Party','yt-warparty','UCf9Krg4kLndrhnZVmoLq_mA','youtube','2026-10-09 18:20:10',0);
INSERT INTO `aggro_sources` VALUES (82,'YouTube Woodward BMX','yt-woodward','PL66B0A48E4E3E6DE1','youtube','2026-10-09 18:20:11',0);
INSERT INTO `aggro_sources` VALUES (83,'YouTube LUX','yt-lux','UCpLJVzG2_I68lX855M_7Eqg','youtube','2026-10-09 18:59:08',0);
INSERT INTO `aggro_sources` VALUES (85,'YouTube Dan Foley','yt-dan-foley','UCsK-Dbcs3dPhqsshvU-Ir5w','youtube','2026-10-09 18:59:08',0);
INSERT INTO `aggro_sources` VALUES (86,'YouTube Stranger','yt-stranger','UCwFISG7xz0QbiGFzwUllyEw','youtube','2026-10-09 18:30:12',0);
INSERT INTO `aggro_sources` VALUES (87,'YouTube 90 East','yt-90-east','UCFtnn5hgMqafvoZHJgp4Uqw','youtube','2026-10-09 17:45:09',0);
INSERT INTO `aggro_sources` VALUES (88,'YouTube Steve Crandall','yt-crandal','UCFuvMlKbmYdySCUjWo36fZA','youtube','2026-10-09 18:20:11',0);
INSERT INTO `aggro_sources` VALUES (92,'YouTube Terrible One','yt-t1','UCqm9K3EEV3QL0U8ZnWu8ggQ','youtube','2026-10-09 18:10:09',0);
INSERT INTO `aggro_sources` VALUES (93,'YouTube Frequentleigh','yt-leigh','UCycyysxUtRm2YGNUQJKuesQ','youtube','2026-10-09 17:45:09',0);
/*!40000 ALTER TABLE `aggro_sources` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026-02-10-200000','App\\Database\\Migrations\\AddThumbnailIssueCount','default','App',1770767546,1);
INSERT INTO `migrations` VALUES (2,'2026-07-07-120000','App\\Database\\Migrations\\AddPlaysRefreshColumns','default','App',1783469769,2);
INSERT INTO `migrations` VALUES (3,'2026-08-30-120000','App\\Database\\Migrations\\AddDurationIssueCount','default','App',1788118339,3);
INSERT INTO `migrations` VALUES (4,'2026-10-08-120000','App\\Database\\Migrations\\AddFlagShort','default','App',1791469673,4);
INSERT INTO `migrations` VALUES (5,'2026-10-09-120000','App\\Database\\Migrations\\DropDurationColumns','default','App',1791497649,5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `news_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_feeds` (
  `site_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `site_name` varchar(50) NOT NULL DEFAULT '',
  `site_slug` varchar(100) NOT NULL DEFAULT '',
  `site_url` varchar(100) NOT NULL DEFAULT '',
  `site_feed` varchar(100) NOT NULL DEFAULT '',
  `site_category` varchar(50) NOT NULL,
  `site_date_added` datetime DEFAULT NULL,
  `site_date_updated` datetime DEFAULT NULL,
  `site_date_last_fetch` datetime NOT NULL,
  `site_date_last_post` datetime NOT NULL,
  `flag_featured` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `flag_stream` tinyint(1) NOT NULL DEFAULT 0,
  `flag_spoof` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`site_id`),
  KEY `idx_feeds_featured` (`flag_featured`,`site_name`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='bmxfeed feeds table';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `news_feeds` WRITE;
/*!40000 ALTER TABLE `news_feeds` DISABLE KEYS */;
INSERT INTO `news_feeds` VALUES (1,'FBM','fbm','https://www.fbmbmx.com/','https://feeds.feedburner.com/fbmnews','companies','2019-10-14 01:04:52','2019-10-14 01:04:52','2026-10-09 19:43:45','2026-09-14 09:23:34',1,1,0);
INSERT INTO `news_feeds` VALUES (2,'Odyssey','odyssey','https://www.odysseybmx.com/','https://feeds.feedburner.com/odysseybmx/','companies','2019-10-14 01:05:05','2019-10-14 01:05:05','2026-10-09 19:43:51','2026-10-05 19:17:38',1,1,0);
INSERT INTO `news_feeds` VALUES (3,'FATBMX','fat-bmx','https://www.fatbmx.com/','https://www.fatbmx.com/all-news?format=feed&type=rss','news','2020-04-24 00:25:27','2020-04-24 00:25:27','2026-10-09 19:43:32','2026-10-09 16:21:56',1,1,1);
INSERT INTO `news_feeds` VALUES (4,'Demolition','demolition','https://demolitionparts.com/','https://demolitionparts.com/blogs/blog.atom','companies','2020-04-24 00:24:54','2020-04-24 00:24:54','2026-10-09 18:58:17','2025-02-17 15:01:13',0,1,0);
INSERT INTO `news_feeds` VALUES (5,'United','united','https://unitedbikeco.com/','https://unitedbikeco.com/blogs/news.atom','companies','2017-06-24 00:27:42','2017-06-24 00:27:42','2026-09-27 01:17:43','2023-11-13 07:30:23',0,0,0);
INSERT INTO `news_feeds` VALUES (6,'BMXfeed videos','bmxfeed','https://bmxfeed.com/','https://bmxfeed.com/rss','news','2016-11-05 23:46:51','2016-11-05 23:46:51','2026-10-09 18:58:14','2026-10-09 16:40:04',0,1,0);
INSERT INTO `news_feeds` VALUES (7,'Fit','fit','https://fitbikeco.com/','https://fitbikeco.com/latest/feed/','companies','2018-02-10 18:02:06','2018-02-10 18:02:06','2026-10-09 18:58:18','2026-07-22 16:07:32',0,1,0);
INSERT INTO `news_feeds` VALUES (8,'Volume','volume','https://volumebikes.com/','https://volumebikes.com/blogs/news.atom','companies','2020-04-24 00:27:12','2020-04-24 00:27:12','2026-10-09 18:58:21','2026-08-08 20:48:00',0,1,0);
INSERT INTO `news_feeds` VALUES (12,'Profile','profile','https://www.profileracing.com/','https://www.profileracing.com/feed/','companies','2012-11-23 18:49:30','2012-11-23 18:49:30','2026-10-09 18:58:19','2026-10-05 08:25:51',0,1,0);
INSERT INTO `news_feeds` VALUES (13,'Kink','kink','https://kinkbmx.com/','https://kinkbmx.com/blogs/news.atom','companies','2020-02-19 02:19:25','2020-02-19 02:19:25','2026-10-09 18:58:19','2026-09-10 09:22:57',0,1,0);
INSERT INTO `news_feeds` VALUES (14,'Colony','colony','https://colonybmx.com.au/','https://colonybmx.com.au/feed/','companies','2017-06-24 00:23:02','2025-06-08 00:23:02','2026-10-09 18:58:16','2026-10-08 04:32:51',0,1,0);
INSERT INTO `news_feeds` VALUES (15,'S&M','s-m','https://www.sandmbikes.com/','http://feeds.feedburner.com/smbikesnews','companies','2015-04-03 00:29:09','2015-04-03 00:29:09','2026-10-09 19:44:16','2026-06-26 19:54:12',1,1,0);
INSERT INTO `news_feeds` VALUES (17,'The Union','the-union','https://bmxunion.com/','https://bmxunion.com/feed','news','2018-09-01 18:56:21','2018-09-01 18:56:21','2026-10-09 18:58:21','2026-10-08 11:40:42',1,1,0);
INSERT INTO `news_feeds` VALUES (19,'Circuit BMX','circuit-bmx','https://circuitbmx.com/blogs/circuit-blog','https://circuitbmx.com/blogs/circuit-blog.atom','retail','2008-06-13 17:14:25','2025-08-01 00:47:04','2026-09-30 03:47:21','2026-05-24 09:51:18',0,0,0);
INSERT INTO `news_feeds` VALUES (21,'Focal Point BMX','focal-point-bmx','https://focalpointbmx.com/','https://focalpointbmx.com/feed/','scene','2013-01-03 17:47:04','2025-08-01 00:47:04','2026-10-09 18:12:52','2026-10-08 05:02:53',0,0,0);
INSERT INTO `news_feeds` VALUES (24,'Sunday','sunday','https://www.sundaybikes.com/','https://feeds.feedburner.com/sundaybikes/','companies','2019-10-14 01:05:16','2019-10-14 01:05:16','2026-10-09 19:44:24','2026-10-01 17:30:42',1,1,0);
INSERT INTO `news_feeds` VALUES (27,'Least Most','least-most','https://leastmost.com/','https://leastmost.com/feed/','blogs','2017-06-24 00:24:11','2017-06-24 00:24:11','2026-09-04 18:54:05','2023-08-30 16:54:22',0,1,0);
INSERT INTO `news_feeds` VALUES (35,'BSD','bsd','https://bsdforever.com/','https://bsdforever.com/blogs/news.atom','companies','2018-08-02 16:38:14','2018-08-02 16:38:14','2026-10-09 18:58:14','2026-10-08 05:31:35',0,1,1);
INSERT INTO `news_feeds` VALUES (42,'Terrible One','terrible-one','https://terribleone.com/','https://terribleone.com/feed/','companies','2018-07-23 01:12:22','2026-01-31 00:47:04','2026-10-09 18:58:20','2026-09-09 15:49:28',1,1,0);
INSERT INTO `news_feeds` VALUES (44,'Central Library','central-library','https://blog.thecentrallibrary.com/','https://blog.thecentrallibrary.com/feed/','blogs','2017-11-24 18:50:46','2017-11-24 18:50:46','2026-10-09 11:36:56','2025-07-21 06:12:47',0,0,0);
INSERT INTO `news_feeds` VALUES (45,'Federal','federal','https://federalbikes.com/','https://federalbikes.com/blogs/news.atom','companies','2018-05-02 15:10:21','2018-05-02 15:10:21','2026-10-09 18:58:18','2026-10-09 13:05:01',0,1,0);
INSERT INTO `news_feeds` VALUES (47,'Our BMX','our-bmx','https://ourbmx.com/','https://ourbmx.com/feed/','news','2019-05-15 10:27:50','2019-05-15 10:27:50','2026-10-09 19:44:10','2026-10-08 12:20:05',1,1,1);
INSERT INTO `news_feeds` VALUES (48,'Cinema','cinema','https://cinemabmx.com/','https://cinemabmx.com/blogs/news.atom','companies','2020-02-19 02:20:14','2020-02-19 02:20:14','2026-10-09 18:58:14','2026-09-10 10:18:58',0,1,0);
INSERT INTO `news_feeds` VALUES (49,'Bloom','bloom','https://www.thebloombmx.com/','https://www.thebloombmx.com/feed','blogs','2023-09-05 20:08:57','2023-09-05 20:08:57','2026-10-09 11:36:51','2026-10-08 09:20:32',0,0,0);
INSERT INTO `news_feeds` VALUES (50,'Shadow','shadow','https://theshadowconspiracy.com/','https://theshadowconspiracy.com/category/news/feed/','companies','2025-12-03 00:00:00','2025-12-03 00:00:00','2026-10-07 05:38:28','2024-09-25 14:21:11',0,0,0);
INSERT INTO `news_feeds` VALUES (51,'Subrosa','subrosa','https://subrosabrand.com/','https://subrosabrand.com/zine/feed/','companies','2025-12-03 00:00:00','2025-12-03 00:00:00','2026-10-09 10:04:43','2024-09-25 14:16:23',0,0,0);
INSERT INTO `news_feeds` VALUES (52,'Tree','tree','https://treebicycleco.com/','https://treebicycleco.com/blogs/news.atom','companies','2025-12-03 00:00:00','2025-12-03 00:00:00','2026-10-09 13:09:42','2024-02-01 23:09:30',0,0,0);
INSERT INTO `news_feeds` VALUES (53,'Frequentleigh','frequentleigh','https://www.frequentleigh.com/','https://www.frequentleigh.com/blog-feed.xml','blogs','2026-08-30 00:00:00','2026-08-30 00:00:00','2026-10-09 18:58:18','2026-10-03 00:00:00',0,1,0);
/*!40000 ALTER TABLE `news_feeds` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `news_featured`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_featured` (
  `story_id` int(11) NOT NULL AUTO_INCREMENT,
  `story_title` varchar(255) NOT NULL,
  `story_permalink` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `story_hash` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `story_date` datetime NOT NULL,
  `site_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`story_id`),
  UNIQUE KEY `permalink` (`story_permalink`),
  KEY `idx_story_date` (`story_date`),
  KEY `idx_story_site_date` (`site_id`,`story_date`),
  CONSTRAINT `fk_story_site` FOREIGN KEY (`site_id`) REFERENCES `news_feeds` (`site_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20901668 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `news_featured` WRITE;
/*!40000 ALTER TABLE `news_featured` DISABLE KEYS */;
INSERT INTO `news_featured` VALUES (20830769,'CULT / Ison Bogosian','https://www.fatbmx.com/featured-news/item/63994-cult-ison-bogosian','b1a973ba99d4f4af67f2fe56fc1b1ff5d1bac474','2026-10-05 16:10:04',3);
INSERT INTO `news_featured` VALUES (20835021,'Coming Home - Gervais Rousseau\'s Western France Bowl Sessions -','https://www.fatbmx.com/featured-news/item/63995-coming-home-gervais-rousseau-s-western-france-bowl-sessions','09b59142d16ac4d06ec17f7b59c9b555937af39b','2026-10-05 16:16:51',3);
INSERT INTO `news_featured` VALUES (20849825,'Scenes from Amsterdam’s Massive BMX Street Jam by VICE Sports','https://www.fatbmx.com/featured-news/item/63996-scenes-from-amsterdam-s-massive-bmx-street-jam-by-vice-sports','ac6a140c56c257e4f813d9faf971635bdf9c203f','2026-10-06 03:44:43',3);
INSERT INTO `news_featured` VALUES (20850606,'KING OF BNE / CASH UP 2026 - DAY 1','https://www.fatbmx.com/featured-news/item/64000-king-of-bne-cash-up-2026-day-1','d945fe5e8167b74caa091dc365f1322f1e06ac5a','2026-10-06 10:46:19',3);
INSERT INTO `news_featured` VALUES (20850607,'FISE SHANGHAI 2026 THE WORLD OF URBAN SPORTS RETURNS TO WEST BUND','https://www.fatbmx.com/bmx-news/item/63999-fise-shanghai-2026-the-world-of-urban-sports-returns-to-west-bund','aa5d8aaa2e6d9037ea0307dd1aff6e0c97954828','2026-10-06 06:05:08',3);
INSERT INTO `news_featured` VALUES (20850608,'PeryaGame PH: Why Responsible Play Matters on GameZone','https://www.fatbmx.com/bmx-news/item/63998-peryagame-ph-why-responsible-play-matters-on-gamezone','f0725f749d3dfdb76ad840b729290205b3fddb33','2026-10-06 04:03:52',3);
INSERT INTO `news_featured` VALUES (20850609,'General Deheza Skatepark by Gaspar Guendulain','https://www.fatbmx.com/featured-news/item/63997-general-deheza-skatepark-by-gaspar-guendulain','9b9b9f81dc271ab7fcf8ebdca3d32e305bf4c749','2026-10-06 03:49:31',3);
INSERT INTO `news_featured` VALUES (20857474,'MIKEY ANDREW | Odyssey BMX - Bike Check','https://bmxfeed.com/video/NpRmL_eTWiM','508025e83a40e300121e874eafef7723e64367ed','2026-10-05 19:15:28',6);
INSERT INTO `news_featured` VALUES (20857475,'BMX -STRANGER X PRIMO SEPTEMBER IG COMPILATION','https://bmxfeed.com/video/PHl5wWcBfI4','61e1d29560a6f3fe1be2501d99cea9945f59ed49','2026-10-05 18:35:18',6);
INSERT INTO `news_featured` VALUES (20859169,'KING OF BNE / CASH UP 2026 - DAY 1','https://bmxfeed.com/video/AjSJ93yndaU','f29257859ddf0f455f9f4215a5705bfd76131ffc','2026-10-06 06:00:11',6);
INSERT INTO `news_featured` VALUES (20861647,'Chongli next up as BMX World Cup rolls deeper into China','https://www.fatbmx.com/bmx-racing/item/64001-chongli-next-up-as-bmx-world-cup-rolls-deeper-into-china','f998f3fa94585ff217fcf11cf7dd3fe6aa8bea4f','2026-10-07 09:38:10',3);
INSERT INTO `news_featured` VALUES (20863981,'\"TRY AGAIN\" | THE IN-BETWEEN EP.5 - DIG BMX','https://bmxfeed.com/video/Du-nudbpHU0','5bd2dce65837c25e789a22e46a601d67758e1b0b','2026-10-06 12:15:38',6);
INSERT INTO `news_featured` VALUES (20866519,'HJALTE JUUL FOR WETHEPEOPLE','https://bmxfeed.com/video/abZeDHQ61nI','ae67e19ff6d4114400979e7d4294c52b588e34f2','2026-10-06 15:20:09',6);
INSERT INTO `news_featured` VALUES (20879353,'DO YOU REMEMBER YOUR FIRST TIME? | THE IN-BETWEEN EP.5','https://www.fatbmx.com/featured-news/item/64002-do-you-remember-your-first-time-the-in-between-ep-5','e1707af641eda0c1eabf52b928c6233a847fbf99','2026-10-07 10:58:03',3);
INSERT INTO `news_featured` VALUES (20879982,'RL Osborn\'s Bike From RAD! / Emerald Valley 7th annual Show & Swap! By Snakebite BMX','https://www.fatbmx.com/bmx-oldskool/item/64004-rl-osborn-s-bike-from-rad-emerald-valley-7th-annual-show-swap-by-snakebite-bmx','16565a4af672c245d7e3762cdc2490b020151b02','2026-10-07 11:16:32',3);
INSERT INTO `news_featured` VALUES (20879983,'Zach Newman - Airborn Vert 2026','https://www.fatbmx.com/featured-news/item/64003-zach-newman-airborn-vert-2026','9d9ca758834fb7faa0b79d699080969460605977','2026-10-07 11:02:53',3);
INSERT INTO `news_featured` VALUES (20881437,'The G.Y.S.T. of It // PNW BMX Mixtape // 2014','https://bmxfeed.com/video/oV7NriSjl8c','051e519db1dad346dceb66db73b32481cc30f684','2026-10-06 16:00:25',6);
INSERT INTO `news_featured` VALUES (20882107,'Results 2026 French National Championship - Park & Flatland','https://www.fatbmx.com/bmx-freestyle/item/64005-results-2026-french-national-championship-park-flatland','98d26cec3ac47a54596e0c2ccbd4f297612bcaa9','2026-10-07 17:03:15',3);
INSERT INTO `news_featured` VALUES (20884689,'Nathan Smith - Welcome To Colony BMX','https://www.fatbmx.com/featured-news/item/64006-nathan-smith-welcome-to-colony-bmx','6372fe29a952ab6a7e631167e49387053279a875','2026-10-08 06:29:53',3);
INSERT INTO `news_featured` VALUES (20888775,'Ep. 146 - \"Chocolatine country\" - BMX video journal','https://www.fatbmx.com/featured-news/item/64007-ep-146-chocolatine-country-bmx-video-journal','89628400daa0cc2454989866b4abba148eccf79c','2026-10-08 06:33:57',3);
INSERT INTO `news_featured` VALUES (20893935,'BMX Vert @ Extreme Games 1995 | X Games Throwbacks','https://www.fatbmx.com/featured-news/item/64008-bmx-vert-extreme-games-1995-x-games-throwbacks','f65b41b5a4f2a33b1c787e91c1a79582ed569fbf','2026-10-08 06:40:44',3);
INSERT INTO `news_featured` VALUES (20894709,'Fit bike co heart breaker custom build with chad powers from powers bmx!','https://bmxfeed.com/video/1Enj3FgdR7Y','f17105865e189fd777c0403d704cf072d3f2b4a8','2026-10-06 17:30:38',6);
INSERT INTO `news_featured` VALUES (20898339,'KOB26 - Brisbane BMX Street Jam - Highlights','https://bmxfeed.com/video/dHnxSFT5NG4','783a5394adfe1f3aa110b824c5dad22b0d448d34','2026-10-08 10:46:03',6);
INSERT INTO `news_featured` VALUES (20901379,'KING OF BNE / CASH UP - DAY 2','https://www.fatbmx.com/featured-news/item/64009-king-of-bne-cash-up-day-2','1c7fb496bd5b331284ae04b459a52520c55bfb76','2026-10-08 06:44:30',3);
INSERT INTO `news_featured` VALUES (20901474,'Federal Bikes – Mauro Valencia “Coming Never”','https://bmxunion.com/federal-bikes-mauro-valencia-coming-never/','ee6f37b0376d9cdf20422900bdade88769f47d3d','2026-10-09 18:06:50',17);
INSERT INTO `news_featured` VALUES (20901475,'Lux BMX – Mitch Campbell “Love What You Do”','https://bmxunion.com/lux-bmx-mitch-campbell-love-what-you-do/','b6d6955c99ee4730d800b48658b8a49b8d4cd38f','2026-10-09 18:02:41',17);
INSERT INTO `news_featured` VALUES (20901476,'BMX News 10/9/26','https://bmxunion.com/bmx-news-10-9-26/','191b61755541cb1877aa890956b15fbcbe7dd945','2026-10-09 17:58:59',17);
INSERT INTO `news_featured` VALUES (20901484,'I Love this Torker Freestylist but it has a Few Things to Watch out for!','https://bmxfeed.com/video/wUaOBttNuJg','bc10f506636344e8b83ff8a6b479718a14198ff2','2026-10-09 16:40:04',6);
INSERT INTO `news_featured` VALUES (20901485,'Mauro Valencia - Coming Never - FEDERAL BIKES','https://bmxfeed.com/video/V6MoejZJW58','d2c3a0381242d10ecfbfa52506036dd471df0888','2026-10-09 13:50:04',6);
INSERT INTO `news_featured` VALUES (20901486,'MITCH CAMPBELL - \"LOVE WHAT YOU DO\" BMX PART','https://bmxfeed.com/video/4h-Y2NwjK8c','e89e18f760ccedb1bfe87be56f5ca6d219f96413','2026-10-09 04:35:23',6);
INSERT INTO `news_featured` VALUES (20901487,'Mountain Dew BMX Commercial // FiolaWilkerson& more! // 1984','https://bmxfeed.com/video/EFDyVQ57O_0','cc9b73a73e88b2af30ed6e26954a9e451577f6b3','2026-10-08 18:05:04',6);
INSERT INTO `news_featured` VALUES (20901488,'The Flat Tire Death Gap We\'ve All Been Waiting For','https://bmxfeed.com/video/PB61QPy1wAw','55764481707993edcd0a4ba6709cc6f837ee4b74','2026-10-08 16:30:18',6);
INSERT INTO `news_featured` VALUES (20901489,'INTERCITES STREET JAM - PARIS','https://bmxfeed.com/video/_MfoM4MS6kE','85b822d2f1eb925dcf1c702a70d267eea32a89c9','2026-10-08 13:02:09',6);
INSERT INTO `news_featured` VALUES (20901490,'KING OF BNE / CASH UP - DAY 2','https://bmxfeed.com/video/L1afl9aiZwQ','5042bf1a62673c4e886f88fa5f62f3191c4718da','2026-10-08 11:46:35',6);
INSERT INTO `news_featured` VALUES (20901491,'RL Osborn\'s Bike From RAD! // Emerald Valley 7th annual Show & Swap!','https://bmxfeed.com/video/9RN8Da1f3TE','f6ecbfd883a17894c4aaf40f2122e3dabb466114','2026-10-08 11:41:32',6);
INSERT INTO `news_featured` VALUES (20901492,'Nathan Smith - Welcome To Colony BMX','https://bmxfeed.com/video/256tIP5e13s','29496f7f834ef5e1d3100fb6db239f057a52872c','2026-10-08 11:36:09',6);
INSERT INTO `news_featured` VALUES (20901493,'KOB26 - Ekibin BMX Drain Jam Goes Off - LUX RAW','https://bmxfeed.com/video/ZnRSeK5kZzI','104e760ba353761136e06e27a6621b0fddb469a1','2026-10-08 10:46:05',6);
INSERT INTO `news_featured` VALUES (20901494,'BSD Focus Seat | Gaspar Guendulain\'s Signature Seat','https://bsdforever.com/blogs/news/bsd-focus-seat-gaspar-guendulains-signature-seat','fffc6ff1f5268ff33d3ca3c30bbbcea526a24521','2026-10-08 05:31:35',35);
INSERT INTO `news_featured` VALUES (20901495,'BSD SURESHOT FRAME - BACK IN BLACK','https://bsdforever.com/blogs/news/bsd-sureshot-frame','623d7c14197f5c0b03ad8987c8eae0fd2a15469f','2026-10-01 08:38:32',35);
INSERT INTO `news_featured` VALUES (20901496,'KILLIAN LIMOUSIN\'S BIKE CHECK','https://bsdforever.com/blogs/news/killian-limousins-bike-check','dc782f72111c629d9b5b8b312e37737fa2e72875','2026-09-29 07:37:41',35);
INSERT INTO `news_featured` VALUES (20901504,'Nathan Williams & Chad Kerley - Kink BMX \'St. Louie\'','https://cinemabmx.com/blogs/news/nathan-williams-chad-kerley-kink-bmx-st-louie','5f17c732a93b3c8f995c77a87202261005524026','2026-09-10 10:18:58',48);
INSERT INTO `news_featured` VALUES (20901514,'Nathan Smith Video Part','https://colonybmx.com.au/2026/10/nathan-smith-video-part/','286536406d0fe43ccdbf018940a2a4d77cfff9bf','2026-10-08 04:32:51',14);
INSERT INTO `news_featured` VALUES (20901515,'Hard Yards 5 Crash Section','https://colonybmx.com.au/2026/09/hard-yards-5-crash-section/','756b9b9e79cb398edfb127906ad334ad4a86619f','2026-09-29 17:30:20',14);
INSERT INTO `news_featured` VALUES (20901516,'BMXIN’ AT BEENO','https://colonybmx.com.au/2026/09/bmxin-at-beeno/','285891fcc2aaff5b30039e91f22bcd4f2465782e','2026-09-20 16:26:16',14);
INSERT INTO `news_featured` VALUES (20901534,'LIVE on FATBMX: Round 7: 1/8 to 1/4 Finals | 2026 UCI BMX Racing World Cup China','https://www.fatbmx.com/bmx-racing/item/64020-live-on-fatbmx-round-7-1-8-to-1-4-finals-2026-uci-bmx-racing-world-cup-china','7070ed7d084c79c0c625985ce28e53908a1c06b5','2026-10-09 16:18:31',3);
INSERT INTO `news_featured` VALUES (20901535,'Mauro Valencia - Coming Never - FEDERAL BIKES','https://www.fatbmx.com/featured-news/item/64018-mauro-valencia-coming-never-federal-bikes','7d786272855b7135f20a240294bbb22fb294c0ff','2026-10-09 13:19:56',3);
INSERT INTO `news_featured` VALUES (20901536,'FAT FAVOURITES list with Andrey Toporovskyi (BEL)','https://www.fatbmx.com/bmx-interviews/item/64017-fat-favourites-list-with-andrey-toporovskyi-bel','6e62ca60dc703c250ac9e86f4830e3fb28cecafb','2026-10-09 09:33:28',3);
INSERT INTO `news_featured` VALUES (20901537,'Thurgoona Skate Park Review / Strictly BMX','https://www.fatbmx.com/bmx-scene-reports/item/64016-thurgoona-skate-park-review-strictly-bmx','1fd45db05da9535c21b8e1faf3c8c99140cbcb5d','2026-10-09 07:06:11',3);
INSERT INTO `news_featured` VALUES (20901538,'Olympic Medalist BMX Racer Short Doc | Episode 1 by Max Alt Media','https://www.fatbmx.com/bmx-racing/item/64015-olympic-medalist-bmx-racer-short-doc-episode-1-by-max-alt-media','5718e6ba27927a061d5aa7e14bb7a535a3960370','2026-10-09 05:20:17',3);
INSERT INTO `news_featured` VALUES (20901539,'ENTITY BMX SHOP - MID BASH 4 - PREVAIL SKATEHOUSE','https://www.fatbmx.com/featured-news/item/64014-entity-bmx-shop-mid-bash-4-prevail-skatehouse','441cb66fb36accb90ada8284a476d366b4d3b6f4','2026-10-09 03:44:39',3);
INSERT INTO `news_featured` VALUES (20901540,'MITCH CAMPBELL - \"LOVE WHAT YOU DO\" BMX PART','https://www.fatbmx.com/featured-news/item/64013-mitch-campbell-love-what-you-do-bmx-part','fc138b7ee609d41c94a0b8eadb48b9d63ce04be8','2026-10-09 03:23:21',3);
INSERT INTO `news_featured` VALUES (20901541,'Alicia Keys & Swizz Beatz on BMX, Art, Ownership & Legacy | BET Exclusive','https://www.fatbmx.com/bmx-comic-and-art/item/64012-alicia-keys-swizz-beatz-on-bmx-art-ownership-legacy-bet-exclusive','a64f32fc4049bd91a26bc2e5e3afe7804c6dcc0e','2026-10-09 03:01:32',3);
INSERT INTO `news_featured` VALUES (20901542,'INTERCITES STREET JAM - PARIS','https://www.fatbmx.com/featured-news/item/64011-intercites-street-jam-paris','a5685b963c366a09fe83a48718b46e2c33c3ba10','2026-10-08 14:31:40',3);
INSERT INTO `news_featured` VALUES (20901543,'Marcus Christopher\'s NEW CUSTOM Passion BMX Frame!','https://www.fatbmx.com/featured-news/item/64010-marcus-christopher-s-new-custom-passion-bmx-frame','15617c40f52020f69684056284765221b0d69dc2','2026-10-08 06:47:23',3);
INSERT INTO `news_featured` VALUES (20901544,'Magilla – The Bar Is Closed!','https://www.fbmbmx.com/magilla-the-bar-is-closed-2/','96fa8f444d778e214f88ba1c903147ec5dbe6c75','2026-09-14 09:23:34',1);
INSERT INTO `news_featured` VALUES (20901545,'Gilly Mountain','https://www.fbmbmx.com/gilly-mountain/','ebb7f40b7f6ce4ba1de49a97b41a632e76f268ac','2026-09-08 11:17:51',1);
INSERT INTO `news_featured` VALUES (20901553,'Mauro Valencia - Coming Never Video LIVE!','https://federalbikes.com/blogs/news/mauro-valencia-coming-never-video-live','c5a60427708dfd4aa57322ee07ceb275dee820e2','2026-10-09 13:05:01',45);
INSERT INTO `news_featured` VALUES (20901573,'Live Show Report: Lucero at The Hall River Ball Room in Saxapahaw, NC','https://www.frequentleigh.com/post/live-show-report-lucero-at-the-hall-river-ball-room-in-saxapahaw-nc','82cfa75e732655ab07ac20e3b344376cb22d5f0b','2026-10-03 00:00:00',53);
INSERT INTO `news_featured` VALUES (20901574,'Early 2000\'s BMX Jam - Dirt at The Ranch Camp','https://www.frequentleigh.com/post/early-2000-s-bmx-jam-dirt-at-the-ranch-camp','ebdf0229706334e114eb1b6dab6d95544864c44d','2026-10-02 00:00:00',53);
INSERT INTO `news_featured` VALUES (20901575,'Live Show Report: Minus the Bear - Menos El Oso 20th Anniversary Tour','https://www.frequentleigh.com/post/live-show-report-minus-the-bear-menos-el-oso-20th-anniversary-tour','39523dfc784c86fdc1f2f7fbd58b6ae32a830831','2026-09-19 19:23:18',53);
INSERT INTO `news_featured` VALUES (20901583,'St. Louie is live!','https://kinkbmx.com/blogs/news/st-louie-is-live','313d3551b036b049680b0792885781480a7672d1','2026-09-10 09:22:57',13);
INSERT INTO `news_featured` VALUES (20901593,'MIKEY “MILKY” ANDREW BIKE CHECK','https://odysseybmx.com/2026/10/mikey-milky-andrew-bike-check/','157e11d3a83a4c4094ce0718579cb9fd5d667e53','2026-10-05 19:17:38',2);
INSERT INTO `news_featured` VALUES (20901594,'JOSH DELAROSA BIKE CHECK','https://odysseybmx.com/2026/09/josh-delarosa-bike-check/','0105c4b5a5d8bf6dd9aa3b57fb13a3a4d71c6c93','2026-09-30 13:39:40',2);
INSERT INTO `news_featured` VALUES (20901595,'Behind The Scenes / Capture BMX','https://odysseybmx.com/2026/09/behind-the-scenes-capture-bmx/','1f62b4bbaa9692fb618e47e73f0224733bbf6634','2026-09-24 15:13:37',2);
INSERT INTO `news_featured` VALUES (20901603,'HOTLINE | SLAVA OGEYCHUK – BLOSSOM','https://ourbmx.com/hotline-slava-ogeychuk-blossom/','62204c5cbfaf956aee594f3bc86409d74a40f258','2026-10-08 12:20:05',47);
INSERT INTO `news_featured` VALUES (20901604,'NATHAN SMITH – WELCOME TO COLONY','https://ourbmx.com/nathan-smith-welcome-to-colony/','a0e57456982aeaafa6e9cc1b2cef003afe95ca28','2026-10-08 11:22:14',47);
INSERT INTO `news_featured` VALUES (20901605,'MIKEY ANDREW – BIKE CHECK – ODYSSEY','https://ourbmx.com/mikey-andrew-bike-check-odyssey/','75317a9836378780e28a96c564cbf4ca18fc66e6','2026-10-07 16:28:00',47);
INSERT INTO `news_featured` VALUES (20901606,'Zach Newman- Airborn Vert 2026','https://ourbmx.com/zach-newman-airborn-vert-2026/','8e9d5e1d7894cfe28c3abe9bf869ef1cc73a0aca','2026-10-06 05:44:36',47);
INSERT INTO `news_featured` VALUES (20901607,'UNCLES','https://ourbmx.com/uncles/','41a3372328c2f42aea73768e4ae5276545dbb13a','2026-10-05 17:24:15',47);
INSERT INTO `news_featured` VALUES (20901608,'HOW NORA CUP VOTING WORKS (As of 2026, at least)','https://ourbmx.com/how-nora-cup-voting-works-as-of-2024-at-least/','c8a166995ce456f254159203e570f023f42fa9cc','2026-10-05 15:57:25',47);
INSERT INTO `news_featured` VALUES (20901613,'Coming Home — Gervais Rousseau’s Western France Bowl Sessions — Profile Racing','https://www.profileracing.com/coming-home-gervais-rousseaus-western-france-bowl-sessions-profile-racing/','5e3a6c12afd9a438129a0d4e908fab9afeb8e544','2026-10-05 08:25:51',12);
INSERT INTO `news_featured` VALUES (20901614,'Mark Mulville – Profile Notes from Underground – September, 2026','https://www.profileracing.com/mark-mulville-profile-notes-from-underground-september-2026-2/','f8fd3a873231e0e1e878921ad2fee67f76227312','2026-10-02 15:30:52',12);
INSERT INTO `news_featured` VALUES (20901615,'Leandro Moreira – Profile Notes from Underground – September, 2026','https://www.profileracing.com/leandro-moreira-profile-notes-from-underground-september-2026/','f8257e040ef43146633a69212edca940579d04a8','2026-10-02 15:29:12',12);
INSERT INTO `news_featured` VALUES (20901633,'BEN ALLEN – TEXAS FRY’D','https://sundaybikes.com/2026/10/ben-allen-texas-fryd/','a489ab886dc27f9fb654249b6e4ea921a66e0844','2026-10-01 17:30:42',24);
INSERT INTO `news_featured` VALUES (20901634,'JARED DUNCAN – HAIR OF THE DOG','https://sundaybikes.com/2026/09/jared-duncan-hair-of-the-dog/','b7809884f7c2fa27259181b2da30295f16c5bcb5','2026-09-24 19:55:43',24);
INSERT INTO `news_featured` VALUES (20901635,'BRETT SILVA – HAIR OF THE DOG','https://sundaybikes.com/2026/09/brett-silva-hair-of-the-dog/','a0ba9f3fa5a24ec19cc8e2c3800a51b09cedb561','2026-09-18 17:24:58',24);
INSERT INTO `news_featured` VALUES (20901643,'it pulls up something from within . . .','https://terribleone.com/it-pulls-up-something-from-within/','bc5fbe0d4e7a440d8dc7c218a04a10f14b10c37a','2026-09-09 15:49:28',42);
INSERT INTO `news_featured` VALUES (20901648,'Colony BMX – Nathan Smith Welcome Video','https://bmxunion.com/colony-bmx-nathan-smith-welcome-video/','0cd8ba9253d64f44790d786a8830f60187841152','2026-10-08 11:40:42',17);
INSERT INTO `news_featured` VALUES (20901649,'Odyssey – Mikey Andrew 2026 Bike Check','https://bmxunion.com/odyssey-mikey-andrew-2026-bike-check/','29a559b9a96b2fdba6c322de7918f26ee97f9792','2026-10-06 18:29:34',17);
INSERT INTO `news_featured` VALUES (20901650,'Cult – Ison Bogosian 2026','https://bmxunion.com/cult-ison-bogosian-2026/','805def9899b27ca428a54f4715e374878d79a438','2026-10-05 16:56:21',17);
/*!40000 ALTER TABLE `news_featured` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `aggro_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `aggro_videos` (
  `aggro_id` int(11) NOT NULL AUTO_INCREMENT,
  `aggro_date_added` datetime DEFAULT NULL,
  `aggro_date_updated` datetime DEFAULT NULL,
  `video_id` varchar(25) NOT NULL DEFAULT '',
  `video_plays` int(11) NOT NULL DEFAULT 0,
  `video_title` varchar(255) DEFAULT NULL,
  `video_thumbnail_url` varchar(255) DEFAULT NULL,
  `video_date_uploaded` datetime DEFAULT NULL,
  `video_width` int(11) NOT NULL DEFAULT 0,
  `video_height` int(11) NOT NULL DEFAULT 0,
  `video_aspect_ratio` float NOT NULL DEFAULT 0,
  `video_type` varchar(255) DEFAULT NULL,
  `video_source_id` varchar(255) DEFAULT NULL,
  `video_source_username` varchar(255) DEFAULT NULL,
  `video_source_user_slug` varchar(255) DEFAULT NULL,
  `video_source_url` varchar(255) DEFAULT NULL,
  `flag_archive` int(11) NOT NULL DEFAULT 0,
  `flag_bad` int(11) NOT NULL DEFAULT 0,
  `flag_favorite` int(11) NOT NULL DEFAULT 0,
  `notes` mediumtext DEFAULT NULL,
  `thumbnail_issue_count` int(11) NOT NULL DEFAULT 0,
  `plays_date_updated` datetime DEFAULT NULL,
  `plays_issue_count` int(11) NOT NULL DEFAULT 0,
  `flag_short` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`aggro_id`),
  UNIQUE KEY `videoid` (`video_id`),
  KEY `idx_video_archive_date` (`flag_archive`,`flag_bad`,`video_date_uploaded`),
  KEY `idx_video_plays_archive` (`flag_archive`,`video_plays`),
  KEY `idx_video_plays_refresh` (`flag_bad`,`plays_date_updated`)
) ENGINE=InnoDB AUTO_INCREMENT=54473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `aggro_videos` WRITE;
/*!40000 ALTER TABLE `aggro_videos` DISABLE KEYS */;
INSERT INTO `aggro_videos` VALUES (30867,'2018-01-08 04:31:03','2018-01-08 04:31:03','250082476',0,'Joe Embrey','https://i.vimeocdn.com/video/676076553_640.jpg','2018-01-08 03:54:27',1920,1080,1.778,'vimeo','channel4down','Channel 4Down','channel4down','https://vimeo.com/channel4down',0,1,0,NULL,0,NULL,0,0);
INSERT INTO `aggro_videos` VALUES (31329,'2018-03-20 19:32:02','2018-03-22 20:20:02','257513305',938,'Corey Dewey - PRESENCE LAST STAND','https://i.vimeocdn.com/video/685452937_640.jpg','2018-02-26 10:31:56',1920,1080,1.778,'vimeo','user17989696','Jeremy Deme','user17989696','https://vimeo.com/user17989696',0,1,0,NULL,0,NULL,0,0);
INSERT INTO `aggro_videos` VALUES (54395,'2026-10-01 12:45:11','2026-10-01 12:45:11','JnwKO_8vexw',984,'BMX - Same Shit Different Shovel part 3','https://i3.ytimg.com/vi/JnwKO_8vexw/hqdefault.jpg','2026-10-01 12:23:37',200,113,1.77,'youtube','UCFuvMlKbmYdySCUjWo36fZA','Steve Crandall',NULL,'https://www.youtube.com/channel/UCFuvMlKbmYdySCUjWo36fZA',0,0,0,NULL,0,'2026-10-09 18:20:11',0,0);
INSERT INTO `aggro_videos` VALUES (54397,'2026-10-01 14:15:04','2026-10-01 14:15:04','GxiE5TrvgGY',8358,'CURTIS SCOTT 2026 | DIG LOCALS','https://i4.ytimg.com/vi/GxiE5TrvgGY/hqdefault.jpg','2026-10-01 13:32:18',200,113,1.77,'youtube','UCuFLeyZaC1yMXPjKafghtIw','DIG BMX Official',NULL,'https://www.youtube.com/channel/UCuFLeyZaC1yMXPjKafghtIw',0,0,0,NULL,0,'2026-10-09 18:00:11',0,0);
INSERT INTO `aggro_videos` VALUES (54398,'2026-10-01 16:40:14','2026-10-01 16:40:14','mB_Vjub1p8M',39278,'Attempting His Brother\'s Impossible BMX Tricks','https://i2.ytimg.com/vi/mB_Vjub1p8M/hqdefault.jpg','2026-10-01 16:00:12',200,113,1.77,'youtube','UCxS2lX7728bTnmK1t21bYlA','Scotty Cranmer',NULL,'https://www.youtube.com/channel/UCxS2lX7728bTnmK1t21bYlA',0,0,0,NULL,0,'2026-10-09 18:40:13',0,0);
INSERT INTO `aggro_videos` VALUES (54399,'2026-10-01 17:00:15','2026-10-01 17:00:15','RifVEmWy4j8',491,'Bruce Crisman & Eduardo Terreros // Bilbao Spain // 2002','https://i3.ytimg.com/vi/RifVEmWy4j8/hqdefault.jpg','2026-10-01 16:46:32',200,113,1.77,'youtube','UC8npzcQ9nw14vODWm33heHw','Snakebite BMX',NULL,'https://www.youtube.com/channel/UC8npzcQ9nw14vODWm33heHw',0,0,0,NULL,0,'2026-10-09 17:55:10',0,0);
INSERT INTO `aggro_videos` VALUES (54400,'2026-10-01 17:00:16','2026-10-01 17:00:16','4_DtKO9RfmE',554,'Spot Check // Burlington Bike Park // Washington // 2013','https://i1.ytimg.com/vi/4_DtKO9RfmE/hqdefault.jpg','2026-10-01 16:38:03',200,113,1.77,'youtube','UC8npzcQ9nw14vODWm33heHw','Snakebite BMX',NULL,'https://www.youtube.com/channel/UC8npzcQ9nw14vODWm33heHw',0,0,0,NULL,0,'2026-10-09 17:55:10',0,0);
INSERT INTO `aggro_videos` VALUES (54401,'2026-10-01 18:35:20','2026-10-01 18:35:20','0b2d2aQ5Ij0',12084,'BEN ALLEN - TEXAS FRY\'D | Sunday Bikes','https://i1.ytimg.com/vi/0b2d2aQ5Ij0/hqdefault.jpg','2026-09-14 17:33:57',200,113,1.77,'youtube','UCsMczRyPB91lkNug1-2vGcQ','Sunday Bikes',NULL,'https://www.youtube.com/channel/UCsMczRyPB91lkNug1-2vGcQ',0,0,0,NULL,0,'2026-10-09 18:35:11',0,0);
INSERT INTO `aggro_videos` VALUES (54404,'2026-10-02 05:47:24','2026-10-02 05:47:24','-x5n3MR3x9w',61615,'DIY, BMX AND DESTRUCTION with Kohl Denny','https://i2.ytimg.com/vi/-x5n3MR3x9w/hqdefault.jpg','2026-10-02 02:54:05',200,113,1.77,'youtube','UCcNSdIT5yN5OXkoLO2NIs7A','Dennis Enarson',NULL,'https://www.youtube.com/channel/UCcNSdIT5yN5OXkoLO2NIs7A',0,0,0,NULL,0,'2026-10-09 18:05:10',0,0);
INSERT INTO `aggro_videos` VALUES (54405,'2026-10-02 06:17:20','2026-10-02 06:17:20','HkN93JqRqUs',503,'Profile Racings Drew Jackson bmx interview','https://i1.ytimg.com/vi/HkN93JqRqUs/hqdefault.jpg','2026-10-01 19:56:52',200,113,1.77,'youtube','UCOXspPzdIOXSmOp-b0unB3A','PowersBMX',NULL,'https://www.youtube.com/channel/UCOXspPzdIOXSmOp-b0unB3A',0,0,0,NULL,0,'2026-10-09 18:10:10',0,0);
INSERT INTO `aggro_videos` VALUES (54407,'2026-10-02 12:30:15','2026-10-02 12:30:15','u1ylreMLT1M',23484,'Felix Prangenberg - STILL','https://i2.ytimg.com/vi/u1ylreMLT1M/hqdefault.jpg','2026-10-02 12:00:25',200,150,1.333,'youtube','UCuFLeyZaC1yMXPjKafghtIw','DIG BMX Official',NULL,'https://www.youtube.com/channel/UCuFLeyZaC1yMXPjKafghtIw',0,0,0,NULL,0,'2026-10-09 18:00:11',0,0);
INSERT INTO `aggro_videos` VALUES (54411,'2026-10-02 16:50:19','2026-10-02 16:50:19','sGrTSeHCNO8',22823,'Bruce Crisman\'s S&M BTM Build is a Freecoasting Machine!','https://i4.ytimg.com/vi/sGrTSeHCNO8/hqdefault.jpg','2026-10-02 15:44:09',200,113,1.77,'youtube','UC8npzcQ9nw14vODWm33heHw','Snakebite BMX',NULL,'https://www.youtube.com/channel/UC8npzcQ9nw14vODWm33heHw',0,0,0,NULL,0,'2026-10-09 17:55:10',0,0);
INSERT INTO `aggro_videos` VALUES (54413,'2026-10-02 18:50:26','2026-10-02 18:50:26','WuWT_XZP0dM',149,'Early 2000 BMX Jam Dirt','https://i4.ytimg.com/vi/WuWT_XZP0dM/hqdefault.jpg','2026-10-02 17:43:56',200,113,1.77,'youtube','UCycyysxUtRm2YGNUQJKuesQ','FrequentLeigh',NULL,'https://www.youtube.com/channel/UCycyysxUtRm2YGNUQJKuesQ',0,0,0,NULL,0,'2026-10-09 17:45:09',0,0);
INSERT INTO `aggro_videos` VALUES (54417,'2026-10-04 04:00:25','2026-10-04 04:00:25','FaDk3HJm0ts',11158,'WHAT THE HELL HAPPENED IN BMX?! - UNCLICKED SEPTEMBER 2026','https://i3.ytimg.com/vi/FaDk3HJm0ts/hqdefault.jpg','2026-10-03 22:48:16',200,113,1.77,'youtube','UCuSZUuRMLzOP0I6ItdA6uAQ','Our BMX',NULL,'https://www.youtube.com/channel/UCuSZUuRMLzOP0I6ItdA6uAQ',0,0,0,NULL,0,'2026-10-09 17:45:10',0,0);
INSERT INTO `aggro_videos` VALUES (54422,'2026-10-04 16:55:11','2026-10-04 16:55:11','nqaHbBMDZjI',94,'Early 2000\'s BMX Jam - Bowl Event at The Ranch Camp - Dugspar, Virginia','https://i3.ytimg.com/vi/nqaHbBMDZjI/hqdefault.jpg','2026-10-04 16:38:12',200,113,1.77,'youtube','UCycyysxUtRm2YGNUQJKuesQ','FrequentLeigh',NULL,'https://www.youtube.com/channel/UCycyysxUtRm2YGNUQJKuesQ',0,0,0,NULL,0,'2026-10-09 17:45:09',0,0);
INSERT INTO `aggro_videos` VALUES (54423,'2026-10-05 07:37:29','2026-10-05 07:37:29','xokgtLzpMGA',3207,'Coming Home -- Gervais Rousseau\'s Western France Bowl Sessions -- Profile Racing','https://i1.ytimg.com/vi/xokgtLzpMGA/hqdefault.jpg','2026-10-05 07:37:05',200,113,1.77,'youtube','UC1K6CuWe6lUj_ikgjwEcKTA','Profile Racing',NULL,'https://www.youtube.com/channel/UC1K6CuWe6lUj_ikgjwEcKTA',0,0,0,NULL,0,'2026-10-09 18:25:11',0,0);
INSERT INTO `aggro_videos` VALUES (54424,'2026-10-05 10:17:38','2026-10-05 10:17:38','7U8-Hbxyh78',257,'The Bar is Closed clips circa \'96','https://i4.ytimg.com/vi/7U8-Hbxyh78/hqdefault.jpg','2026-10-05 09:48:17',200,150,1.333,'youtube','UCFuvMlKbmYdySCUjWo36fZA','Steve Crandall',NULL,'https://www.youtube.com/channel/UCFuvMlKbmYdySCUjWo36fZA',0,0,0,NULL,0,'2026-10-09 18:20:11',0,0);
INSERT INTO `aggro_videos` VALUES (54425,'2026-10-05 12:07:56','2026-10-05 12:07:56','a-bRQInnNVM',6076,'CULT/ Ison Bogosian','https://i2.ytimg.com/vi/a-bRQInnNVM/hqdefault.jpg','2026-10-05 12:00:55',200,150,1.333,'youtube','UCUXFXlTfxxykngI4m_LmYWg','Cult Crew',NULL,'https://www.youtube.com/channel/UCUXFXlTfxxykngI4m_LmYWg',0,0,0,NULL,0,'2026-10-09 17:45:09',0,0);
INSERT INTO `aggro_videos` VALUES (54428,'2026-10-05 12:37:16','2026-10-05 12:37:16','iiBe1Rd0RK0',1918,'New bmx bike day! DK Vega SS 22” bmx bike build and review! this bike is sick!','https://i2.ytimg.com/vi/iiBe1Rd0RK0/hqdefault.jpg','2026-10-05 11:36:45',200,113,1.77,'youtube','UCOXspPzdIOXSmOp-b0unB3A','PowersBMX',NULL,'https://www.youtube.com/channel/UCOXspPzdIOXSmOp-b0unB3A',0,0,0,NULL,0,'2026-10-09 18:10:10',0,0);
INSERT INTO `aggro_videos` VALUES (54430,'2026-10-05 18:35:18','2026-10-05 18:35:18','PHl5wWcBfI4',896,'BMX -STRANGER X PRIMO SEPTEMBER IG COMPILATION','https://i1.ytimg.com/vi/PHl5wWcBfI4/hqdefault.jpg','2026-10-05 17:33:07',200,113,1.77,'youtube','UCwFISG7xz0QbiGFzwUllyEw','PRIMO,STRANGER',NULL,'https://www.youtube.com/channel/UCwFISG7xz0QbiGFzwUllyEw',0,0,0,NULL,0,'2026-10-09 18:30:12',0,0);
INSERT INTO `aggro_videos` VALUES (54431,'2026-10-05 19:15:28','2026-10-05 19:15:28','NpRmL_eTWiM',7555,'MIKEY ANDREW | Odyssey BMX - Bike Check','https://i3.ytimg.com/vi/NpRmL_eTWiM/hqdefault.jpg','2026-10-05 18:14:32',200,113,1.77,'youtube','UCJHbVOD6qHuwkTth4Nn38PQ','Odyssey BMX',NULL,'https://www.youtube.com/channel/UCJHbVOD6qHuwkTth4Nn38PQ',0,0,0,NULL,0,'2026-10-09 18:20:10',0,0);
INSERT INTO `aggro_videos` VALUES (54433,'2026-10-06 06:00:11','2026-10-06 06:00:11','AjSJ93yndaU',14775,'KING OF BNE / CASH UP 2026 - DAY 1','https://i2.ytimg.com/vi/AjSJ93yndaU/hqdefault.jpg','2026-10-06 05:12:33',200,113,1.77,'youtube','UCuFLeyZaC1yMXPjKafghtIw','DIG BMX Official',NULL,'https://www.youtube.com/channel/UCuFLeyZaC1yMXPjKafghtIw',0,0,0,NULL,0,'2026-10-09 18:00:11',0,0);
INSERT INTO `aggro_videos` VALUES (54434,'2026-10-06 12:15:38','2026-10-06 12:15:38','Du-nudbpHU0',3368,'\"TRY AGAIN\" | THE IN-BETWEEN EP.5 - DIG BMX','https://i1.ytimg.com/vi/Du-nudbpHU0/hqdefault.jpg','2026-10-06 12:00:14',200,113,1.77,'youtube','UCuFLeyZaC1yMXPjKafghtIw','DIG BMX Official',NULL,'https://www.youtube.com/channel/UCuFLeyZaC1yMXPjKafghtIw',0,0,0,NULL,0,'2026-10-09 18:00:11',0,0);
INSERT INTO `aggro_videos` VALUES (54438,'2026-10-06 15:20:09','2026-10-06 15:20:09','abZeDHQ61nI',6338,'HJALTE JUUL FOR WETHEPEOPLE','https://i2.ytimg.com/vi/abZeDHQ61nI/hqdefault.jpg','2026-09-24 13:00:08',200,113,1.77,'youtube','UCkJ8F2VXvwJzsg-NkFg4f2g','Wethepeople BMX',NULL,'https://www.youtube.com/channel/UCkJ8F2VXvwJzsg-NkFg4f2g',0,0,0,NULL,0,'2026-10-09 18:59:08',0,0);
INSERT INTO `aggro_videos` VALUES (54439,'2026-10-06 16:00:25','2026-10-06 16:00:25','oV7NriSjl8c',602,'The G.Y.S.T. of It // PNW BMX Mixtape // 2014','https://i4.ytimg.com/vi/oV7NriSjl8c/hqdefault.jpg','2026-10-06 15:34:26',200,113,1.77,'youtube','UC8npzcQ9nw14vODWm33heHw','Snakebite BMX',NULL,'https://www.youtube.com/channel/UC8npzcQ9nw14vODWm33heHw',0,0,0,NULL,0,'2026-10-09 17:55:10',0,0);
INSERT INTO `aggro_videos` VALUES (54441,'2026-10-06 17:30:38','2026-10-06 17:30:38','1Enj3FgdR7Y',1195,'Fit bike co heart breaker custom build with chad powers from powers bmx!','https://i2.ytimg.com/vi/1Enj3FgdR7Y/hqdefault.jpg','2026-09-29 18:27:14',200,113,1.77,'youtube','UCOXspPzdIOXSmOp-b0unB3A','PowersBMX',NULL,'https://www.youtube.com/channel/UCOXspPzdIOXSmOp-b0unB3A',0,0,0,NULL,0,'2026-10-09 18:10:10',0,0);
INSERT INTO `aggro_videos` VALUES (54444,'2026-10-08 10:46:03','2026-10-08 10:46:03','dHnxSFT5NG4',3171,'KOB26 - Brisbane BMX Street Jam - Highlights','https://i1.ytimg.com/vi/dHnxSFT5NG4/hqdefault.jpg','2026-10-07 02:00:07',200,113,1.77,'youtube','UCpLJVzG2_I68lX855M_7Eqg','LUXBMX',NULL,'https://www.youtube.com/channel/UCpLJVzG2_I68lX855M_7Eqg',0,0,0,NULL,0,'2026-10-09 18:59:08',0,0);
INSERT INTO `aggro_videos` VALUES (54445,'2026-10-08 10:46:05','2026-10-08 10:46:05','ZnRSeK5kZzI',2571,'KOB26 - Ekibin BMX Drain Jam Goes Off - LUX RAW','https://i3.ytimg.com/vi/ZnRSeK5kZzI/hqdefault.jpg','2026-10-07 02:00:07',200,113,1.77,'youtube','UCpLJVzG2_I68lX855M_7Eqg','LUXBMX',NULL,'https://www.youtube.com/channel/UCpLJVzG2_I68lX855M_7Eqg',0,0,0,NULL,0,'2026-10-09 18:59:08',0,0);
INSERT INTO `aggro_videos` VALUES (54453,'2026-10-08 11:36:09','2026-10-08 11:36:09','256tIP5e13s',7407,'Nathan Smith - Welcome To Colony BMX','https://i3.ytimg.com/vi/256tIP5e13s/hqdefault.jpg','2026-10-08 04:19:00',200,113,1.77,'youtube','UC2bGSgCvxF2dM9Yig3z2gxA','ColonyBMX',NULL,'https://www.youtube.com/channel/UC2bGSgCvxF2dM9Yig3z2gxA',0,0,0,NULL,0,'2026-10-09 17:40:09',0,0);
INSERT INTO `aggro_videos` VALUES (54454,'2026-10-08 11:41:32','2026-10-08 11:41:32','9RN8Da1f3TE',3553,'RL Osborn\'s Bike From RAD! // Emerald Valley 7th annual Show & Swap!','https://i2.ytimg.com/vi/9RN8Da1f3TE/hqdefault.jpg','2026-10-06 20:55:21',200,113,1.77,'youtube','UC8npzcQ9nw14vODWm33heHw','Snakebite BMX',NULL,'https://www.youtube.com/channel/UC8npzcQ9nw14vODWm33heHw',0,0,0,NULL,0,'2026-10-09 17:55:10',0,0);
INSERT INTO `aggro_videos` VALUES (54455,'2026-10-08 11:46:35','2026-10-08 11:46:35','L1afl9aiZwQ',10619,'KING OF BNE / CASH UP - DAY 2','https://i1.ytimg.com/vi/L1afl9aiZwQ/hqdefault.jpg','2026-10-07 12:00:25',200,113,1.77,'youtube','UCuFLeyZaC1yMXPjKafghtIw','DIG BMX Official',NULL,'https://www.youtube.com/channel/UCuFLeyZaC1yMXPjKafghtIw',0,0,0,NULL,0,'2026-10-09 18:00:11',0,0);
INSERT INTO `aggro_videos` VALUES (54457,'2026-10-08 13:02:09','2026-10-08 13:02:09','_MfoM4MS6kE',15020,'INTERCITES STREET JAM - PARIS','https://i4.ytimg.com/vi/_MfoM4MS6kE/hqdefault.jpg','2026-10-08 12:00:38',200,113,1.77,'youtube','UCuFLeyZaC1yMXPjKafghtIw','DIG BMX Official',NULL,'https://www.youtube.com/channel/UCuFLeyZaC1yMXPjKafghtIw',0,0,0,NULL,0,'2026-10-09 18:00:11',0,0);
INSERT INTO `aggro_videos` VALUES (54459,'2026-10-08 16:30:18','2026-10-08 16:30:18','PB61QPy1wAw',25535,'The Flat Tire Death Gap We\'ve All Been Waiting For','https://i1.ytimg.com/vi/PB61QPy1wAw/hqdefault.jpg','2026-10-08 16:00:37',200,113,1.77,'youtube','UCxS2lX7728bTnmK1t21bYlA','Scotty Cranmer',NULL,'https://www.youtube.com/channel/UCxS2lX7728bTnmK1t21bYlA',0,0,0,NULL,0,'2026-10-09 18:40:13',0,0);
INSERT INTO `aggro_videos` VALUES (54460,'2026-10-08 18:05:04','2026-10-08 18:05:04','EFDyVQ57O_0',1259,'Mountain Dew BMX Commercial // Fiola,Wilkerson,& more! // 1984','https://i2.ytimg.com/vi/EFDyVQ57O_0/hqdefault.jpg','2026-10-08 17:19:58',200,150,1.333,'youtube','UC8npzcQ9nw14vODWm33heHw','Snakebite BMX',NULL,'https://www.youtube.com/channel/UC8npzcQ9nw14vODWm33heHw',0,0,0,NULL,0,'2026-10-09 17:55:10',0,0);
INSERT INTO `aggro_videos` VALUES (54462,'2026-10-09 04:35:23','2026-10-09 04:35:23','4h-Y2NwjK8c',2097,'MITCH CAMPBELL - \"LOVE WHAT YOU DO\" BMX PART','https://i1.ytimg.com/vi/4h-Y2NwjK8c/hqdefault.jpg','2026-10-08 20:14:42',200,113,1.77,'youtube','UCpLJVzG2_I68lX855M_7Eqg','LUXBMX',NULL,'https://www.youtube.com/channel/UCpLJVzG2_I68lX855M_7Eqg',0,0,0,NULL,0,'2026-10-09 18:59:08',0,0);
INSERT INTO `aggro_videos` VALUES (54466,'2026-10-09 12:05:56','2026-10-09 12:05:56','riOxUlCD_VI',3324,'BRO, WHAAAT?! #bmx','https://i3.ytimg.com/vi/riOxUlCD_VI/hqdefault.jpg','2026-10-09 11:55:04',200,113,1.77,'youtube','UCyPFwLmziUkiw4gw9QkI8_w','freedombmx',NULL,'https://www.youtube.com/channel/UCyPFwLmziUkiw4gw9QkI8_w',0,0,0,NULL,0,'2026-10-09 18:30:12',0,1);
INSERT INTO `aggro_videos` VALUES (54467,'2026-10-09 12:11:00','2026-10-09 12:11:00','OvcSD3dzwQM',259,'DARKWAVE AUTHENTIC COMPLETE BIKE | SUNDAY BIKES','https://i4.ytimg.com/vi/OvcSD3dzwQM/hqdefault.jpg','2026-10-09 11:00:14',200,113,1.77,'youtube','UCsMczRyPB91lkNug1-2vGcQ','Sunday Bikes',NULL,'https://www.youtube.com/channel/UCsMczRyPB91lkNug1-2vGcQ',0,0,0,NULL,0,'2026-10-09 18:35:11',0,1);
INSERT INTO `aggro_videos` VALUES (54468,'2026-10-09 12:16:04','2026-10-09 12:16:04','jHL2wVoadCs',1178,'HIS BIKING HIT DIFFERENT - RYOTA MIYAJI','https://i3.ytimg.com/vi/jHL2wVoadCs/hqdefault.jpg','2026-10-09 12:00:11',200,113,1.77,'youtube','UCWWCwqQj0MIGZahGrOo4j0Q','MERRITTBMX',NULL,'https://www.youtube.com/channel/UCWWCwqQj0MIGZahGrOo4j0Q',0,0,0,NULL,0,'2026-10-09 18:40:13',0,1);
INSERT INTO `aggro_videos` VALUES (54469,'2026-10-09 13:25:03','2026-10-09 13:25:03','J50Jq13mFHw',1830,'NACKENSCHLÄGE #bmx','https://i3.ytimg.com/vi/J50Jq13mFHw/hqdefault.jpg','2026-10-09 12:58:01',200,113,1.77,'youtube','UCyPFwLmziUkiw4gw9QkI8_w','freedombmx',NULL,'https://www.youtube.com/channel/UCyPFwLmziUkiw4gw9QkI8_w',0,0,0,NULL,0,'2026-10-09 18:30:12',0,1);
INSERT INTO `aggro_videos` VALUES (54470,'2026-10-09 13:50:04','2026-10-09 13:50:04','V6MoejZJW58',1313,'Mauro Valencia - Coming Never - FEDERAL BIKES','https://i3.ytimg.com/vi/V6MoejZJW58/hqdefault.jpg','2026-10-09 13:00:26',200,113,1.77,'youtube','UCg-C5SGBn1qxMlxWbJWLQrg','Federal Bikes',NULL,'https://www.youtube.com/channel/UCg-C5SGBn1qxMlxWbJWLQrg',0,0,0,NULL,0,'2026-10-09 18:59:08',0,0);
INSERT INTO `aggro_videos` VALUES (54471,'2026-10-09 16:10:17','2026-10-09 16:10:17','tLHjh1NjFzk',1391,'Handicap Rider Jumps Flat Tire Death Gap  #bmx #bikes','https://i1.ytimg.com/vi/tLHjh1NjFzk/hqdefault.jpg','2026-10-09 16:00:32',200,113,1.77,'youtube','UCxS2lX7728bTnmK1t21bYlA','Scotty Cranmer',NULL,'https://www.youtube.com/channel/UCxS2lX7728bTnmK1t21bYlA',0,0,0,NULL,0,'2026-10-09 18:40:13',0,1);
INSERT INTO `aggro_videos` VALUES (54472,'2026-10-09 16:40:04','2026-10-09 16:40:04','wUaOBttNuJg',129,'I Love this Torker Freestylist but it has a Few Things to Watch out for!','https://i4.ytimg.com/vi/wUaOBttNuJg/hqdefault.jpg','2026-10-09 15:39:56',200,113,1.77,'youtube','UC8npzcQ9nw14vODWm33heHw','Snakebite BMX',NULL,'https://www.youtube.com/channel/UC8npzcQ9nw14vODWm33heHw',0,0,0,NULL,0,'2026-10-09 17:55:10',0,0);
/*!40000 ALTER TABLE `aggro_videos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

