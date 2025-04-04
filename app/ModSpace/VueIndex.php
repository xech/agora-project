<style>
/* BLOCKS DE CONTENU */
#pageMenu .infos			{text-align:left;}
.objBlocks .objContainer	{height:200px; width:300px; min-width:300px; max-width:700px; padding:10px;}/*surcharge*/
.vSpaceName					{font-size:1.1em;}
.vSpaceDescription			{margin-top:10px; font-weight:normal;}
.vModules					{margin:15px 0px;}
.vModules img				{max-height:30px; margin-right:10px;}
.vSpaceAffectationLabel		{margin:12px 0px 8px 3px;}
.vSpaceAffectations			{overflow-y:auto; height:70px;}
.vSpaceAffectation			{display:inline-block; min-width:150px; width:32%; padding:0px 5px 5px 0px; font-size:0.95em;}
.vSpaceAffectation img		{max-height:20px;}

/*MOBILE FANCYBOX (440px)*/
@media screen and (max-width:440px){
	.vSpaceAffectation	{width:48%;}
}
</style>

<div id="pageFull">
	<div id="pageMenu">
		<div class="miscContainer">
			<div class="menuLine" onclick="lightboxOpen('<?= MdlSpace::getUrlNew() ?>')" <?= Txt::tooltip("SPACE_moduleTooltip") ?>><div class="menuIcon"><img src="app/img/plus.png"></div><div><?= Txt::trad("SPACE_addSpace") ?></div></div>
			<?= MdlSpace::menuSort() ?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= count($spaceList)." ".Txt::trad(count($spaceList)>1?"SPACE_spaces":"SPACE_space") ?></div></div>
			<div class="infos"><?= Txt::trad("SPACE_moduleTooltip") ?></div>
		</div>
	</div>

	<div id="pageContent" class="objBlocks">
		<?php
		////	LISTE DES ESPACES
		foreach($spaceList as $tmpSpace)
		{
			////	CONTENEUR
			echo $tmpSpace->objContainerMenu();
				////	NOM & DESCRIPTION & MODULES AFFECTES
				$moduleList=null;
				foreach($tmpSpace->moduleList(true) as $tmpModule)  {$moduleList.='<img src="app/img/'.$tmpModule["moduleName"].'/icon.png" '.Txt::tooltip($tmpModule["description"]).'>';}
				echo '<div class="vSpaceName">'.$tmpSpace->name.'</div>
					  <div class="vSpaceDescription" '.Txt::tooltip($tmpSpace->description).'>'.Txt::reduce($tmpSpace->description,80).'</div>
					  <div class="vModules">'.$moduleList.'</div><hr>';
				////	AFFECTATIONS
				echo '<div class="vSpaceAffectations">';
					//Droit d'accès à definir  /  Espace public  /  Tous les users affectes
					if(count($tmpSpace->getUsers())==0 && $tmpSpace->allUsersAffected()==false && empty($tmpSpace->public))  {echo '<div class="infos">'.Txt::trad("SPACE_accessRightUndefined").'</div>';}
					if($tmpSpace->allUsersAffected())	{echo '<div class="vSpaceAffectation"><img src="app/img/user/iconSmall.png"> '.Txt::trad("SPACE_allUsers").'</div>';}
					if(!empty($tmpSpace->public))		{echo '<div class="vSpaceAffectation"><img src="app/img/user/guest.png"> '.Txt::trad("SPACE_publicSpace").'</div>';}
					//Users affectes
					foreach($tmpSpace->getUsers() as $tmpUser){
						$accessRightUser=$tmpSpace->accessRightUser($tmpUser);
						if($tmpSpace->allUsersAffected() && $accessRightUser==1)  {continue;}//Pas d'affichage si simple user et tous les users sont affectés
						echo '<div class="vSpaceAffectation" onclick="'.$tmpUser->openVue().'"><img src="app/img/user/'.($accessRightUser==2?"userAdminSpace.png":"user.png").'"> '.$tmpUser->getLabel().'</div>';
					}
				echo '</div>
			</div>';
		}
		?>
	</div>
</div>