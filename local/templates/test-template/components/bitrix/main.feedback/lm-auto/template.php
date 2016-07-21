<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();?>

<?if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $v)
		ShowError($v);
}
if(strlen($arResult["OK_MESSAGE"]) > 0)
{
	ShowNote($arResult["OK_MESSAGE"]);
}
?>
<br />
<hr />

<div class="feedback">
<h1>Задайте вопрос</h1>
	<form class="form-horizontal" action="" method="POST">
	<?=bitrix_sessid_post()?>
	
			<div class="control-group">
				<label class="control-label field-title"><?=GetMessage("MFT_NAME")?></label>
				<div class="controls">
					<input type="text" name="user_name" class="input_text_style" value="<?=$arResult["AUTHOR_NAME"]?>"><br/>
				</div>
			</div>
	
			<div class="control-group">
				<label class="control-label field-title"><?=GetMessage("MFT_EMAIL")?></label>
				<div class="controls">
					<input type="text" name="user_email" class="input_text_style" value="<?=$arResult["AUTHOR_EMAIL"]?>"><br/>
				</div>
			</div>
	
			<div class="control-group">
				<label class="control-label field-title"><?=GetMessage("MFT_MESSAGE")?></label>
				<div class="controls">
					<textarea name="MESSAGE" rows="5" cols="40" style="width:500px; height:200px"><?=$arResult["MESSAGE"]?></textarea><br/>
				</div>
			</div>
		
			<?if($arParams["USE_CAPTCHA"] == "Y"):?>
			<div class="control-group">
				<label class="control-label field-title"><?=GetMessage("MFT_CAPTCHA_CODE")?></label>
				<div class="controls">
					<input type="text" name="captcha_word" size="30" maxlength="50" value="">
					<input type="hidden" name="captcha_sid" value="<?=$arResult["capCode"]?>"><br />
					<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["capCode"]?>" width="180" height="40" alt="CAPTCHA">
				</div>	
			</div>
			<?endif;?>
		
			<div class="control-group">
				<div class="controls">
					<input class="btn btn-warning" type="submit" class="bt3" name="submit" value="<?=GetMessage("MFT_SUBMIT")?>">
				</div>
			</div>
	
	</form>
</div>