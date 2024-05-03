<script>
////	Resize
lightboxSetWidth(500);
</script>

<style>
.vAdminLabel		{text-align:center;}
.vAdminLabel hr		{margin-top:30px; margin-bottom:10px;}
.vAdminLabel span	{font-style:italic; margin-left:5px;}
</style>

<div>
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo "<div class='lightboxTitle'>".$curObj->inlineContextMenu().$curObj->getLabel("full")."</div>";

	////	IMAGE & DETAILS DE l'USER
	echo "<div class='personLabelImg'>".$curObj->personImg()."</div>";
	echo "<div class='personVueFields'>".$curObj->getFieldsValues("profile")."</div>";

	////	GROUPES D'UTILISATEURS
	$groupsLabel=null;
	foreach(MdlUserGroup::getGroups(null,$curObj) as $tmpGroup)  {$groupsLabel.="<img src='app/img/arrowRight.png'> ".$tmpGroup->title."<br>";}
	if(!empty($groupsLabel))  {echo "<div class='objField'><div><img src='app/img/user/userGroup.png'> ".Txt::trad("USER_userGroups")."</div><div>".$groupsLabel."</div></div>";}
	
	////	ADMIN GENERAL/D'ESPACE
	if($curObj->isGeneralAdmin())	{echo "<div class='vAdminLabel'><hr><img src='app/img/user/userAdminGeneral.png'> ".Txt::trad("USER_adminGeneral")."</div>";}
	elseif($curObj->isSpaceAdmin())	{echo "<div class='vAdminLabel'><hr><img src='app/img/user/userAdminSpace.png'> ".Txt::trad("USER_adminSpace")." <span>".Ctrl::$curSpace->name."</span></div>";}
	?>
</div>