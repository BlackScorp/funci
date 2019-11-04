CREATE TABLE IF NOT EXISTS `users` (
  `userId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `rememberMeToken` varchar(64) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `unique_username` (`username`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `updated` (`updated`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `rememberMeToken` (`rememberMeToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

