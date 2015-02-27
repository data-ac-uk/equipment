-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 27, 2015 at 10:22 AM
-- Server version: 5.5.40
-- PHP Version: 5.4.35-0+deb7u2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `equipment`
--

-- --------------------------------------------------------

--
-- Table structure for table `autoOPDs`
--

CREATE TABLE IF NOT EXISTS `autoOPDs` (
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

-- --------------------------------------------------------

--
-- Table structure for table `crawls`
--

CREATE TABLE IF NOT EXISTS `crawls` (
  `crawl_id` int(11) NOT NULL AUTO_INCREMENT,
  `crawl_dataset` varchar(255) NOT NULL,
  `crawl_timestamp` datetime NOT NULL,
  `crawl_success` varchar(12) NOT NULL,
  `crawl_records` int(11) NOT NULL,
  `crawl_notes` text NOT NULL,
  `crawl_gong` varchar(12) NOT NULL,
  `crawl_gong_json` text NOT NULL,
  PRIMARY KEY (`crawl_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `datasets`
--

CREATE TABLE IF NOT EXISTS `datasets` (
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

-- --------------------------------------------------------

--
-- Table structure for table `groupLinks`
--

CREATE TABLE IF NOT EXISTS `groupLinks` (
  `link_group` varchar(255) NOT NULL,
  `link_org` varchar(255) NOT NULL,
  PRIMARY KEY (`link_group`,`link_org`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` varchar(255) NOT NULL,
  `group_type` varchar(127) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `group_sname` varchar(255) NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `group_type` (`group_type`,`group_sname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `itemPages`
--

CREATE TABLE IF NOT EXISTS `itemPages` (
  `page_id` varchar(64) NOT NULL,
  `page_org` varchar(255) NOT NULL,
  `page_dataset` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_content` text NOT NULL,
  `page_updated` datetime NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `itemRDF`
--

CREATE TABLE IF NOT EXISTS `itemRDF` (
  `rdf_id` varchar(64) NOT NULL,
  `rdf_org` varchar(255) NOT NULL,
  `rdf_dataset` varchar(255) NOT NULL,
  `rdf_rdf` text NOT NULL,
  `rdf_updated` datetime NOT NULL,
  PRIMARY KEY (`rdf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `item_id` varchar(64) NOT NULL,
  `item_org` varchar(255) NOT NULL,
  `item_dataset` varchar(255) NOT NULL,
  `item_location` varchar(255) NOT NULL,
  `item_updated` datetime NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `itemUniquips`
--

CREATE TABLE IF NOT EXISTS `itemUniquips` (
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

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `loc_uri` varchar(255) NOT NULL,
  `loc_point` point NOT NULL,
  `loc_lat` float NOT NULL,
  `loc_long` float NOT NULL,
  `loc_easting` int(11) NOT NULL,
  `loc_northing` int(11) NOT NULL,
  `loc_updated` datetime NOT NULL,
  PRIMARY KEY (`loc_uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `message_to` varchar(255) NOT NULL,
  `message_subject` varchar(255) NOT NULL,
  `message_body` text NOT NULL,
  `message_type` varchar(20) NOT NULL,
  `message_link` varchar(255) NOT NULL,
  `message_time` datetime NOT NULL,
  `message_sent` tinyint(1) NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `orgs`
--

CREATE TABLE IF NOT EXISTS `orgs` (
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

-- --------------------------------------------------------

--
-- Table structure for table `orgsManual`
--

CREATE TABLE IF NOT EXISTS `orgsManual` (
  `org_xid` varchar(255) NOT NULL,
  `dataset_type` varchar(32) NOT NULL,
  `dataset_url` varchar(255) NOT NULL,
  `org_name` varchar(255) NOT NULL,
  `org_url` varchar(255) NOT NULL,
  `org_logo` varchar(255) NOT NULL,
  `dataset_corrections` varchar(255) NOT NULL,
  `dataset_contact` varchar(255) NOT NULL,
  `org_enabled` tinyint(1) NOT NULL,
  `org_comment` text NOT NULL,
  PRIMARY KEY (`org_xid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `statsIPOwner`
--

CREATE TABLE IF NOT EXISTS `statsIPOwner` (
  `ip_address` varchar(32) NOT NULL,
  `ip_owner` varchar(255) NOT NULL,
  `ip_date` datetime NOT NULL,
  PRIMARY KEY (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `statsSearchTerms`
--

CREATE TABLE IF NOT EXISTS `statsSearchTerms` (
  `search_key` varchar(127) NOT NULL,
  `search_ip` varchar(16) NOT NULL,
  `search_date` datetime NOT NULL,
  `search_term` varchar(255) NOT NULL,
  `search_owner` varchar(255) NOT NULL,
  PRIMARY KEY (`search_key`),
  KEY `search_term` (`search_term`),
  KEY `search_owner` (`search_owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
