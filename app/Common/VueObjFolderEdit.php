<script>
////	Resize
lightboxSetWidth(520);

$(function(){
	////	Change l'icone du dossier
	$("select[name='icon']").change(function(){
		var iconPath=$("option[value='"+this.value+"']").attr("data-filePath");
		$("#folderIconImg").prop("src",iconPath);
	});
	
	////	Validation du formulaire
	$("#mainForm").submit(function(event){
		//Le formulaire doit d'abord être controlé
		if(typeof mainFormControled=="undefined"){
			//Pas de validation par défaut du formulaire
			event.preventDefault();
			//Vérifie si un autre dossier porte le même nom
			$.ajax("?ctrl=object&action=ControlDuplicateName&targetObjId=<?= $curObj->_targetObjId ?>&targetObjIdContainer=<?= $curObj->containerObj()->_targetObjId ?>&controledName="+encodeURIComponent($("[name='name']").val())).done(function(result){
				if(find("duplicate",result))	{notify("<?= Txt::trad("NOTIF_duplicateName"); ?>","warning");  return false;}	//Un autre fichier porte le même nom...
				else if(mainFormControl())		{mainFormControled=true;  $("#mainForm").submit();}								//Sinon : image "Loading" & Sinon on confirme le formulaire !
			});
		}
	});
});
</script>

<style>
textarea[name='description']	{<?= empty($curObj->description)?"display:none;":null ?>}
#folderIcon			{display:table; margin-top:15px;}
#folderIcon>div		{display:table-cell;}
#folderIcon select	{height:80px; margin-left:20px; padding:5px;}
#folderIcon option	{padding:2px;}
</style>


<form action="index.php" method="post" id="mainForm" class="lightboxContent" enctype="multipart/form-data">

	<!--NOM & DESCRIPTION-->
	<input type="text" name="name" value="<?= $curObj->name ?>" class="textBig" placeholder="<?= Txt::trad("name") ?>">
	<img src="app/img/description.png" class="sLink" title="<?= Txt::trad("description") ?>" onclick="$('textarea[name=description]').slideToggle();">
	<br><br>
	<textarea name="description" placeholder="<?= Txt::trad("description") ?>"><?= $curObj->description ?></textarea>
	
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

	<!--MENU COMMUN-->
	<?= $curObj->menuEdit() ?>
</form>