CREATE TABLE `ap_agora` (
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `wallpaper` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `logoUrl` varchar(255) DEFAULT NULL,
  `logoConnect` varchar(255) DEFAULT NULL,
  `dateUpdateDb` datetime NOT NULL,
  `version_agora` varchar(255) DEFAULT NULL,
  `skin` varchar(255) DEFAULT 'white',
  `footerHtml` text DEFAULT NULL,
  `usersLike` TINYINT DEFAULT NULL,
  `usersComment` tinyint DEFAULT '1',
  `mapTool` varchar(255) DEFAULT 'gmap',
  `gApiKey` varchar(255) DEFAULT NULL,
  `gIdentity` tinyint DEFAULT '1',
  `gIdentityClientId` varchar(255) DEFAULT NULL,
  `messengerDisplay` tinyint DEFAULT '1',
  `moduleLabelDisplay` tinyint DEFAULT '1',
  `folderDisplayMode` varchar(255) DEFAULT 'block',
  `personsSort` varchar(255) DEFAULT 'firstName',
  `userMailDisplay` tinyint DEFAULT NULL,
  `logsTimeOut` smallint DEFAULT '120',
  `visioHost` varchar(255) DEFAULT NULL,
  `visioHostAlt` varchar(255) DEFAULT NULL,
  `sendmailFrom` varchar(255) DEFAULT NULL,
  `smtpHost` varchar(255) DEFAULT NULL,
  `smtpPort` smallint DEFAULT NULL,
  `smtpSecure` varchar(255) DEFAULT NULL,
  `smtpUsername` varchar(255) DEFAULT NULL,
  `smtpPass` varchar(255) DEFAULT NULL,
  `ldap_server` varchar(255) DEFAULT NULL,
  `ldap_server_port` varchar(255) DEFAULT NULL,
  `ldap_admin_login` varchar(255) DEFAULT NULL,
  `ldap_admin_pass` varchar(255) DEFAULT NULL,
  `ldap_base_dn` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendar` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `timeSlot` varchar(255) DEFAULT NULL,
  `propositionNotify` varchar(1) DEFAULT NULL,
  `propositionGuest` varchar(1) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarEvent` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `dateBegin` datetime DEFAULT NULL,
  `dateEnd` datetime DEFAULT NULL,
  `_idCat` int DEFAULT NULL,
  `important` tinyint DEFAULT NULL,
  `contentVisible` varchar(255) DEFAULT NULL,
  `visioUrl` varchar(255) DEFAULT NULL,
  `periodType` varchar(255) DEFAULT NULL,
  `periodValues` varchar(1000) DEFAULT NULL,
  `periodDateEnd` date DEFAULT NULL,
  `periodDateExceptions` text DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `guest` varchar(255) DEFAULT NULL,
  `guestMail` varchar(255) DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarEventAffectation` (
  `_idEvt` int NOT NULL,
  `_idCal` int NOT NULL,
  `confirmed` tinyint DEFAULT NULL,
  KEY `indexes` (`_idCal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarCategory` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idSpaces` text DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `rank` smallint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_contact` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `civility` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `companyOrganization` text DEFAULT NULL,
  `function` text DEFAULT NULL,
  `adress` text DEFAULT NULL,
  `postalCode` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `telmobile` varchar(255) DEFAULT NULL,
  `mail` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_contactFolder` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardNews` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `description` text CHARACTER SET utf8mb4,
  `une` tinyint DEFAULT NULL,
  `offline` tinyint DEFAULT NULL,
  `dateOnline` datetime DEFAULT NULL,
  `dateOffline` datetime DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_file` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `octetSize` int DEFAULT NULL,
  `downloadsNb` int NOT NULL DEFAULT '0',
  `downloadedBy` varchar(10000) DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_fileFolder` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_fileVersion` (
  `_idFile` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `realName` text NOT NULL,
  `octetSize` int NOT NULL,
  `description` text DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
   KEY `indexes` (`_idFile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumMessage` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idMessageParent` int DEFAULT NULL,
  `_idContainer` int DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idMessageParent`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumSubject` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4,
  `_idTheme` int DEFAULT NULL,
  `dateLastMessage` datetime DEFAULT NULL,
  `usersConsultLastMessage` varchar(10000) DEFAULT NULL,
  `usersNotifyLastMessage` varchar(10000) DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idTheme`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumTheme` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idSpaces` text DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `rank` smallint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_invitation` (
  `_idInvitation` varchar(255) DEFAULT NULL,
  `_idSpace` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  KEY `indexes` (`_idInvitation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_joinSpaceModule` (
  `_idSpace` int DEFAULT NULL,
  `moduleName` varchar(255) DEFAULT NULL,
  `rank` tinyint DEFAULT NULL,
  `options` text DEFAULT NULL,
  KEY `indexes` (`_idSpace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_joinSpaceUser` (
  `_idSpace` int DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `allUsers` tinyint DEFAULT NULL,
  `accessRight` varchar(255) DEFAULT NULL,
  KEY `indexes` (`_idSpace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_link` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `adress` text NOT NULL,
  `description` text DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_linkFolder` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_log` (
  `action` varchar(50) DEFAULT NULL,
  `moduleName` varchar(50) DEFAULT NULL,
  `objectType` varchar(50) DEFAULT NULL,
  `_idObject` int DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `_idSpace` int DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 DEFAULT NULL,
  KEY `indexes` (`action`,`_idObject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_mail` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `recipients` text NOT NULL,
  `title` text DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 NOT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_objectAttachedFile` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `objectType` varchar(255) NOT NULL,
  `_idObject` int NOT NULL,
  `downloadsNb` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ap_objectComment` (
  `_id` INT NOT NULL AUTO_INCREMENT,
  `objectType` varchar(255) NOT NULL,
  `_idObject` int NOT NULL,
  `_idUser` int NOT NULL,
  `dateCrea` datetime NOT NULL,
  `comment` varchar(1000) NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ap_objectLike` (
  `objectType` varchar(255) NOT NULL,
  `_idObject` int NOT NULL,
  `_idUser` int NOT NULL,
  KEY `indexes` (`objectType`,`_idObject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_objectTarget` (
  `objectType` varchar(255) NOT NULL,
  `_idObject` int NOT NULL,
  `_idSpace` int NOT NULL,
  `target` varchar(255) NOT NULL,
  `accessRight` float NOT NULL,
  KEY `indexes` (`objectType`,`_idObject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_space` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `public` tinyint DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `userInscription` tinyint DEFAULT NULL,
  `userInscriptionNotify` tinyint DEFAULT NULL,
  `usersInvitation` tinyint DEFAULT NULL,
  `wallpaper` varchar(255) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_task` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `title` text NOT NULL,
  `description` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `_idStatus` int DEFAULT NULL,
  `priority` varchar(255) DEFAULT NULL,
  `advancement` tinyint DEFAULT NULL,
  `responsiblePersons` text DEFAULT NULL,
  `dateBegin` date DEFAULT NULL,
  `dateEnd` date DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`), 
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_taskStatus` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idSpaces` text DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `rank` smallint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_taskFolder` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idContainer` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `shortcut` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `indexes` (`_id`,`_idContainer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_user` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `civility` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `adress` text DEFAULT NULL,
  `postalCode` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `telmobile` varchar(255) DEFAULT NULL,
  `mail` text DEFAULT NULL,
  `function` text DEFAULT NULL,
  `companyOrganization` text DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `lastConnection` int DEFAULT NULL,
  `previousConnection` int DEFAULT NULL,
  `generalAdmin` tinyint DEFAULT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `connectionSpace` varchar(255) DEFAULT NULL,
  `calendarDisabled` tinyint DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userAuthToken` (
  `_idUser` int NOT NULL,
  `userAuthToken` varchar(255) NOT NULL,
  `browserId` varchar(255),
  `dateCrea` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userGroup` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `_idSpace` int NOT NULL,
  `_idUsers` text NOT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userInscription` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `_idSpace` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userLivecouter` (
  `_idUser` int NOT NULL DEFAULT '0',
  `ipAdress` varchar(255) NOT NULL,
  `editTypeId` varchar(255) DEFAULT NULL,
  `editorDraft` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `draftTypeId` varchar(255) DEFAULT NULL,
  `date` int DEFAULT NULL,
  PRIMARY KEY (`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userMessenger` (
  `_idUserMessenger` int NOT NULL,
  `allUsers` tinyint DEFAULT NULL,
  `_idUser` int DEFAULT NULL,
  KEY `indexes` (`_idUserMessenger`,`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userMessengerMessage` (
  `_idUser` int NOT NULL,
  `_idUsers` text NOT NULL,
  `message` text CHARACTER SET utf8mb4 NOT NULL,
  `date` int DEFAULT NULL,
  KEY `indexes` (`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userPreference` (
  `_idUser` int DEFAULT NULL,
  `keyVal` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  KEY `indexes` (`_idUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPoll` (
  `_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `dateEnd` date DEFAULT NULL,
  `multipleResponses` tinyint DEFAULT NULL,
  `newsDisplay` tinyint DEFAULT NULL,
  `publicVote` tinyint DEFAULT NULL,
  `dateCrea` datetime NOT NULL,
  `_idUser` int NOT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` int DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPollResponse` (
  `_id` varchar(255) NOT NULL,
  `_idPoll` int NOT NULL,
  `label` varchar(500) NOT NULL,
  `rank` smallint NOT NULL,
  `fileName` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPollResponseVote` (
  `_idUser` int NOT NULL,
  `_idResponse` varchar(255) NOT NULL,
  `_idPoll` int NOT NULL,
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
(5,'#bf0073','Evenement r&eacute;p&eacute;t&eacute;');

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