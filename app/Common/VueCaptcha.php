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
#captchaText	{width:170px!important; font-size:0.95em;}
#captchaReload	{cursor:pointer; width:16px;}
</style>

<div id="captchaDiv" title="<?= Txt::trad("captchaInfo") ?>">
	<img src="?ctrl=misc&action=CaptchaImg" id="captchaImg">
	<img src="app/img/arrowRight.png" id="captchaArrow">
	<input type="text" name="captcha" id="captchaText" placeholder="<?= Txt::trad("captcha") ?>">
	<img src="app/img/reload.png" id="captchaReload" title="reload !" onclick="$('#captchaImg').attr('src','?ctrl=misc&action=CaptchaImg&rand='+Math.random())">
</div>