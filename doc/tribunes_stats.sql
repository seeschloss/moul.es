-- MySQL dump 10.15  Distrib 10.0.22-MariaDB, for Linux (x86_64)
--
-- Host: mysql.seos.fr    Database: tribunes_stats
-- ------------------------------------------------------
-- Server version	10.0.21-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `dlfp`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dlfp` (
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` text NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `totoz` int(11) NOT NULL DEFAULT '0',
  `bold` int(11) NOT NULL DEFAULT '0',
  `url` int(11) NOT NULL DEFAULT '0',
  `prems` int(11) NOT NULL DEFAULT '0',
  `deuz` int(11) NOT NULL DEFAULT '0',
  `length` int(11) NOT NULL DEFAULT '0',
  `url_domain` varchar(255) NOT NULL DEFAULT '',
  `horloge` int(11) NOT NULL DEFAULT '0',
  `naked_url` int(11) NOT NULL DEFAULT '0',
  `question` int(11) NOT NULL DEFAULT '0',
  `ta_gueule_answer` int(11) NOT NULL DEFAULT '0',
  `username` text NOT NULL,
  UNIQUE KEY `id_time_unique` (`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`),
  KEY `totoz` (`totoz`),
  KEY `bold` (`bold`),
  KEY `url` (`url`),
  KEY `prems` (`prems`),
  KEY `deuz` (`deuz`),
  KEY `length` (`length`),
  KEY `horloge` (`horloge`),
  KEY `naked_url` (`naked_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dlfp_answers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dlfp_answers` (
  `post_source_id` int(11) NOT NULL,
  `post_source_time` varchar(14) NOT NULL,
  `post_target_id` int(11) NOT NULL,
  `post_source_clock_id` int(11) NOT NULL,
  `post_source_clock` varchar(11) NOT NULL,
  PRIMARY KEY (`post_source_id`,`post_source_time`,`post_source_clock_id`),
  KEY `target` (`post_target_id`),
  KEY `source` (`post_source_id`),
  KEY `source_unique` (`post_source_id`,`post_source_time`),
  KEY `post_source_id` (`post_source_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `dlfp_latest`
--

SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `dlfp_latest` (
  `id` tinyint NOT NULL,
  `time` tinyint NOT NULL,
  `info` tinyint NOT NULL,
  `login` tinyint NOT NULL,
  `message` tinyint NOT NULL,
  `totoz` tinyint NOT NULL,
  `bold` tinyint NOT NULL,
  `url` tinyint NOT NULL,
  `prems` tinyint NOT NULL,
  `deuz` tinyint NOT NULL,
  `length` tinyint NOT NULL,
  `url_domain` tinyint NOT NULL,
  `horloge` tinyint NOT NULL,
  `naked_url` tinyint NOT NULL,
  `question` tinyint NOT NULL,
  `username` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `dlfp_mem`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dlfp_mem` (
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` varchar(255) NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` varchar(511) NOT NULL,
  `totoz` int(11) NOT NULL DEFAULT '0',
  `bold` int(11) NOT NULL DEFAULT '0',
  `url` int(11) NOT NULL DEFAULT '0',
  `prems` int(11) NOT NULL DEFAULT '0',
  `deuz` int(11) NOT NULL DEFAULT '0',
  `length` int(11) NOT NULL DEFAULT '0',
  `url_domain` varchar(255) NOT NULL DEFAULT '',
  `horloge` int(11) NOT NULL DEFAULT '0',
  `naked_url` int(11) NOT NULL DEFAULT '0',
  `question` int(11) NOT NULL DEFAULT '0',
  `ta_gueule_answer` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL,
  UNIQUE KEY `id_time_unique` (`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`),
  KEY `totoz` (`totoz`),
  KEY `bold` (`bold`),
  KEY `url` (`url`),
  KEY `prems` (`prems`),
  KEY `deuz` (`deuz`),
  KEY `length` (`length`),
  KEY `horloge` (`horloge`),
  KEY `naked_url` (`naked_url`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dlfp_tmp`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dlfp_tmp` (
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` text NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `totoz` int(11) NOT NULL DEFAULT '0',
  `bold` int(11) NOT NULL DEFAULT '0',
  `url` int(11) NOT NULL DEFAULT '0',
  `prems` int(11) NOT NULL DEFAULT '0',
  `deuz` int(11) NOT NULL DEFAULT '0',
  `length` int(11) NOT NULL DEFAULT '0',
  `url_domain` varchar(255) NOT NULL DEFAULT '',
  `horloge` int(11) NOT NULL DEFAULT '0',
  `naked_url` int(11) NOT NULL DEFAULT '0',
  `question` int(11) NOT NULL DEFAULT '0',
  `username` text NOT NULL,
  UNIQUE KEY `id_time_unique` (`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`),
  KEY `totoz` (`totoz`),
  KEY `bold` (`bold`),
  KEY `url` (`url`),
  KEY `prems` (`prems`),
  KEY `deuz` (`deuz`),
  KEY `length` (`length`),
  KEY `horloge` (`horloge`),
  KEY `naked_url` (`naked_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `euromussels`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `euromussels` (
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` text NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `totoz` int(11) NOT NULL DEFAULT '0',
  `bold` int(11) NOT NULL DEFAULT '0',
  `url` int(11) NOT NULL DEFAULT '0',
  `prems` int(11) NOT NULL DEFAULT '0',
  `deuz` int(11) NOT NULL DEFAULT '0',
  `length` int(11) NOT NULL DEFAULT '0',
  `url_domain` varchar(255) NOT NULL DEFAULT '',
  `horloge` int(11) NOT NULL DEFAULT '0',
  `naked_url` int(11) NOT NULL DEFAULT '0',
  `question` int(11) NOT NULL DEFAULT '0',
  `username` text NOT NULL,
  UNIQUE KEY `id_time_unique` (`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`),
  KEY `totoz` (`totoz`),
  KEY `bold` (`bold`),
  KEY `url` (`url`),
  KEY `prems` (`prems`),
  KEY `deuz` (`deuz`),
  KEY `length` (`length`),
  KEY `horloge` (`horloge`),
  KEY `naked_url` (`naked_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fortunes_dlfp`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fortunes_dlfp` (
  `fortune_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` text NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `fortune_login` varchar(20) NOT NULL DEFAULT '',
  `fortune_time` varchar(14) NOT NULL DEFAULT '',
  `fortune_info` text NOT NULL,
  `fortune_post_id` int(11) NOT NULL DEFAULT '0',
  `fortune_message` text NOT NULL,
  UNIQUE KEY `fortune_post` (`fortune_id`,`fortune_post_id`,`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fortunes_euromussels`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fortunes_euromussels` (
  `fortune_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` text NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `fortune_login` varchar(20) NOT NULL DEFAULT '',
  `fortune_time` varchar(14) NOT NULL DEFAULT '',
  `fortune_info` text NOT NULL,
  `fortune_post_id` int(11) NOT NULL DEFAULT '0',
  `fortune_message` text NOT NULL,
  UNIQUE KEY `fortune_post` (`fortune_id`,`fortune_post_id`,`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hadoken`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hadoken` (
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` text NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `totoz` int(11) NOT NULL DEFAULT '0',
  `bold` int(11) NOT NULL DEFAULT '0',
  `url` int(11) NOT NULL DEFAULT '0',
  `prems` int(11) NOT NULL DEFAULT '0',
  `deuz` int(11) NOT NULL DEFAULT '0',
  `length` int(11) NOT NULL DEFAULT '0',
  `url_domain` varchar(255) NOT NULL DEFAULT '',
  `horloge` int(11) NOT NULL DEFAULT '0',
  `naked_url` int(11) NOT NULL DEFAULT '0',
  `question` int(11) NOT NULL DEFAULT '0',
  `username` text NOT NULL,
  UNIQUE KEY `id_time_unique` (`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`),
  KEY `totoz` (`totoz`),
  KEY `bold` (`bold`),
  KEY `url` (`url`),
  KEY `prems` (`prems`),
  KEY `deuz` (`deuz`),
  KEY `length` (`length`),
  KEY `horloge` (`horloge`),
  KEY `naked_url` (`naked_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moules`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moules` (
  `id` int(11) NOT NULL,
  `time` varchar(14) NOT NULL,
  `info` text NOT NULL,
  `login` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `totoz` int(11) NOT NULL DEFAULT '0',
  `bold` int(11) NOT NULL DEFAULT '0',
  `url` int(11) NOT NULL DEFAULT '0',
  `prems` int(11) NOT NULL DEFAULT '0',
  `deuz` int(11) NOT NULL DEFAULT '0',
  `length` int(11) NOT NULL DEFAULT '0',
  `url_domain` varchar(255) NOT NULL DEFAULT '',
  `horloge` int(11) NOT NULL DEFAULT '0',
  `naked_url` int(11) NOT NULL DEFAULT '0',
  `question` int(11) NOT NULL DEFAULT '0',
  `username` text NOT NULL,
  UNIQUE KEY `id_time_unique` (`id`,`time`),
  KEY `id` (`id`),
  KEY `time` (`time`),
  KEY `login` (`login`),
  KEY `totoz` (`totoz`),
  KEY `bold` (`bold`),
  KEY `url` (`url`),
  KEY `prems` (`prems`),
  KEY `deuz` (`deuz`),
  KEY `length` (`length`),
  KEY `horloge` (`horloge`),
  KEY `naked_url` (`naked_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='utf8_general_ci';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `dlfp_latest`
--

/*!50001 DROP TABLE IF EXISTS `dlfp_latest`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `dlfp_latest` AS select `dlfp`.`id` AS `id`,`dlfp`.`time` AS `time`,`dlfp`.`info` AS `info`,`dlfp`.`login` AS `login`,`dlfp`.`message` AS `message`,`dlfp`.`totoz` AS `totoz`,`dlfp`.`bold` AS `bold`,`dlfp`.`url` AS `url`,`dlfp`.`prems` AS `prems`,`dlfp`.`deuz` AS `deuz`,`dlfp`.`length` AS `length`,`dlfp`.`url_domain` AS `url_domain`,`dlfp`.`horloge` AS `horloge`,`dlfp`.`naked_url` AS `naked_url`,`dlfp`.`question` AS `question`,`dlfp`.`username` AS `username` from `dlfp` order by `dlfp`.`time` desc limit 100000 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-12-05 14:04:44
