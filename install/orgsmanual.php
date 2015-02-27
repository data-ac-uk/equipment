-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: 152.78.138.218
-- Generation Time: Feb 27, 2015 at 10:44 AM
-- Server version: 5.1.73
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `equipment`
--

--
-- Dumping data for table `orgsManual`
--

INSERT INTO `orgsManual` (`org_xid`, `dataset_type`, `dataset_url`, `org_name`, `org_url`, `org_logo`, `dataset_corrections`, `dataset_contact`, `org_enabled`, `org_comment`) VALUES
('other/X1', 'uniquip/csv', 'http://www.roslin.ed.ac.uk/equipment.data/ri_data.csv', 'The Roslin Institute', 'http://www.roslin.ed.ac.uk', 'http://www.roslin.ed.ac.uk/assets/logos/roslinlogo_250x92.png', 'mailto:info@roslin.ed.ac.uk', 'mailto:info@roslin.ed.ac.uk', 1, ''),
('other/X2', 'uniquip/csv', 'http://www.nbi.ac.uk/equipment/jic/equipment.csv', 'The John Innes Centre', 'http://www.jic.ac.uk', 'http://www.jic.ac.uk/images/jiclogo.gif', 'mailto:finance@nbi.ac.uk', 'mailto:finance@nbi.ac.uk', 1, ''),
('other/X3', 'uniquip/csv', 'http://www.rothamsted.ac.uk/equipment.csv', 'Rothamsted Research', 'http://www.rothamsted.ac.uk/', 'http://www.rothamsted.ac.uk/Graphics/LogoLarge.gif', 'mailto:stephen.james@rothamsted.ac.uk', 'mailto:stephen.james@rothamsted.ac.uk', 1, ''),
('other/X4', 'uniquip/csv', 'http://www.nbi.ac.uk/equipment/ifr/equipment.csv', 'Institute of Food Research', 'http://www.ifr.ac.uk', 'http://www.ifr.ac.uk/assets/images/global/ifr-logo.jpg', 'mailto:finance@nbi.ac.uk', 'mailto:finance@nbi.ac.uk', 1, ''),
('other/X5', 'uniquip/csv', 'http://www.nbi.ac.uk/equipment/tgac/equipment.csv', 'The Genome Analysis Centre', 'http://www.tgac.ac.uk', 'http://www.tgac.ac.uk/v2images/tgac_logo_single.png', 'mailto:finance@nbi.ac.uk', 'mailto:finance@nbi.ac.uk', 1, ''),
('other/X7', 'uniquip/csv', 'http://www.aber.ac.uk/en/media/departmental/ibers/iberskit/iberskit.csv', 'The Institute of Biological, Environmental and Rural Sciences', 'http://www.aber.ac.uk/en/ibers', 'http://www.aber.ac.uk/en/media/departmental/ibers/iberskit/IBERS-and-AU-logo.jpg', 'mailto:rmd@aber.ac.uk', 'mailto:rmd@aber.ac.uk', 1, ''),
('ukprn/10000824', 'uniquip/xlsx', 'http://research.bournemouth.ac.uk/wp-content/uploads/2014/01/bu-equipment.xls', 'University of Bournemouth', 'http://www.bournemouth.ac.uk/', 'http://upload.wikimedia.org/wikipedia/en/thumb/2/20/BournemouthUniversity.jpg/200px-BournemouthUniversity.jpg', 'mailto:jgarrad@bournemouth.ac.uk', 'mailto:jgarrad@bournemouth.ac.uk', 1, ''),
('ukprn/10004113', 'kitcat', 'http://equipment.lboro.ac.uk/api/public/items.json', 'University of Loughborough', 'http://www.lboro.ac.uk/', 'http://www.lboro.ac.uk/-images/lulogo.gif', 'mailto:kit-catalogue@lboro.ac.uk', 'mailto:kit-catalogue@lboro.ac.uk', 1, ''),
('ukprn/10006840', 'rdf', 'http://www.m5universities.ac.uk/equipment/__catalogue2/api/public/items.rdf?ou-name=University+of+Birmingham', 'University of Birmingham', 'http://www.birmingham.ac.uk/', 'http://www.birmingham.ac.uk/Images/website/logo.gif', 'mailto:a.c.jones@bham.ac.uk', 'mailto:a.c.jones@bham.ac.uk', 1, 'Added AJM 08/04/14: Use an asset register dump file, so for now they''ll probably only release data via M5.'),
('ukprn/10007154', 'rdf', 'http://www.m5universities.ac.uk/equipment/__catalogue2/api/public/items.rdf?ou-name=University+of+Nottingham', 'University of Nottingham', 'http://www.nottingham.ac.uk/', 'http://www.nottingham.ac.uk/siteelements/images/base/logo.png', 'mailto:Elizabeth.French@nottingham.ac.uk', 'mailto:Elizabeth.French@nottingham.ac.uk', 1, 'Added AJM 08/04/14: Have a private Kit-Catalogue installation, only available to their own staff.  It looks like they''ll open up their own data at some point, but I''m not sure if/when it will happen.\r\n'),
('ukprn/10007158', 'rdf', 'http://id.southampton.ac.uk/dataset/facilities/latest', 'University of Southampton', 'http://www.soton.ac.uk/', 'http://www.southampton.ac.uk/images/bg_logo_small.png', 'mailto:facshare@soton.ac.uk', 'mailto:facshare@soton.ac.uk', 0, ''),
('ukprn/10007160', 'uniquip/csv', 'http://magic.surrey.ac.uk/equipment/servlet/EquipmentFeed', 'University of Surrey', 'http://www.surrey.ac.uk/', 'http://upload.wikimedia.org/wikipedia/en/thumb/b/bd/University_of_Surrey_Logo.svg/248px-University_of_Surrey_Logo.svg.png', 'mailto:m.chenery@surrey.ac.uk', 'mailto:m.chenery@surrey.ac.uk', 1, ''),
('ukprn/10007163', 'kitcat', 'http://www.m5universities.ac.uk/equipment/__catalogue2/api/public/items.json?ou-name=University+of+Warwick', 'University of Warwick', 'http://www.warwick.ac.uk/', 'http://www2.warwick.ac.uk/marque.jpg', 'mailto:M.A.Barnett@warwick.ac.uk', 'mailto:M.A.Barnett@warwick.ac.uk', 1, 'Added AJM 08/04/14: Use an asset register for the data, but have their own web-based catalogue of services which they seem pretty happy with, so for now they''ll probably only release data via M5.'),
('ukprn/10007759', 'rdf', 'http://www.m5universities.ac.uk/equipment/__catalogue2/api/public/items.rdf?ou-name=Aston+University', 'Aston University', 'http://www.aston.ac.uk', 'http://static.aston.ac.uk/survey/aston%20logo.jpg', 'mailto:astonkit@aston.ac.uk', 'mailto:astonkit@aston.ac.uk', 1, 'Added AJM 08/04/14: Have small Kit-Catalogue installation, hopefully when they''ve catalogued more things we can have their open up the catalogue and its API so you can switch to a more authoritative, and hopefully more substantial, source of data.\r\n'),
('ukprn/10007774', 'rdf', 'https://source.data.ox.ac.uk/archive/public/research-facilities/latest.rdf', 'University of Oxford', 'http://www.ox.ac.uk/', 'https://static.data.ox.ac.uk/brandmark.gif', 'mailto:opendata@oucs.ox.ac.uk', 'mailto:opendata@oucs.ox.ac.uk', 1, ''),
('ukprn/10007779', 'uniquip/csv', 'http://www.rvc.ac.uk/Media/Default/Research/documents/equipment.csv', 'The Royal Veterinary College', 'http://www.rvc.ac.uk/', 'http://www.rvc.ac.uk/cf_images/Logo_RVC_150.jpg', 'mailto:ahibbert@rvc.ac.uk', 'mailto:ahibbert@rvc.ac.uk', 1, ''),
('ukprn/10007786', 'pure', 'http://www.equipsouthwest.org.uk/share/institution/bristol', 'University of Bristol', 'http://www.bristol.ac.uk/', 'http://www.bristol.ac.uk/media-library/sites/public-relations/images/logos/full-colour-png.png', 'mailto:Richard.Buist@bristol.ac.uk', 'mailto:Richard.Buist@bristol.ac.uk', 1, ''),
('ukprn/10007792', 'pure', 'http://www.equipsouthwest.org.uk/share/institution/exeter', 'University of Exeter', 'http://www.exeter.ac.uk', 'http://www.exeter.ac.uk/designstudio/visualidentity/downloads/logos/colour_logo.jpg', 'mailto:S.Trowell@exeter.ac.uk', 'mailto:S.Trowell@exeter.ac.uk', 1, ''),
('ukprn/10007794', 'uniquip/xlsx', 'http://web.eng.gla.ac.uk/jwnc/JWNC_database_spreadsheet.xlsx', 'University of Glasgow', 'http://www.gla.ac.uk', 'http://www.gla.ac.uk/0t4/generic/images/logo_print.gif', 'mailto:Linsey.Robertson@glasgow.ac.uk', 'mailto:Linsey.Robertson@glasgow.ac.uk', 1, ''),
('ukprn/10007795', 'rdf', 'http://data.leeds.ac.uk/equipment/', 'University of Leeds', 'http://www.leeds.ac.uk/', 'http://data.leeds.ac.uk/assets/img/logo.png', 'mailto:g.burnell@leeds.ac.uk', 'mailto:g.burnell@leeds.ac.uk', 1, ''),
('ukprn/10007796', 'kitcat', 'http://www.m5universities.ac.uk/equipment/__catalogue2/api/public/items.json?ou-name=University+of+Leicester', 'University of Leicester', 'http://www.le.ac.uk/', 'http://www2.le.ac.uk/research/festival/images/University%20of%20Leicester%20Logo.JPG', 'mailto:bb118@leicester.ac.uk', 'mailto:bb118@leicester.ac.uk', 1, 'Added AJM 09/04/14:Have a publically viewable Kit-Catalogue installation, but haven''t enabled the API.'),
('ukprn/10007814', 'pure', 'http://equipsouthwest.org.uk/share/institution/cardiff', 'Cardiff University', 'http://www.cardiff.ac.uk/', 'http://sites.cardiff.ac.uk/brandtoolkit/files/2013/11/universitylogo1-300x288.jpg', 'mailto:equipment@cardiff.ac.uk', 'mailto:equipment@cardiff.ac.uk', 1, ''),
('ukprn/10007850', 'pure', 'http://www.equipsouthwest.org.uk/share/institution/bath', 'University of Bath', 'http://www.bath.ac.uk', 'http://www.bath.ac.uk/homepage/images/logos/logo.gif', 'mailto:research-equipment-sharing@bath.ac.uk', 'mailto:research-equipment-sharing@bath.ac.uk', 1, ''),
('ukprn/10032038', 'uniquip/csv', 'http://www.babraham.ac.uk/biskit/biskit.csv', 'The Babraham Institute', 'http://www.babraham.ac.uk', 'http://www.babraham.ac.uk/img11/logo/BI-2010.png', 'mailto:michael.hinton@babraham.ac.uk', 'mailto:michael.hinton@babraham.ac.uk', 1, ''),
('ukprn/10033892', 'uniquip/xlsx', 'http://www.pirbright.ac.uk/KitOnARope/KitOnARope.xlsx', 'The Pirbright Institute', 'http://www.pirbright.ac.uk/', 'http://www.pirbright.ac.uk/images/Logos/Pirbright_HiRes.jpg', 'mailto:caroline.head@pirbright.ac.uk', 'mailto:caroline.head@pirbright.ac.uk', 0, '');
