<script>
////	Init l'affichage
ready(function(){
	////	Contrôle l'ajout/modif d'un commentaire
	$("form").on("submit",function(){
		if($(this).find("textarea").isEmpty())  {notify("<?= Txt::trad("emptyFields") ?>");  return false;}
	});

	////	Edition/suppression d'un commentaire : update le "circleNb"  (idem "usersLikeUpdate()")
	<?php if(Req::isParam("actionComment")){ ?>
		var menuId="#usersComment_<?= $curObj->typeId ?>";																						//Id du menu
		if(<?= count($commentList) ?>==0)	{window.top.$(menuId).addClass("hide").find(".circleNb").html("");}									//Masque l'icone et le nb de commentaires
		else								{window.top.$(menuId).removeClass("hide").find(".circleNb").html("<?= count($commentList) ?>");}	//Affiche l'icone
		window.top.$(menuId).tooltipUpdate("<?= $commentsTitle ?>");																			//Update le Tooltip
	<?php } ?>
});
</script>


<style>
fieldset						{margin-bottom:30px;}/*surcharge*/
.vCommentsTable					{display:table; width:100%;}
.vCommentsTable>div				{display:table-cell;}
.vCommentDateUser				{width:180px;}
.vCommentDateUser>div			{margin-top:5px;}
.vCommentOptions				{width:25px;}
.vCommentOptions img:last-child	{margin-top:5px;}
.vCommentEditForm				{display:none; margin-block:15px;}
.vCommentAddForm				{margin-top:40px;}
.submitButton					{margin-top:15px;}/*surcharge*/			

/*** RESPONSIVE SMARTPHONE*/
@media screen and (max-width:499px){
	.vCommentsTable		{font-size:0.9rem;}
	.vCommentDateUser	{font-size:0.8rem; width:130px;}
}
</style>


<div>
	<div class="lightboxTitle"><?= $commentsTitle ?></div>

	<?php
	////	AFFICHE CHAQUE COMMENTAIRE
	foreach($commentList as $tmpComment){
		$tmpId=$tmpComment['_id'];
	?>
	<fieldset>
		<div class="vCommentsTable">
			<div id="commentValue<?= $tmpId ?>">
				<div><?= $tmpComment['comment'] ?></div>
			</div>
			<div class="vCommentDateUser">
				<?= Ctrl::getObj("user",$tmpComment['_idUser'])->getLabel() ?>
				<div><?= Txt::dateLabel("default",$tmpComment['dateCrea']) ?></div>
			</div>
			<?php if(MdlObject::userCommentEditRight($tmpId)){ ?>
				<div class="vCommentOptions">
					<img src="app/img/edit.png" <?= Txt::tooltip("modify") ?> onclick="$('#commentValue<?= $tmpId ?>,#commentForm<?= $tmpId ?>').toggle()">
					<img src="app/img/delete.png" <?= Txt::tooltip("delete") ?> onclick="confirmDelete('?ctrl=object&action=UsersComment&typeId=<?= $curObj->typeId ?>&idComment=<?= $tmpId ?>&actionComment=delete')">
				</div>
			<?php } ?>
		</div>
		<form action="index.php" method="post" class="vCommentEditForm" id="commentForm<?= $tmpId ?>">
			<textarea name="comment" maxlength="200"><?= $tmpComment['comment'] ?></textarea>
			<input type="hidden" name="idComment" value="<?= $tmpId ?>">
			<input type="hidden" name="actionComment" value="modif">
			<?= Txt::submitButton("modify") ?>
		</form>
	</fieldset>
	<?php } ?>

	<!--AJOUT D'UN COMMENTAIRE-->
	<form action="index.php" method="post" class="vCommentAddForm">
		<textarea name="comment" maxlength="200" placeholder="<?= Txt::trad("commentAdd") ?>" class="vCommentAddTextarea"></textarea>
		<input type="hidden" name="actionComment" value="add">
		<?= Txt::submitButton("add"); ?>
	</form>
</div>