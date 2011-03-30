CREATE TABLE `queue` (
  `id` int(11) NOT NULL auto_increment,
  `trackKey` varchar(10) default NULL,
  `userKey` varchar(10) default NULL,
  `added` int(11) default NULL,
  `startplay` int(11) default NULL,
  `endplay` int(11) default NULL,
  PRIMARY KEY  (`id`)
)

CREATE TABLE `user` (
  `key` varchar(10) NOT NULL,
  `state` smallint(6) default '0',
  `token` varchar(64) default NULL,
  `secret` varchar(64) default NULL,
  `lastseen` int(11) default NULL,
  PRIMARY KEY  (`key`)
)

CREATE TABLE `api_usage` (
  `id` int(11) NOT NULL auto_increment,
  `user` varchar(200) default NULL,
  `executed` int(11) default NULL,
  `params` varchar(200) default NULL,
  `return` mediumtext default NULL,
  PRIMARY KEY  (`id`)
)
