# 2015-07-22

ALTER TABLE  `groups` ADD  `group_opd` VARCHAR( 255 ) NOT NULL ,
ADD  `group_logo` VARCHAR( 255 ) NOT NULL ,
ADD  `group_url` VARCHAR( 255 ) NOT NULL ,
ADD  `group_updated` DATETIME NOT NULL,
ADD  `group_ena` TINYINT(1) NOT NULL

# 2015-07-23
ALTER TABLE  `groupLinks` ADD  `link_updated` DATETIME NOT NULL ,
ADD  `link_ena` TINYINT(1) NOT NULL