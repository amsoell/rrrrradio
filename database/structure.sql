CREATE TABLE `api_usage` (
  `id` int(11) NOT NULL auto_increment,
  `api` varchar(20) default NULL,
  `user` varchar(200) default NULL,
  `executed` int(11) default NULL,
  `params` varchar(200) default NULL,
  `return` mediumtext,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `mark` (
  `userKey` varchar(10) NOT NULL default '',
  `trackKey` varchar(10) NOT NULL default '',
  `mark` tinyint(4) default NULL,
  PRIMARY KEY  (`userKey`,`trackKey`)
);

CREATE TABLE `queue` (
  `id` int(11) NOT NULL auto_increment,
  `trackKey` varchar(10) default NULL,
  `albumKey` varchar(10) default NULL,
  `artistKey` varchar(10) default NULL,
  `userKey` varchar(10) default NULL,
  `free` tinyint(4) NOT NULL default '0',
  `added` int(11) default NULL,
  `startplay` int(11) default NULL,
  `endplay` int(11) default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `user` (
  `key` varchar(10) NOT NULL,
  `state` smallint(6) default '0',
  `token` varchar(64) default NULL,
  `secret` varchar(64) default NULL,
  `lastseen` int(11) default NULL,
  PRIMARY KEY  (`key`)
);

CREATE TABLE `vote` (
  `userKey` varchar(10) NOT NULL default '',
  `trackKey` varchar(10) NOT NULL default '',
  `vote` int(11) default NULL,
  PRIMARY KEY  (`userKey`,`trackKey`)
);
