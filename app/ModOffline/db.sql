CREATE TABLE `ap_agora` (
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `lang` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `wallpaper` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `logoUrl` varchar(255) DEFAULT NULL,
  `logoConnect` varchar(255) DEFAULT NULL,
  `dateUpdateDb` datetime NOT NULL,
  `version_agora` varchar(255) DEFAULT NULL,
  `skin` varchar(255) DEFAULT NULL,
  `footerHtml` text,
  `usersLike` varchar(255) DEFAULT NULL,
  `usersComment` tinyint(1) unsigned DEFAULT NULL,
  `mapTool` varchar(255) DEFAULT 'gmap',
  `mapApiKey` varchar(255) DEFAULT NULL,
  `gSignin` tinyint(1) DEFAULT NULL,
  `gSigninClientId` varchar(255) DEFAULT NULL,
  `gPeopleApiKey` varchar(255) DEFAULT NULL,
  `messengerDisabled` tinyint(1) unsigned DEFAULT NULL,
  `moduleLabelDisplay` varchar(255) DEFAULT NULL,
  `personsSort` varchar(255) DEFAULT NULL,
  `logsTimeOut` smallint(6) DEFAULT NULL,
  `visioHost` varchar(255) DEFAULT NULL,
  `sendmailFrom` varchar(255) DEFAULT NULL,
  `smtpHost` varchar(255) DEFAULT NULL,
  `smtpPort` smallint(6) DEFAULT NULL,
  `smtpSecure` varchar(255) DEFAULT NULL,
  `smtpUsername` varchar(255) DEFAULT NULL,
  `smtpPass` varchar(255) DEFAULT NULL,
  `ldap_server` varchar(255) DEFAULT NULL,
  `ldap_server_port` varchar(255) DEFAULT NULL,
  `ldap_admin_login` varchar(255) DEFAULT NULL,
  `ldap_admin_pass` varchar(255) DEFAULT NULL,
  `ldap_base_dn` varchar(255) DEFAULT NULL,
  `ldap_crea_auto_users` tinyint(1) unsigned DEFAULT NULL,
  `ldap_pass_cryptage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendar` (
  `_id` mediumint(8) unsigned NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `timeSlot` varchar(255) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarEvent` (
  `_id` mediumint(8) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `dateBegin` datetime DEFAULT NULL,
  `dateEnd` datetime DEFAULT NULL,
  `_idCat` smallint(5) unsigned DEFAULT NULL,
  `important` tinyint(1) unsigned DEFAULT NULL,
  `contentVisible` varchar(255) DEFAULT NULL,
  `periodType` varchar(255) DEFAULT NULL,
  `periodValues` varchar(1000) DEFAULT NULL,
  `periodDateEnd` date DEFAULT NULL,
  `periodDateExceptions` text,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `guest` varchar(255) DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarEventAffectation` (
  `_idEvt` mediumint(8) unsigned NOT NULL,
  `_idCal` mediumint(8) unsigned NOT NULL,
  `confirmed` tinyint(1) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_calendarEventCategory` (
  `_id` smallint(5) unsigned NOT NULL,
  `_idSpaces` text,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `color` varchar(255) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_contact` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `civility` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `companyOrganization` text,
  `function` text,
  `adress` text,
  `postalCode` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `telmobile` varchar(255) DEFAULT NULL,
  `mail` text,
  `comment` text,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_contactFolder` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardNews` (
  `_id` mediumint(8) unsigned NOT NULL,
  `description` text,
  `une` tinyint(1) unsigned DEFAULT NULL,
  `offline` tinyint(1) unsigned DEFAULT NULL,
  `dateOnline` datetime DEFAULT NULL,
  `dateOffline` datetime DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_file` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `octetSize` int(11) DEFAULT NULL,
  `downloadsNb` int(10) unsigned NOT NULL DEFAULT '0',
  `downloadedBy` varchar(10000) DEFAULT NULL,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_fileFolder` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_fileVersion` (
  `_idFile` mediumint(8) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `realName` text,
  `octetSize` int(10) unsigned DEFAULT NULL,
  `description` text,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumMessage` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idMessageParent` int(10) unsigned DEFAULT NULL,
  `_idContainer` mediumint(8) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumSubject` (
  `_id` mediumint(8) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `_idTheme` smallint(6) DEFAULT NULL,
  `dateLastMessage` datetime DEFAULT NULL,
  `usersConsultLastMessage` varchar(10000) DEFAULT NULL,
  `usersNotifyLastMessage` varchar(10000) DEFAULT NULL,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_forumTheme` (
  `_id` smallint(5) unsigned NOT NULL,
  `_idSpaces` text,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `color` varchar(255) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_invitation` (
  `_idInvitation` varchar(255) DEFAULT NULL,
  `_idSpace` smallint(6) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_joinSpaceModule` (
  `_idSpace` smallint(5) unsigned DEFAULT NULL,
  `moduleName` varchar(255) DEFAULT NULL,
  `rank` tinyint(1) unsigned DEFAULT NULL,
  `options` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_joinSpaceUser` (
  `_idSpace` smallint(5) unsigned DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `allUsers` tinyint(1) unsigned DEFAULT NULL,
  `accessRight` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_link` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `adress` text,
  `description` text,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_linkFolder` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_log` (
  `action` varchar(50) DEFAULT NULL,
  `moduleName` varchar(50) DEFAULT NULL,
  `objectType` varchar(50) DEFAULT NULL,
  `_idObject` mediumint(8) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `_idSpace` smallint(5) unsigned DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `comment` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_mailHistory` (
  `_id` mediumint(8) unsigned NOT NULL,
  `recipients` text,
  `title` text,
  `description` text,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_objectAttachedFile` (
  `_id` mediumint(8) unsigned NOT NULL,
  `name` text,
  `objectType` varchar(255) DEFAULT NULL,
  `_idObject` mediumint(8) unsigned DEFAULT NULL,
  `downloadsNb` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ap_objectComment` (
  `_id` mediumint(8) unsigned not null,
  `objectType` varchar(255) NOT NULL,
  `_idObject` mediumint(8) NOT NULL,
  `_idUser` mediumint(8) NOT NULL,
  `dateCrea` datetime NOT NULL,
  `comment` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ap_objectLike` (
  `objectType` varchar(255) NOT NULL,
  `_idObject` mediumint(8) NOT NULL,
  `_idUser` mediumint(8) NOT NULL,
  `value` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_objectTarget` (
  `objectType` varchar(255) DEFAULT NULL,
  `_idObject` mediumint(8) unsigned DEFAULT NULL,
  `_idSpace` smallint(5) unsigned DEFAULT NULL,
  `target` varchar(255) DEFAULT NULL,
  `accessRight` float unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_space` (
  `_id` smallint(5) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `public` tinyint(1) unsigned DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `usersInscription` tinyint(1) unsigned DEFAULT NULL,
  `usersInvitation` tinyint(1) unsigned DEFAULT NULL,
  `wallpaper` varchar(255) DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_task` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `title` text,
  `description` text,
  `priority` varchar(255) DEFAULT NULL,
  `advancement` tinyint(1) unsigned DEFAULT NULL,
  `responsiblePersons` text,
  `dateBegin` datetime DEFAULT NULL,
  `dateEnd` datetime DEFAULT NULL,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_taskFolder` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idContainer` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `icon` VARCHAR(255) DEFAULT NULL,
  `shortcut` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_user` (
  `_id` mediumint(8) unsigned NOT NULL,
  `civility` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `adress` text,
  `postalCode` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `telmobile` varchar(255) DEFAULT NULL,
  `mail` text,
  `function` text,
  `companyOrganization` text,
  `comment` text,
  `lastConnection` int(10) unsigned DEFAULT NULL,
  `previousConnection` int(10) unsigned DEFAULT NULL,
  `generalAdmin` tinyint(1) unsigned DEFAULT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `connectionSpace` varchar(255) DEFAULT NULL,
  `calendarDisabled` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userGroup` (
  `_id` smallint(5) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `_idSpace` mediumint(8) unsigned DEFAULT NULL,
  `_idUsers` text,
  `dateCrea` datetime DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userInscription` (
  `_id` mediumint(8) unsigned NOT NULL,
  `_idSpace` smallint(5) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `message` text,
  `date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userLivecouter` (
  `_idUser` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ipAdress` varchar(255) DEFAULT NULL,
  `editObjId` varchar(255) DEFAULT NULL,
  `editorDraft` TEXT DEFAULT NULL,
  `draftTargetObjId` varchar(255) DEFAULT NULL,
  `date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userMessenger` (
  `_idUserMessenger` mediumint(8) unsigned DEFAULT NULL,
  `allUsers` tinyint(1) unsigned DEFAULT NULL,
  `_idUser` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userMessengerMessage` (
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `_idUsers` text,
  `message` text CHARACTER SET utf8mb4,
  `color` varchar(255) DEFAULT NULL,
  `date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_userPreference` (
  `_idUser` mediumint(8) unsigned DEFAULT NULL,
  `keyVal` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPoll` (
  `_id` mediumint(8) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `dateEnd` date DEFAULT NULL,
  `multipleResponses` tinyint(1) unsigned DEFAULT NULL,
  `newsDisplay` tinyint(1) unsigned DEFAULT NULL,
  `publicVote` tinyint(1) unsigned DEFAULT NULL,
  `dateCrea` datetime NOT NULL,
  `_idUser` mediumint(8) unsigned NOT NULL,
  `dateModif` datetime DEFAULT NULL,
  `_idUserModif` mediumint(8) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPollResponse` (
  `_id` varchar(255) NOT NULL,
  `_idPoll` mediumint(8) unsigned NOT NULL,
  `label` varchar(500) NOT NULL,
  `rank` tinyint(2) unsigned NOT NULL,
  `fileName` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ap_dashboardPollResponseVote` (
  `_idUser` mediumint(8) unsigned NOT NULL,
  `_idResponse` varchar(255) NOT NULL,
  `_idPoll` mediumint(8) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `ap_calendar`					ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_calendarEvent`				ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_calendarEventAffectation`	ADD KEY `indexes` (`_idCal`);
ALTER TABLE `ap_calendarEventCategory`		ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_contact`					ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_contactFolder`				ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_dashboardNews`				ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_file`						ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_fileFolder`					ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_fileVersion`				ADD KEY `indexes` (`_idFile`);
ALTER TABLE `ap_forumMessage`				ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idMessageParent`,`_idContainer`);
ALTER TABLE `ap_forumSubject`				ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idTheme`);
ALTER TABLE `ap_forumTheme`					ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_invitation`					ADD KEY `indexes` (`_idInvitation`);
ALTER TABLE `ap_joinSpaceModule`			ADD KEY `indexes` (`_idSpace`);
ALTER TABLE `ap_joinSpaceUser`				ADD KEY `indexes` (`_idSpace`);
ALTER TABLE `ap_link`						ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_linkFolder`					ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_log`						ADD KEY `indexes` (`action`,`_idObject`);
ALTER TABLE `ap_mailHistory`				ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_objectAttachedFile`			ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_objectComment`				ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_objectLike`					ADD KEY `indexes` (`objectType`(100),`_idObject`);
ALTER TABLE `ap_objectTarget`				ADD KEY `indexes` (`objectType`(100),`_idObject`);
ALTER TABLE `ap_space`						ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_task`						ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_taskFolder`					ADD PRIMARY KEY (`_id`), ADD KEY `indexes` (`_id`,`_idContainer`);
ALTER TABLE `ap_user`						ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_userGroup`					ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_userInscription`			ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_userLivecouter`				ADD PRIMARY KEY (`_idUser`);
ALTER TABLE `ap_userMessenger`				ADD KEY `indexes` (`_idUserMessenger`,`_idUser`);
ALTER TABLE `ap_userMessengerMessage`		ADD KEY `indexes` (`_idUser`);
ALTER TABLE `ap_userPreference`				ADD KEY `indexes` (`_idUser`);
ALTER TABLE `ap_dashboardPoll`				ADD PRIMARY KEY (`_id`);
ALTER TABLE `ap_dashboardPollResponse`		ADD PRIMARY KEY (`_id`(20));
ALTER TABLE `ap_dashboardPollResponseVote`	ADD PRIMARY KEY (`_idUser`,`_idResponse`(20));


ALTER TABLE `ap_calendar`				MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_calendarEvent`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_calendarEventCategory`	MODIFY `_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_contact`				MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_contactFolder`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_dashboardNews`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_file`					MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_fileFolder`				MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_forumMessage`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_forumSubject`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_forumTheme`				MODIFY `_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_link`					MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_linkFolder`				MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_mailHistory`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_objectAttachedFile`		MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_objectComment`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_space`					MODIFY `_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_task`					MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_taskFolder`				MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_user`					MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_userGroup`				MODIFY `_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_userInscription`		MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `ap_dashboardPoll`			MODIFY `_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;




INSERT INTO ap_agora SET name='Omnispace / Agora-Project', personsSort='firstName', logsTimeOut='120', version_agora='3.0.0', dateUpdateDb=NOW(), usersLike='likeSimple', usersComment=1, mapTool='gmap', gSignin='1';

INSERT INTO ap_space SET _id=1, usersInvitation=1;

INSERT INTO ap_user SET _id=1, generalAdmin=1;

INSERT INTO ap_userMessenger SET _idUserMessenger=1, allUsers=1;

INSERT INTO ap_calendar (_id,type,_idUser,title) VALUES (1,'ressource',1,NULL), (2,'user',1,NULL);

INSERT INTO ap_calendarEventCategory (_id,color,title) VALUES (1,'#770000','rendez-vous'), (2,'#000077','reunion'), (3,'#dd7700','vacances'), (4,'#007700','personnel');

INSERT INTO ap_contactFolder SET _id=1, _idContainer=0;

INSERT INTO ap_linkFolder SET _id=1, _idContainer=0;

INSERT INTO ap_taskFolder SET _id=1, _idContainer=0;

INSERT INTO ap_fileFolder SET _id=1, _idContainer=0;

INSERT INTO ap_link (_id, _idContainer, adress, dateCrea, _idUser) VALUES
(1, 1, 'https://www.omnispace.fr', NOW(), 1),
(2, 1, 'https://fr.wikipedia.org', NOW(), 1);

INSERT INTO ap_file (_id, _idContainer, name, description, octetSize, downloadsNb, dateCrea, _idUser) VALUES
(1, 1, 'Documentation.pdf', 'Documentation', 228075, 1, NOW(), 1),
(2, 1, 'Photo 1.jpg', NULL, 172057, 1, NOW(), 1),
(3, 1, 'Photo 2.jpg', NULL, 214053, 1, NOW(), 1),
(4, 1, 'Photo 3.jpg', NULL, 280614, 1, NOW(), 1);

INSERT INTO ap_fileVersion (_idFile, name, realName, octetSize, description, dateCrea, _idUser) VALUES
(1, 'Documentation.pdf', '1_1514764800.pdf', 228075, 'Documentation', NOW(), 1),
(2, 'Photo 1.jpg', '2_1514764800.jpg', 172057, NULL, NOW(), 1),
(3, 'Photo 2.jpg', '3_1514764800.jpg', 214053, NULL, NOW(), 1),
(4, 'Photo 3.jpg', '4_1514764800.jpg', 280614, NULL, NOW(), 1);

INSERT INTO ap_joinSpaceModule (_idSpace,moduleName,rank) VALUES (1,'dashboard',1), (1,'file',2), (1,'calendar',3), (1,'forum',4), (1,'contact',5), (1,'link',6), (1,'task',7), (1,'user',8), (1,'mail',9);

INSERT INTO ap_joinSpaceUser SET _idSpace=1, allUsers=1, accessRight=1;

INSERT INTO ap_objectTarget (objectType, _idObject, _idSpace, target, accessRight) VALUES 
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