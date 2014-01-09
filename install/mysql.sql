-- MySQL dump 10.13  Distrib 5.5.31, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: equipment
-- ------------------------------------------------------
-- Server version	5.5.31-0+wheezy1

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
-- Table structure for table `autoOPDs`
--

DROP TABLE IF EXISTS `autoOPDs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `autoOPDs` (
  `opd_id` varchar(255) NOT NULL,
  `opd_url` varchar(255) NOT NULL,
  `opd_ena` tinyint(1) NOT NULL,
  `opd_firstseen` datetime NOT NULL,
  `opd_lastseen` datetime NOT NULL,
  `opd_type` varchar(255) NOT NULL,
  `opd_cache` text NOT NULL,
  `opd_src` varchar(20) NOT NULL,
  PRIMARY KEY (`opd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crawls`
--

DROP TABLE IF EXISTS `crawls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crawls` (
  `crawl_id` int(11) NOT NULL AUTO_INCREMENT,
  `crawl_dataset` varchar(255) NOT NULL,
  `crawl_timestamp` datetime NOT NULL,
  `crawl_success` varchar(12) NOT NULL,
  `crawl_records` int(11) NOT NULL,
  `crawl_notes` text NOT NULL,
  `crawl_gong` varchar(12) NOT NULL,
  `crawl_gong_json` text NOT NULL,
  PRIMARY KEY (`crawl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datasets`
--

DROP TABLE IF EXISTS `datasets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datasets` (
  `data_uri` varchar(255) NOT NULL,
  `data_org` varchar(255) NOT NULL,
  `data_conforms` varchar(255) NOT NULL,
  `data_license` varchar(255) NOT NULL,
  `data_contact` varchar(255) NOT NULL,
  `data_corrections` varchar(255) NOT NULL,
  `data_type` varchar(255) NOT NULL,
  `data_firstseen` datetime NOT NULL,
  `data_lastseen` datetime NOT NULL,
  `data_ena` int(11) NOT NULL,
  `data_hash` varchar(32) NOT NULL,
  `data_crawl` int(11) NOT NULL,
  `data_src` varchar(32) NOT NULL,
  PRIMARY KEY (`data_uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemPages`
--

DROP TABLE IF EXISTS `itemPages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemPages` (
  `page_id` varchar(64) NOT NULL,
  `page_org` varchar(255) NOT NULL,
  `page_dataset` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_content` text NOT NULL,
  `page_updated` datetime NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemRDF`
--

DROP TABLE IF EXISTS `itemRDF`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemRDF` (
  `rdf_id` varchar(64) NOT NULL,
  `rdf_org` varchar(255) NOT NULL,
  `rdf_dataset` varchar(255) NOT NULL,
  `rdf_rdf` text NOT NULL,
  `rdf_updated` datetime NOT NULL,
  PRIMARY KEY (`rdf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemUniquips`
--

DROP TABLE IF EXISTS `itemUniquips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemUniquips` (
  `itemU_id` varchar(64) NOT NULL,
  `itemU_org` varchar(255) NOT NULL,
  `itemU_dataset` varchar(255) NOT NULL,
  `itemU_updated` datetime NOT NULL,
  `itemU_f_type` varchar(255) NOT NULL,
  `itemU_f_name` varchar(255) NOT NULL,
  `itemU_f_desc` text NOT NULL,
  `itemU_f_facid` varchar(255) NOT NULL,
  `itemU_f_technique` varchar(255) NOT NULL,
  `itemU_f_location` varchar(255) NOT NULL,
  `itemU_f_contactname` varchar(255) NOT NULL,
  `itemU_f_contacttel` varchar(255) NOT NULL,
  `itemU_f_contacturl` varchar(255) NOT NULL,
  `itemU_f_contactemail` varchar(255) NOT NULL,
  `itemU_f_contact2name` varchar(255) NOT NULL,
  `itemU_f_contact2tel` varchar(255) NOT NULL,
  `itemU_f_contact2url` varchar(255) NOT NULL,
  `itemU_f_contact2email` varchar(255) NOT NULL,
  `itemU_f_lid` varchar(255) NOT NULL,
  `itemU_f_photo` varchar(255) NOT NULL,
  `itemU_f_department` varchar(255) NOT NULL,
  `itemU_f_sitelocation` varchar(255) NOT NULL,
  `itemU_f_building` varchar(255) NOT NULL,
  `itemU_f_servicelevel` varchar(255) NOT NULL,
  `itemU_f_url` varchar(255) NOT NULL,
  PRIMARY KEY (`itemU_id`),
  KEY `itemU_org` (`itemU_org`),
  KEY `itemU_dataset` (`itemU_dataset`),
  FULLTEXT KEY `textsearch` (`itemU_f_name`,`itemU_f_desc`,`itemU_f_technique`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `item_id` varchar(64) NOT NULL,
  `item_org` varchar(255) NOT NULL,
  `item_dataset` varchar(255) NOT NULL,
  `item_location` varchar(255) NOT NULL,
  `item_updated` datetime NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `loc_uri` varchar(255) NOT NULL,
  `loc_point` point NOT NULL,
  `loc_lat` float NOT NULL,
  `loc_long` float NOT NULL,
  `loc_easting` int(11) NOT NULL,
  `loc_northing` int(11) NOT NULL,
  `loc_updated` datetime NOT NULL,
  PRIMARY KEY (`loc_uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orgs`
--

DROP TABLE IF EXISTS `orgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orgs` (
  `org_uri` varchar(255) NOT NULL,
  `org_idscheme` varchar(20) NOT NULL,
  `org_id` varchar(64) NOT NULL,
  `org_name` varchar(255) NOT NULL,
  `org_sort` varchar(255) NOT NULL,
  `org_url` varchar(255) NOT NULL,
  `org_logo` varchar(255) NOT NULL,
  `org_location` varchar(255) NOT NULL,
  `org_ena` int(11) NOT NULL,
  `org_firstseen` datetime NOT NULL,
  `org_lastseen` datetime NOT NULL,
  PRIMARY KEY (`org_uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-01-07  9:58:55
