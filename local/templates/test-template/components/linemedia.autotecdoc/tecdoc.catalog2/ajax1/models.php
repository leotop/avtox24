<? // include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);

global $APPLICATION;

$APPLICATION->RestartBuffer();?>

<? print_r($arResult); define('FIRST_CAR_YEAR', '1986') ?>

<?/*<select id="model-select-id">*/?>
	<option value=""><?=GetMessage('HEAD_MODEL')?></option>
	<? foreach ($arResult['MODELS'] as $model) { ?>
		<option value="<?= $model['modelId'] ?>"><?= htmlspecialcharsEx($model['modelname']) ?></option>
	<? } ?>
<?/*</select>*/?>

<?

die();