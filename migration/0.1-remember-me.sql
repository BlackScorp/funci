ALTER TABLE `users`
	ADD COLUMN `rememberMeToken` VARCHAR(64) NULL DEFAULT NULL AFTER `passwordHash`,
	ADD INDEX `rememberMeToken` (`rememberMeToken`);