CREATE TABLE `album` (
  `key` varchar(20) NOT NULL default '',
  `longkey` varchar(20) default NULL,
  `artistKey` varchar(10) default NULL,
  `name` varchar(255) default NULL,
  `icon` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  `isExplicit` tinyint(4) default NULL,
  `isClean` tinyint(4) default NULL,
  `canStream` tinyint(4) default NULL,
  `shortUrl` varchar(255) default NULL,
  `embedUrl` varchar(255) default NULL,
  `displayDate` datetime default NULL,
  `releaseDate` datetime default NULL,
  `duration` mediumtext,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;


CREATE TABLE `artist` (
  `key` varchar(10) NOT NULL default '',
  `name` varchar(255) default NULL,
  `url` varchar(255) default NULL,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

CREATE TABLE `queue` (
  `id` int(11) NOT NULL auto_increment,
  `trackKey` varchar(10) default NULL,
  `userKey` varchar(10) default NULL,
  `added` int(11) default NULL,
  `startplay` int(11) default NULL,
  `endplay` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1725 DEFAULT CHARSET=latin1;

CREATE TABLE `track` (
  `key` varchar(20) NOT NULL default '',
  `albumKey` varchar(10) default NULL,
  `artistKey` varchar(10) default NULL,
  `name` varchar(255) default NULL,
  `trackNum` int(11) default NULL,
  `shortUrl` varchar(255) default NULL,
  `duration` int(11) default NULL,
  `isExplicit` tinyint(4) default NULL,
  `isClean` tinyint(4) default NULL,
  `canStream` tinyint(4) default '1',
  `requested` tinyint(4) NOT NULL default '0',
  `lastqueue` int(11) default NULL,
  `rnd` float default NULL,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `user` (
  `key` varchar(10) NOT NULL,
  `username` varchar(32) NOT NULL,
  `firstName` varchar(100) default NULL,
  `lastName` varchar(100) default NULL,
  `icon` varchar(255) default NULL,
  `gender` varchar(1) default NULL,
  `state` smallint(6) default '0',
  `token` varchar(64) default NULL,
  `secret` varchar(64) default NULL,
  `lastseen` int(11) default NULL,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

