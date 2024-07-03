<script>
////	Captcha toujours en majuscule
$(function(){
	$("#captchaText").on("change keyup",function(){
		$(this).val(this.value.toUpperCase());
	});
});
</script>

<style>
#captchaDiv		{margin-top:20px;}
#captchaImg		{vertical-align:middle;}
#captchaArrow	{margin:0px 5px 0px 5px;}
#captchaText	{width:190px!important;}
</style>

<div id="captchaDiv" title="<?= Txt::trad("captchaTooltip") ?>">
	<img src="?ctrl=misc&action=CaptchaImg" id="captchaImg">
	<img src="app/img/arrowRightBig.png" id="captchaArrow">
	<input type="text" name="captcha" id="captchaText" placeholder="<?= Txt::trad("captcha") ?>">
	<img src="app/img/reload.png" id="captchaReload" title="reload !" onclick="$('#captchaImg').attr('src','?ctrl=misc&action=CaptchaImg&rand='+Math.random())">
</div>