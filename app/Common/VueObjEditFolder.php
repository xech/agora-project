<script>
////	Resize
lightboxSetWidth(600);

$(function(){
	////	CHANGE L'ICONE DU DOSSIER
	$("select[name='icon']").on("change",function(){
		var iconPath=$("option[value='"+this.value+"']").attr("data-filePath");
		$("#folderIconImg").attr("src",iconPath);
	});

	////	CLICK LA CHECKBOX D'UNE AFFECTATION : CONTROL AJAX DU DROIT D'ACCÈS AU DOSSIER PARENT
	<?php if($curObj->containerObj()->isRootFolder()==false){ ?>
	$("[id^=objectRightBox]").on("change",function(){
		if($(this).prop("checked")){
			var ajaxUrl="?ctrl=object&action=AccessRightParentFolder&typeId=<?= $curObj->containerObj()->_typeId ?>&objectRight="+$(this).val();
			$.ajax({url:ajaxUrl,dataType:"json"}).done(function(result){
				if(result.errorMessage)  {notify(result.errorMessage);}
			});
		}
	});
	<?php } ?>
});

////	Controle spécifique à l'objet (cf. "VueObjEditMenuSubmit.php")
function objectFormControl(){
	return new Promise((resolve)=>{
		// vérif si un autre element porte le même nom
		var ajaxUrl="?ctrl=object&action=ControlDuplicateName&typeId=<?= $curObj->_typeId ?>&controledName="+encodeURIComponent($("[name='name']").val())+"&typeIdContainer=<?= $curObj->containerObj()->_typeId ?>";
		$.ajax(ajaxUrl).done(function(result){
			if(/duplicate/i.test(result))	{notify("<?= Txt::trad("NOTIF_duplicateName") ?>");  resolve(false);}	//"Un autre element porte le même nom"
			else							{resolve(true);}														//Sinon renvoie le résultat du controle principal
		});
	});
}
</script>


<style>
.inputTitleName			{width:75%}/*surcharge*/
#folderIcon				{display:table; margin-top:30px;}
#folderIcon>div			{display:table-cell;}
#folderIcon select		{height:80px; margin-left:20px; padding:5px;}
#folderIcon option		{padding:2px;}
</style>


<form action="index.php" method="post" id="mainForm" enctype="multipart/form-data">
	<!--TITRE MOBILE-->
	<?= $curObj->titleMobile("addFolder") ?>

	<!--NOM & DESCRIPTION-->
	<input type="text" name="name" value="<?= $curObj->name ?>" class="inputTitleName" placeholder="<?= Txt::trad("name") ?>">
	<?= $curObj->editDescription() ?>

	<!--ICONE DU DOSSIER-->
	<div id="folderIcon">
		<div>
			<img src="<?= $curObj->iconPath() ?>" id="folderIconImg">
		</div>
		<div>
			<select name="icon" size="5">
				<?php
				for($cpt=0; $cpt<=18; $cpt++){
					if($cpt>0)	{$iconValue="folder".$cpt.".png";	$iconImg=$iconValue;	$iconLabel="folder ".$cpt;}
					else		{$iconValue=null;					$iconImg="folder.png";	$iconLabel=Txt::trad("byDefault");}
					$iconSelect=($iconValue==$curObj->icon || ($cpt==0 && empty($curObj->icon)))  ?  "selected"  :  null;
					echo "<option value=\"".$iconValue."\" data-filePath=\"".PATH_ICON_FOLDER.$iconImg."\" ".$iconSelect.">".$iconLabel."</option>";
				}
				?>
			</select>
		</div>
	</div>

	<!--MENU COMMUN & SUBMIT & CONTROLE DU FORM-->
	<?= $curObj->editMenuSubmit() ?>
</form>