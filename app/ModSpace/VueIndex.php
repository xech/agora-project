<style>
/* BLOCKS DE CONTENU */
#moduleMenu .infos			{text-align:left;}
.objBlocks .objContainer	{height:220px; min-width:400px; max-width:700px;}/*surcharge*/
.objContainerScroll			{padding:10px;}
.vSpaceName					{font-size:1.1rem;}
.vSpaceDescription			{margin-top:10px; font-weight:normal;}
.vModules					{margin:15px 0px;}
.vModules img				{max-height:25px; margin-inline:5px;}
.vSpaceAffectation			{display:inline-block; width:32%; padding:5px; font-size:0.85rem;}
.vSpaceAffectation img		{max-height:17px;}

/*AFFICHAGE SMARTPHONE*/
@media screen and (max-width:490px){
	.vSpaceAffectation		{width:48%;}
	.vSpaceAffectation img	{max-height:15px;}
}
</style>

<div id="pageFull">
	<div id="moduleMenu">
		<div class="miscContainer">
			<div class="menuLine forMobileAddElem" onclick="lightboxOpen('<?= MdlSpace::getUrlNew() ?>')" <?= Txt::tooltip("SPACE_moduleTooltip") ?>><div class="menuIcon"><img src="app/img/plus.png"></div><div><?= Txt::trad("SPACE_addSpace") ?></div></div>
			<?= MdlSpace::menuSort() ?>
			<div class="menuLine"><div class="menuIcon"><img src="app/img/info.png"></div><div><?= count($spaceList)." ".Txt::trad(count($spaceList)>1?"SPACE_spaces":"SPACE_space") ?></div></div>
			<div class="infos"><?= Txt::trad("SPACE_moduleTooltip") ?></div>
		</div>
	</div>

	<div id="pageContent" class="objBlocks">
		<!--LISTE DES ESPACES-->
		<?php foreach($spaceList as $tmpSpace){ ?>
			<?= $tmpSpace->objContainerMenu() ?>
				<div class="objContainerScroll">
					<div class="vSpaceName"><?= $tmpSpace->name ?></div>
					<div class="vSpaceDescription" <?= Txt::tooltip($tmpSpace->description) ?> ><?= Txt::reduce($tmpSpace->description,80) ?></div>
					<div class="vModules"><?php foreach($tmpSpace->moduleList(true) as $tmpModule)  {echo '<img src="app/img/'.$tmpModule["moduleName"].'/iconSmall.png" '.Txt::tooltip($tmpModule["description"]).'>';} ?></div>
					<hr>
					<!--"DROIT D'ACCÈS À DEFINIR"-->
					<?php if(count($tmpSpace->getUsers())==0 && empty($tmpSpace->public) && $tmpSpace->allUsersAffected()==false){ ?>
						<div class="infos"><?= Txt::trad("SPACE_accessRightUndefined") ?></div>
					<?php } ?>

					<!--"ESPACE PUBLIC"-->
					<?php if(!empty($tmpSpace->public)){ ?>
						<div class="vSpaceAffectation"><img src="app/img/user/accessGuest.png"> <?= Txt::trad("SPACE_publicSpace") ?></div>
					<?php } ?>

					<!--"TOUS LES UTILISATEURS"-->
					<?php if($tmpSpace->allUsersAffected()){ ?>
						<div class="vSpaceAffectation"><img src="app/img/user/accessAllUsers.png"> <?= Txt::trad("SPACE_allUsers") ?></div>
					<?php } ?>

					<!--LISTE DES USERS AFFECTES-->
					<?php foreach($tmpSpace->getUsers() as $tmpUser){
						$accessRight=$tmpSpace->accessRightUser($tmpUser);
						if($tmpSpace->allUsersAffected() && $accessRight==1)  {continue;}//Simple user et tous les users affectés à l'espace
					?>
						<div class="vSpaceAffectation" onclick="<?= $tmpUser->openVue() ?>"><img src="app/img/user/<?= $accessRight==2?"userAdminSpace.png":"accessUser.png" ?>"> <?= $tmpUser->getLabel() ?></div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>