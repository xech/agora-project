<style>
/* BLOCKS DE CONTENU */
.objBlocks .objContainer{height:200px; width:300px; min-width:300px; max-width:600px; padding:10px;}/*surcharge*/
.vSpaceDescription		{font-weight:normal;}
.vModules				{margin-top:10px;}
.vModules img			{max-height:30px; margin-right:5px;}
.vSpaceAffectations		{overflow-y:auto; height:70px;}
.vSpaceAffectationLabel	{margin:12px 0px 8px 3px;}
.vSpaceAffectation		{display:inline-block; width:32%; padding:0px 10px 10px 0px;}
.vSpaceAffectation img	{max-height:18px;}

/*RESPONSIVE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vSpaceAffectation	{width:48%;}
}
</style>

<div id="pageFull">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<div class="menuLine sLink" onclick="lightboxOpen('<?= MdlSpace::getUrlNew() ?>');" title="<?= Txt::trad("SPACE_moduleInfo") ?>"><div class="menuIcon"><img src="app/img/plus.png"></div><div><?= Txt::trad("SPACE_addSpace") ?></div></div><hr>
			<?= MdlSpace::menuSort() ?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= count($spaceList)." ".Txt::trad(count($spaceList)>1?"SPACE_spaces":"SPACE_space") ?></div></div>
		</div>
	</div>
	<div id="pageFullContent" class="objBlocks">
		<?php
		////	LISTE DES ESPACES
		foreach($spaceList as $tmpSpace)
		{
			////	CONTENEUR
			echo $tmpSpace->divContainer().$tmpSpace->contextMenu();
				////	NOM & DESCRIPTION & MODULES AFFECTES
				echo $tmpSpace->name."<div class='vSpaceDescription' title=\"".$tmpSpace->description."\">".Txt::reduce($tmpSpace->description,(Req::isMobile()?50:80))."</div>";
				echo "<div class='vModules'>";
					foreach($tmpSpace->moduleList(false) as $tmpModule)  {echo "<img src='app/img/".$tmpModule["moduleName"]."/icon.png' title=\"".$tmpModule["description"]."\">";}
				echo "</div><hr>";
				////	AFFECTATIONS
				echo "<div class='vSpaceAffectationLabel'>".Txt::trad("EDIT_accessRight")." :</div>";
				echo "<div class='vSpaceAffectations'>";
					//A definir
					if(count($tmpSpace->getUsers())==0 && $tmpSpace->allUsersAffected()==false && empty($tmpSpace->public))  {echo "<div class='infos'>".Txt::trad("SPACE_accessRightUndefined")."</div>";}
					//Espace public / tous les users affectes
					if($tmpSpace->allUsersAffected())	{echo "<div class='vSpaceAffectation'><img src='app/img/user/icon.png'> ".Txt::trad("SPACE_allUsers")."</div>";}
					if(!empty($tmpSpace->public))		{echo "<div class='vSpaceAffectation'><img src='app/img/public.png'> ".Txt::trad("SPACE_publicSpace")."</div>";}
					//Users affectes
					foreach($tmpSpace->getUsers() as $tmpUser){
						$userRightAcces=$tmpSpace->userAccessRight($tmpUser);
						if($tmpSpace->allUsersAffected() && $userRightAcces==1)	{continue;}//Pas d'affichage si simple user et tous les users sont affectés
						echo "<div class='vSpaceAffectation sLink' onclick=\"lightboxOpen('".$tmpUser->getUrl("vue")."');\"><img src='app/img/user/".($userRightAcces==2?'adminSpace.png':'accesUser.png')."'> ".$tmpUser->getLabel()."</div>";
					}
				echo "</div>";
			echo "</div>";
		}
		?>
	</div>
</div>