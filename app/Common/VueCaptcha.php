<script>
/**************************************************************************************************
 * ASYNC : CONTROLE LE CAPTCHA
 **************************************************************************************************/
function captchaControl(){
	return new Promise((resolve)=>{
		$.ajax("?ctrl=misc&action=CaptchaControl&captcha="+encodeURIComponent($("#captchaText").val())).done(function(result){
			if(/controlOK/i.test(result)==false)	{notify("<?=Txt::trad("captchaError") ?>");  resolve(false);}
			else									{resolve(true);}							
		});
	});
}

/**************************************************************************************************
 * CAPTCHA EN MAJUSCULE
**************************************************************************************************/
ready(function(){
	$("#captchaText").on("change keyup",function(){
		$(this).val(this.value.toUpperCase());
	});
});
</script>

<style>
#captchaDiv		{margin-top:20px;}
#captchaImg		{vertical-align:middle;}
#captchaArrow	{margin-inline:10px;}
#captchaText	{width:180px!important; font-size:14px; margin-right:5px;}
</style>

<div id="captchaDiv">
	<img src="?ctrl=misc&action=CaptchaImg" id="captchaImg">
	<img src="app/img/arrowRightSmall.png" id="captchaArrow">
	<input type="text" name="captcha" id="captchaText" placeholder="<?= Txt::trad("captcha") ?>" <?= Txt::tooltip("captchaTooltip") ?> required>
	<img src="app/img/reload.png" title="Change captcha" onclick="$('#captchaImg').attr('src','?ctrl=misc&action=CaptchaImg&rand='+Math.random())">
</div>