CREATE TABLE `ap_agora` (
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `lang` VARCHAR(255) DEFAULT NULL,
  `timezone` VARCHAR(255) DEFAULT NULL,
  `wallpaper` VARCHAR(255) DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `logoUrl` VARCHAR(255) DEFAULT NULL,
  `logoConnect` VARCHAR(255) DEFAULT NULL,
  `dateUpdateDb` DATETIME NOT NULL,
  `version_agora` VARCHAR(255) DEFAULT NULL,
  `skin` VARCHAR(255) DEFAULT 'white',
  `footerHtml` TEXT DEFAULT NULL,
  `usersLike` TINYINT DEFAULT NULL,
  `usersComment` TINYINT DEFAULT '1',
  `mapTool` VARCHAR(255) DEFAULT 'gmap',
  `gApiKey` VARCHAR(255) DEFAULT NULL,
  `gIdentity` TINYINT DEFAULT '1',
  `gIdentityClientId` VARCHAR(255) DEFAULT NULL,
  `messengerDisplay` TINYINT DEFAULT '1',
  `moduleLabelDisplay` TINYINT DEFAULT '1',
  `folderDisplayMode` VARCHAR(255) DEFAULT 'block',
  `personsSort` VARCHAR(255) DEFAULT 'firstName',
  `userMailDisplay` TINYINT DEFAULT NULL,
  `logsTimeOut` SMALLINT DEFAULT '120',
  `visioHost` VARCHAR(255) DEFAULT NULL,
  `visioHostAlt` VARCHAR(255) DEFAULT NULL,
  `sendmailFrom` VARCHAR(255) DEFAULT NULL,
  `smtpHost` VARCHAR(255) DEFAULT NULL,
  `smtpPort` SMALLINT DEFAULT NULL,
  `smtpSecure` VARCHAR(255) DEFAULT NULL,
  `smtpUsername` VARCHAR(255) DEFAULT NULL,
  `smtpPass` VARCHAR(255) DEFAULT NULL,
  `ldap_server` VARCHAR(255) DEFAULT NULL,
  `ldap_server_port` VARCHAR(255) DEFAULT NULL,
  `ldap_admin_login` VARCHAR(255) DEFAULT NULL,
  `ldap_admin_pass` VARCHAR(255) DEFAULT NULL,
  `ldap_base_dn` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendar` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `timeSlot` VARCHAR(255) DEFAULT NULL,
  `propositionNotify` VARCHAR(1) DEFAULT NULL,
  `propositionGuest` VARCHAR(1) DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarEvent` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT CHARACTER SET utf8mb4 DEFAULT NULL,
  `dateBegin` DATETIME DEFAULT NULL,
  `dateEnd` DATETIME DEFAULT NULL,
  `allDay` TINYINT UNSIGNED DEFAULT NULL,
  `location` VARCHAR(500) DEFAULT NULL,
  `_idCat` INT DEFAULT NULL,
  `important` TINYINT DEFAULT NULL,
  `contentVisible` VARCHAR(255) DEFAULT NULL,
  `visioUrl` VARCHAR(255) DEFAULT NULL,
  `periodType` VARCHAR(255) DEFAULT NULL,
  `periodValues` VARCHAR(1000) DEFAULT NULL,
  `periodDateEnd` DATE DEFAULT NULL,
  `periodDateExceptions` TEXT DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `guest` VARCHAR(255) DEFAULT NULL,
  `guestMail` VARCHAR(255) DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarEventAffectation` (
  `_idEvt` INT NOT NULL,
  `_idCal` INT NOT NULL,
  `confirmed` TINYINT DEFAULT NULL,
  KEY `indexes` (`_idCal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarCategory` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idSpaces` TEXT DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `color` VARCHAR(255) DEFAULT NULL,
  `rank` SMALLINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_contact` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `civility` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `firstName` VARCHAR(255) DEFAULT NULL,
  `companyOrganization` TEXT DEFAULT NULL,
  `function` TEXT DEFAULT NULL,
  `adress` TEXT DEFAULT NULL,
  `postalCode` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(255) DEFAULT NULL,
  `country` VARCHAR(255) DEFAULT NULL,
  `telephone` VARCHAR(255) DEFAULT NULL,
  `telmobile` VARCHAR(255) DEFAULT NULL,
  `mail` TEXT DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_contactFolder` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardNews` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `description` TEXT CHARACTER SET utf8mb4,
  `une` TINYINT DEFAULT NULL,
  `offline` TINYINT DEFAULT NULL,
  `dateOnline` DATETIME DEFAULT NULL,
  `dateOffline` DATETIME DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_file` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `octetSize` bigint DEFAULT NULL,
  `downloadsNb` INT NOT NULL DEFAULT '0',
  `downloadedBy` VARCHAR(10000) DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_fileFolder` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_fileVersion` (
  `_idFile` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `realName` TEXT NOT NULL,
  `octetSize` bigint NOT NULL,
  `description` TEXT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
   KEY `indexes` (`_idFile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumMessage` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idMessageParent` INT DEFAULT NULL,
  `_idContainer` INT DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT CHARACTER SET utf8mb4,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idMessageParent`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumSubject` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT CHARACTER SET utf8mb4,
  `_idTheme` INT DEFAULT NULL,
  `dateLastMessage` DATETIME DEFAULT NULL,
  `usersConsultLastMessage` VARCHAR(10000) DEFAULT NULL,
  `usersNotifyLastMessage` VARCHAR(10000) DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idTheme`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumTheme` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idSpaces` TEXT DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `color` VARCHAR(255) DEFAULT NULL,
  `rank` SMALLINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_invitation` (
  `_idInvitation` VARCHAR(255) DEFAULT NULL,
  `_idSpace` INT DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `firstName` VARCHAR(255) DEFAULT NULL,
  `mail` VARCHAR(255) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  KEY `indexes` (`_idInvitation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_joinSpaceModule` (
  `_idSpace` INT DEFAULT NULL,
  `moduleName` VARCHAR(255) DEFAULT NULL,
  `rank` TINYINT DEFAULT NULL,
  `options` TEXT DEFAULT NULL,
  KEY `indexes` (`_idSpace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_joinSpaceUser` (
  `_idSpace` INT DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `allUsers` TINYINT DEFAULT NULL,
  `accessRight` VARCHAR(255) DEFAULT NULL,
  KEY `indexes` (`_idSpace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_link` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `adress` TEXT NOT NULL,
  `description` TEXT DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_linkFolder` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_log` (
  `action` VARCHAR(50) DEFAULT NULL,
  `moduleName` VARCHAR(50) DEFAULT NULL,
  `objectType` VARCHAR(50) DEFAULT NULL,
  `_idObject` INT DEFAULT NULL,
  `date` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `_idSpace` INT DEFAULT NULL,
  `ip` VARCHAR(255) DEFAULT NULL,
  `comment` TEXT CHARACTER SET utf8mb4 DEFAULT NULL,
  KEY `indexes` (`action`,`_idObject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_mail` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `recipients` TEXT NOT NULL,
  `title` TEXT DEFAULT NULL,
  `description` TEXT CHARACTER SET utf8mb4 NOT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_objectAttachedFile` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `name` TEXT NOT NULL,
  `objectType` VARCHAR(255) NOT NULL,
  `_idObject` INT NOT NULL,
  `downloadsNb` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ap_objectComment` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `objectType` VARCHAR(255) NOT NULL,
  `_idObject` INT NOT NULL,
  `_idUser` INT NOT NULL,
  `dateCrea` DATETIME NOT NULL,
  `comment` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ap_objectLike` (
  `objectType` VARCHAR(255) NOT NULL,
  `_idObject` INT NOT NULL,
  `_idUser` INT NOT NULL,
  KEY `indexes` (`objectType`,`_idObject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_objectTarget` (
  `objectType` VARCHAR(255) NOT NULL,
  `_idObject` INT NOT NULL,
  `_idSpace` INT NOT NULL,
  `target` VARCHAR(255) NOT NULL,
  `accessRight` float NOT NULL,
  KEY `indexes` (`objectType`,`_idObject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_space` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `public` TINYINT DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `userInscription` TINYINT DEFAULT NULL,
  `userInscriptionNotify` TINYINT DEFAULT NULL,
  `usersInvitation` TINYINT DEFAULT NULL,
  `wallpaper` VARCHAR(255) DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_task` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `title` TEXT NOT NULL,
  `description` TEXT CHARACTER SET utf8mb4 DEFAULT NULL,
  `_idStatus` INT DEFAULT NULL,
  `priority` VARCHAR(255) DEFAULT NULL,
  `advancement` TINYINT DEFAULT NULL,
  `responsiblePersons` TEXT DEFAULT NULL,
  `dateBegin` DATE DEFAULT NULL,
  `dateEnd` DATE DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`), 
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_taskStatus` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idSpaces` TEXT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `color` VARCHAR(255) DEFAULT NULL,
  `rank` SMALLINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_taskFolder` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idContainer` INT NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_user` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `civility` VARCHAR(255) DEFAULT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `firstName` VARCHAR(255) DEFAULT NULL,
  `login` VARCHAR(255) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `adress` TEXT DEFAULT NULL,
  `postalCode` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(255) DEFAULT NULL,
  `country` VARCHAR(255) DEFAULT NULL,
  `telephone` VARCHAR(255) DEFAULT NULL,
  `telmobile` VARCHAR(255) DEFAULT NULL,
  `mail` TEXT DEFAULT NULL,
  `function` TEXT DEFAULT NULL,
  `companyOrganization` TEXT DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `lastConnection` INT DEFAULT NULL,
  `previousConnection` INT DEFAULT NULL,
  `generalAdmin` TINYINT DEFAULT NULL,
  `lang` VARCHAR(255) DEFAULT NULL,
  `connectionSpace` VARCHAR(255) DEFAULT NULL,
  `calendarDisabled` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userAuthToken` (
  `_idUser` INT NOT NULL,
  `userAuthToken` VARCHAR(255) NOT NULL,
  `browserId` VARCHAR(255),
  `dateCrea` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userGroup` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `_idSpace` INT NOT NULL,
  `_idUsers` TEXT NOT NULL,
  `dateCrea` DATETIME DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userInscription` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `_idSpace` INT NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `firstName` VARCHAR(255) DEFAULT NULL,
  `mail` VARCHAR(255) DEFAULT NULL,
  `login` VARCHAR(255) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userLivecouter` (
  `_idUser` INT NOT NULL DEFAULT '0',
  `ipAdress` VARCHAR(255) NOT NULL,
  `editTypeId` VARCHAR(255) DEFAULT NULL,
  `editorDraft` TEXT CHARACTER SET utf8mb4 DEFAULT NULL,
  `draftTypeId` VARCHAR(255) DEFAULT NULL,
  `date` INT DEFAULT NULL,
  PRIMARY KEY (`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userMessenger` (
  `_idUserMessenger` INT NOT NULL,
  `allUsers` TINYINT DEFAULT NULL,
  `_idUser` INT DEFAULT NULL,
  KEY `indexes` (`_idUserMessenger`,`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userMessengerMessage` (
  `_idUser` INT NOT NULL,
  `_idUsers` TEXT NOT NULL,
  `message` TEXT CHARACTER SET utf8mb4 NOT NULL,
  `date` INT DEFAULT NULL,
  KEY `indexes` (`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userPreference` (
  `_idUser` INT DEFAULT NULL,
  `keyVal` VARCHAR(255) DEFAULT NULL,
  `value` VARCHAR(255) DEFAULT NULL,
  KEY `indexes` (`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPoll` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT CHARACTER SET utf8mb4 DEFAULT NULL,
  `dateEnd` DATE DEFAULT NULL,
  `multipleResponses` TINYINT DEFAULT NULL,
  `toVoteWithNews` TINYINT DEFAULT NULL,
  `publicVote` TINYINT DEFAULT NULL,
  `dateCrea` DATETIME NOT NULL,
  `_idUser` INT NOT NULL,
  `dateModif` DATETIME DEFAULT NULL,
  `_idUserModif` INT DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPollResponse` (
  `_id` VARCHAR(255) NOT NULL,
  `_idPoll` INT NOT NULL,
  `label` VARCHAR(500) NOT NULL,
  `rank` SMALLINT NOT NULL,
  `fileName` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPollResponseVote` (
  `_idUser` INT NOT NULL,
  `_idResponse` VARCHAR(255) NOT NULL,
  `_idPoll` INT NOT NULL,
  PRIMARY KEY (`_idUser`,`_idResponse`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




INSERT INTO `ap_agora` SET `name`='Omnispace / Agora-Project', `dateUpdateDb`=NOW();

INSERT INTO `ap_space` SET `_id`=1, `name`='Agora', `usersInvitation`=1;

INSERT INTO `ap_user` SET `_id`=1, `generalAdmin`=1, `dateCrea`=NOW(), `_idUser`=1;

INSERT INTO `ap_userMessenger` SET `_idUserMessenger`=1, `allUsers`=1;

INSERT INTO `ap_joinSpaceUser` SET `_idSpace`=1, `allUsers`=1, `accessRight`=1;

INSERT INTO `ap_joinSpaceModule` (`_idSpace`, `moduleName`, `rank`) VALUES 
(1,'dashboard',1), 
(1,'file',2), 
(1,'calendar',3), 
(1,'task',4), 
(1,'forum',5), 
(1,'contact',6), 
(1,'link',7),
(1,'mail',8),  
(1,'user',9);

INSERT INTO `ap_calendar` (`_id`, `type`, `_idUser`, `title`) VALUES 
(1,'ressource',1,NULL), 
(2,'user',1,NULL);

INSERT INTO `ap_calendarCategory` (`_id`, `color`, `title`) VALUES 
(1,'#880000','Rendez-vous'), 
(2,'#000088','Reunion'), 
(3,'#dd7700','Vacances'), 
(4,'#007700','Personnel'), 
(5,'#bf0073','Evenement periodique');

INSERT INTO `ap_contactFolder` SET `_id`=1, `_idContainer`=0;
INSERT INTO `ap_fileFolder` SET `_id`=1, `_idContainer`=0;
INSERT INTO `ap_linkFolder` SET `_id`=1, `_idContainer`=0;
INSERT INTO `ap_taskFolder` SET `_id`=1, `_idContainer`=0;

INSERT INTO `ap_file` (`_id`, `_idContainer`, `name`, `description`, `octetSize`, `downloadsNb`, `dateCrea`, `_idUser`) VALUES
(1, 1, 'Documentation.pdf', 'Documentation', 228075, 1, NOW(), 1),
(2, 1, 'Photo 1.jpg', NULL, 172057, 1, NOW(), 1),
(3, 1, 'Photo 2.jpg', NULL, 214053, 1, NOW(), 1),
(4, 1, 'Photo 3.jpg', NULL, 280614, 1, NOW(), 1);

INSERT INTO `ap_fileVersion` (`_idFile`, `name`, `realName`, `octetSize`, `description`, `dateCrea`, `_idUser`) VALUES
(1, 'Documentation.pdf', '1_1514764800.pdf', 228075, 'Documentation', NOW(), 1),
(2, 'Photo 1.jpg', '2_1514764800.jpg', 172057, NULL, NOW(), 1),
(3, 'Photo 2.jpg', '3_1514764800.jpg', 214053, NULL, NOW(), 1),
(4, 'Photo 3.jpg', '4_1514764800.jpg', 280614, NULL, NOW(), 1);

INSERT INTO `ap_link` (`_id`, `_idContainer`, `adress`, `dateCrea`, `_idUser`) VALUES
(1, 1, 'https://www.omnispace.fr', NOW(), 1),
(2, 1, 'https://fr.wikipedia.org', NOW(), 1);

INSERT INTO `ap_objectTarget` (`objectType`, `_idObject`, `_idSpace`, `target`, `accessRight`) VALUES 
('calendar', 1, 1, 'spaceUsers', 1.5),
('calendar', 2, 1, 'spaceUsers', 1),
('contactFolder', 1, 1, 'spaceUsers', 2),
('linkFolder', 1, 1, 'spaceUsers', 2),
('taskFolder', 1, 1, 'spaceUsers', 2),
('fileFolder', 1, 1, 'spaceUsers', 2),
('file', 1, 1, 'spaceUsers', 1),
('file', 2, 1, 'spaceUsers', 1),
('file', 3, 1, 'spaceUsers', 1),
('file', 4, 1, 'spaceUsers', 1),
('link', 1, 1, 'spaceUsers', 1),
('link', 2, 1, 'spaceUsers', 1);