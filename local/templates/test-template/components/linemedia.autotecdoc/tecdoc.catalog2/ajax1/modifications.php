<? include(dirname(__FILE__) . '/header.php'); IncludeTemplateLangFile(__FILE__);

global $APPLICATION;

$APPLICATION->RestartBuffer();?>

<?/*<select id="modification-select-id">*/?>
	<option value=""><?=GetMessage('HEAD_MODIFICATION')?></option>
	<? foreach ($arResult['MODIFICATIONS'] as $modification) { ?>
		<option value="<?= $modification['carId'] ?>">
			<?= htmlspecialcharsEx($modification['carName']) ?>
			
			<?= $modification['begin'] ?> &mdash; <?= $modification['end'] ?>
		</option>
	<? } ?>
<?/*</select>*/

die();
?>