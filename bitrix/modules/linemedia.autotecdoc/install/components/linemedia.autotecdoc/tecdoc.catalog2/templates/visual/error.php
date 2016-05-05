<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);?>

<div class="tecdoc error">
	<?= ShowError(ShowError(GetMessage('ERROR') . ': '.$arResult['ERROR'])) ?>
</div>

<? include(dirname(__FILE__) . '/footer.php'); ?>