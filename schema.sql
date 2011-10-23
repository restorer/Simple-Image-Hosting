CREATE TABLE IF NOT EXISTS `images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8_bin NOT NULL,
  `orig_filename` varchar(255) COLLATE utf8_bin NOT NULL,
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `adsense` varchar(255) COLLATE utf8_bin NOT NULL,
  `deletion_code` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` varchar(255) COLLATE utf8_bin NOT NULL,
  `uploaded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastshow` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `orig_width` int(10) unsigned NOT NULL DEFAULT '0',
  `orig_height` int(10) unsigned NOT NULL DEFAULT '0',
  `thumb_width` int(10) unsigned NOT NULL DEFAULT '0',
  `thumb_height` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
