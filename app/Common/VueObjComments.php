<script>
////	Resize
lightboxSetWidth(500);

////	Init l'affichage
$(function(){
	//Focus du champ (pas en responsive pour ne pas afficher le clavier virtuel)
	if(!isMobile())  {$(".vCommentAdd textarea").focus();}

	////	Affiche le nombre de comment en page principale
	<?php if(!empty($updateCircleNb)){ ?>
	var commentMenuId="#commentMenu_<?= $curObj->_targetObjId ?>";
	parent.$(commentMenuId+" .objMiscMenuCircle").html("<?= count($commentList) ?>").removeClass("objMiscMenuCircleHide");//Affiche le nb de commentaires (circle)
	parent.$(commentMenuId).tooltipster("destroy").attr("title","<?= $commentsTitle ?>").tooltipster(tooltipsterOptions);//Maj le "title" du menu des commentaires
	parent.$(commentMenuId).parent().removeClass("hideMiscMenu");//Enlève l'opacité du menu (affichage Block, cf. css)
	<?php } ?>
});

////	Contrôle du formulaire
function formControl()
{
	if($("textarea[name='comment']").isEmpty())
		{notify("<?= Txt::trad("fillAllFields"); ?>");  return false;}
}
</script>


<style>
form					{text-align:right;}
form button				{width:120px;}
.vCommentsTable			{display:table; width:100%; margin-bottom:15px;}
.vCommentsRow			{display:table-row;}
.vCommentsRow>div		{display:table-cell; padding:5px;}
.vCommentDateUser		{width:130px;}
.vCommentDateUser>div	{font-weight:normal}
.vCommentText>form		{display:none;}
.vCommentOptions		{width:50px;}
.vCommentOptions img	{max-height:18px;}
.formMainButton			{margin-top:10px;}

/*RESPONSIVE*/
@media screen and (max-width:440px){
	.vCommentsTable, .vCommentsRow, .vCommentsRow>div	{display:block; width:100%;}
	.vCommentsRow			{margin-bottom:15px!important;}
	.vCommentText			{border:dotted 1px #ddd; padding:10px!important;}
	.vCommentOptions img	{max-height:18px; margin-right:5px;}
}
</style>


<div class="lightboxContent">
	<div class="lightboxTitle"><?= $commentsTitle ?></div>

	<?php
	////	Affiche chaque Commentaire
	foreach($commentList as $tmpComment)
	{
		echo "<div class='vCommentsTable'>
				<div class='vCommentsRow sTableRow'>
					<div class='vCommentDateUser'>".Ctrl::getObj("user",$tmpComment["_idUser"])->getLabel()." <div>".Txt::displayDate($tmpComment["dateCrea"],"full")."</div></div>
					<div class='vCommentText' id='commentText".$tmpComment["_id"]."'>
						<div>".$tmpComment["comment"]."</div>
						<form action='index.php' method='post'><textarea name='comment' maxlength='200'>".$tmpComment["comment"]."</textarea><input type='hidden' name='idComment' value='".$tmpComment["_id"]."'><input type='hidden' name='actionComment' value='modif'>".Txt::submit("modify",false)."</form>
					</div>
					<div class='vCommentOptions' ".(MdlObjectAttributes::userCommentEditRight($tmpComment["_id"])?null:"style='visibility:hidden'").">
						<img src='app/img/edit.png' class='sLink' onclick=\"$('#commentText".$tmpComment["_id"].">*').toggle()\">
						<img src='app/img/delete.png' class='sLink' onclick=\"confirmDelete('?ctrl=object&action=comments&targetObjId=".$curObj->_targetObjId."&idComment=".$tmpComment["_id"]."&actionComment=delete')\">
					</Div>
				</div>
			  </div>";
	}
	?>

	<!--AJOUT D'UN COMMENTAIRE-->
	<form class="vCommentAdd" action="index.php" method="post" onsubmit="return formControl()">
		<textarea name="comment" maxlength="200" placeholder="<?= Txt::trad("commentAdd") ?>"></textarea>
		<input type='hidden' name='actionComment' value='add'>
		<?= Txt::submit("add",false); ?>
	</form>
</div>