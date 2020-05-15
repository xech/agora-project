<script>
////	Resize
lightboxSetWidth(550);
</script>

<style>
.vAdminLabel		{text-align:center;}
.vAdminLabel hr		{margin-top:30px; margin-bottom:10px;}
.vAdminLabel span	{font-style:italic; margin-left:5px;}
</style>

<div class="lightboxContent objVueBg">
	<?php
	////	MENU CONTEXTUEL/D'EDITION  &&  TITRE
	echo $curObj->menuContextEdit()."<div class='lightboxTitle'>".$curObj->getLabel("all")."</div>";

	////	IMAGE & DETAILS DE l'USER
	echo "<div class='personLabelImg'>".$curObj->getImg()."</div>";
	echo "<div class='personVueFields'>".$curObj->getFieldsValues("profile")."</div>";

	////	GROUPES D'UTILISATEURS
	$groupsLabel=null;
	foreach(MdlUserGroup::getGroups(null,$curObj) as $tmpGroup)  {$groupsLabel.="<img src='app/img/arrowRight.png'> ".$tmpGroup->title."<br>";}
	if(!empty($groupsLabel))  {echo "<div class='objField'><div class='fieldLabel'><img src='app/img/user/userGroup.png'> ".Txt::trad("USER_userGroups")."</div><div>".$groupsLabel."</div></div>";}
	
	////	ADMIN GENERAL/D'ESPACE
	if($curObj->isAdminGeneral())	{echo "<div class='vAdminLabel'><hr><img src='app/img/user/adminGeneral.png'> ".Txt::trad("USER_adminGeneral")."</div>";}
	elseif($curObj->isAdminSpace())	{echo "<div class='vAdminLabel'><hr><img src='app/img/user/adminSpace.png'> ".Txt::trad("USER_adminSpace")." <span>".Ctrl::$curSpace->name."</span></div>";}
	?>
</div>