<style>
.vAdminLabel		{text-align:center;}
.vAdminLabel hr		{margin-block:20px;}
.vAdminLabel img	{margin-right:10px;}
</style>

<div>
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo "<div class='lightboxTitle'>".$curObj->lightboxMenu().$curObj->getLabel("full")."</div>";

	////	IMAGE & DETAILS DE l'USER
	echo "<div class='personProfileImg'>".$curObj->profileImg()."</div>";
	echo "<div class='personVueFields'>".$curObj->getFields("profile")."</div>";

	////	GROUPES D'UTILISATEURS
	$groupsLabel=null;
	foreach(MdlUserGroup::getGroups(null,$curObj) as $tmpGroup)  {$groupsLabel.=ucfirst($tmpGroup->title)."<br>";}
	if(!empty($groupsLabel))  {echo "<div class='objField'><div><img src='app/img/user/userGroup.png'> ".Txt::trad("USER_userGroups")."</div><div>".$groupsLabel."</div></div>";}

	////	ADMIN GENERAL/D'ESPACE
	if($curObj->isGeneralAdmin())	{echo "<div class='vAdminLabel'><hr><img src='app/img/user/userAdminGeneral.png'> ".Txt::trad("USER_adminGeneral")."</div>";}
	elseif($curObj->isSpaceAdmin())	{echo "<div class='vAdminLabel'><hr><img src='app/img/user/userAdminSpace.png'> ".Txt::trad("USER_adminSpace")." &nbsp;<i>".Ctrl::$curSpace->name."</i></div>";}
	?>
</div>