<script>
////	Init l'affichage
ready(function(){
	////	Contrôle l'ajout/modif d'un commentaire
	$("form").on("submit",function(){
		if($(this).find("textarea").isEmpty())  {notify("<?= Txt::trad("emptyFields") ?>");  return false;}
	});

	////	Edition/suppression d'un commentaire : update le "circleNb"  (idem "usersLikeUpdate()")
	<?php if(Req::isParam("actionComment")){ ?>
		var menuId="#usersComment_<?= $curObj->_typeId ?>";																								//Id du menu
		if(<?= count($commentList) ?>==0)	{window.top.$(menuId).addClass("hide").find(".circleNb").html("");}											//Masque l'icone et le nb de commentaires
		else								{window.top.$(menuId).removeClass("hide").find(".circleNb").html("<?= count($commentList) ?>").pulsate(1);}	//Affiche l'icone
		window.top.$(menuId).tooltipUpdate("<?= $commentsTitle ?>");																					//Update le Tooltip
	<?php } ?>

	////	Focus du champ (pas sur mobile pour ne pas afficher le clavier virtuel)
	$(".vCommentAddTextarea").focusAlt();
});
</script>


<style>
form					{text-align:right;}
form button				{width:120px;}
.vCommentsTable			{display:table; width:100%; margin-bottom:20px;}
.vCommentsRow			{display:table-row;}
.vCommentsRow>div		{display:table-cell; padding:5px;}
.vCommentDateUser		{width:200px;}
.vCommentDateUser>div	{font-weight:normal}
.vCommentText form		{display:none;}
.vCommentOptions		{width:100px; text-align:right;}
.vCommentOptions img	{margin-left:10px;}
.submitButtonInline		{padding-top:10px;}

/*RESPONSIVE SMARTPHONE*/
@media screen and (max-width:490px){
	.vCommentsTable, .vCommentsRow, .vCommentsRow>div	{display:block; width:100%;}
	.vCommentsRow			{margin-bottom:15px!important;}
	.vCommentText			{border:dotted 1px #ddd; padding:10px!important;}
	.vCommentOptions img	{max-height:18px; margin-right:5px;}
}
</style>


<div>
	<div class="lightboxTitle"><?= $commentsTitle ?></div>

	<!--AFFICHE CHAQUE COMMENTAIRE-->
	<?php foreach($commentList as $tmpComment){ ?>
		<div class="vCommentsTable">
			<div class="vCommentsRow lineHover">
				<div class="vCommentDateUser"><?= Ctrl::getObj("user",$tmpComment['_idUser'])->getLabel() ?><div><?= Txt::dateLabel($tmpComment['dateCrea'],"labelFull") ?></div></div>
				<div class="vCommentText" id="commentText<?= $tmpComment['_id'] ?>">
					<div><?= $tmpComment['comment'] ?></div>
					<form action="index.php" method="post">
						<textarea name="comment" maxlength="200"><?= $tmpComment['comment'] ?></textarea>
						<input type="hidden" name="idComment" value="<?= $tmpComment['_id'] ?>">
						<input type="hidden" name="actionComment" value="modif">
						<?= Txt::submitButton("modify",false) ?>
					</form>
				</div>
				<?php if(MdlObject::userCommentEditRight($tmpComment['_id'])){ ?>
					<div class="vCommentOptions">
						<img src="app/img/edit.png" <?= Txt::tooltip("modify") ?> onclick="$('#commentText<?= $tmpComment['_id'] ?> >*').toggle()">
						<img src="app/img/delete.png" <?= Txt::tooltip("delete") ?> onclick="confirmDelete('?ctrl=object&action=UsersComment&typeId=<?= $curObj->_typeId ?>&idComment=<?= $tmpComment['_id'] ?>&actionComment=delete')">
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<!--AJOUT D'UN COMMENTAIRE-->
	<form action="index.php" method="post">
		<textarea name="comment" maxlength="200" placeholder="<?= Txt::trad("commentAdd") ?>" class="vCommentAddTextarea"></textarea>
		<input type='hidden' name='actionComment' value='add'>
		<?= Txt::submitButton("add",false); ?>
	</form>
</div>