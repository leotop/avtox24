<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.auto");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


IncludeModuleLangFile(__FILE__);

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}







if($_POST['save'] == 'Y' && check_bitrix_sessid()) {
	foreach((array) $_POST['statuses'] AS $status_id => $status) {
		LinemediaAutoStatus::Update($status_id, $status);
	}
}



$statuses = LinemediaAutoStatus::getList(array());



CJSCore::Init(array('jquery'));
$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/interface/colorpicker/css/colorpicker.css');
$APPLICATION->AddHeadScript('/bitrix/themes/.default/interface/colorpicker/colorpicker.js');

$APPLICATION->SetTitle(GetMessage("LM_AUTO_SETTING_STATUSES_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");



?>
<form action="" method="post">
	<input type="hidden" name="save" value="Y" />
	<?=bitrix_sessid_post();?>
<?



/*
 * Описываем табы административной панели битрикса.
 */
$aTabs = array(
    array(
        'DIV'   => 'titles',
        'TAB'   => GetMessage('LM_AUTO_STATUSES_TAB_TITLES'),
        'ICON'  => 'titles_settings',
        'TITLE' => GetMessage('LM_AUTO_STATUSES_TAB_TITLES')
    ),
    array(
        'DIV'   => 'relationships',
        'TAB'   => GetMessage('LM_AUTO_STATUSES_TAB_RELATIONS'),
        'ICON'  => 'relationships_settings',
        'TITLE' => GetMessage('LM_AUTO_STATUSES_TAB_RELATIONS')
    ),
);

/**
 * Инициализируем табы
 */
$oTabControl = new CAdmintabControl('tabControl', $aTabs);
$oTabControl->Begin();
$oTabControl->BeginNextTab();
?>

<?
/*
 * Описываем внутренние табы административной панели битрикса.
 */
$innerTabs = array(
    array(
        'DIV'   => 'wholesale_buyer',
        'TAB'   => GetMessage('LM_AUTO_STATUS_FOR_WHOLESALE_BUYER'),
        'ICON'  => 'titles_settings',
        'TITLE' => GetMessage('LM_AUTO_STATUS_FOR_WHOLESALE_BUYER')
    ),
    array(
        'DIV'   => 'retail_buyer',
        'TAB'   => GetMessage('LM_AUTO_STATUS_FOR_RETAIL_BUYER'),
        'ICON'  => 'relationships_settings',
        'TITLE' => GetMessage('LM_AUTO_STATUS_FOR_RETAIL_BUYER')
    ),
);

?>
<tr>
	<td>

		<?=BeginNote()?>
		<?=GetMessage('LM_AUTO_SETTING_STATUSES_MESSAGE')?>
		<?=EndNote()?>
		
		
		<table class="lm-settings-statuses adm-list-table">
			<thead>
				<tr class="adm-list-table-header">
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_ID')?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_NAME')?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_DESCRIPTION')?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_LID')?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_PUBLIC_TITLE')?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_COLOR_ADMIN')?></div></td>
					<td class="adm-list-table-cell adm-list-table-cell-last"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_COLOR_PUBLIC')?></div></td>
				</tr>
			</thead>
			<tbody>
				<?foreach($statuses AS $status_id => $status){?>
				<tr class="adm-list-table-row">
					<td class="adm-list-table-cell"><?=$status_id?></td>
					<td class="adm-list-table-cell">
						<input type="text" name="statuses[<?=$status_id?>][NAME]" size="40" value="<?=htmlspecialchars($status['NAME'])?>"/>
					</td>
					<td class="adm-list-table-cell">
						<textarea name="statuses[<?=$status_id?>][DESCRIPTION]" cols="20" rows="3"><?=htmlspecialchars($status['DESCRIPTION'])?></textarea>
					</td>
					<td class="adm-list-table-cell"><?=$status['LID']?></td>
					<td class="adm-list-table-cell">
						<input type="text" name="statuses[<?=$status_id?>][PUBLIC_TITLE]" size="40" value="<?=htmlspecialchars($status['PUBLIC_TITLE'])?>"/>
					</td>
					<td class="adm-list-table-cell">
						<input type="text" class="color-picker" name="statuses[<?=$status_id?>][COLOR_ADMIN]" size="12" value="<?=htmlspecialchars($status['COLOR_ADMIN'])?>"/>
						<div class="color" style="background-color: <?=htmlspecialchars($status['COLOR_ADMIN'])?>;">&nbsp;</div>
					</td>
					<td class="adm-list-table-cell adm-list-table-cell-last">
						<input type="text" class="color-picker" name="statuses[<?=$status_id?>][COLOR_PUBLIC]" size="12" value="<?=htmlspecialchars($status['COLOR_PUBLIC'])?>"/>
						<div class="color" style="background-color: <?=htmlspecialchars($status['COLOR_ADMIN'])?>;">&nbsp;</div>
					</td>
				</tr>
				<?}?>
			</tbody>
		</table>
	</td>
</tr>
<? $oTabControl->EndTab();?>


<? $oTabControl->BeginNextTab();?>
<tr>
	<td>
		<?=BeginNote()?>
		<?=GetMessage('LM_AUTO_SETTING_STATUSES_RELATIONS_MESSAGE')?>
		<?=EndNote()?>
		
		
		<a href="#" id="reset-statuses" class="adm-btn" style="margin-bottom:20px;"><?=GetMessage('LM_AUTO_STATUS_RELATIONS_RESET')?></a>
        
    	<?  
		/**
		 * Инициализируем внутренние табы
		 */
		$innerTabControl = new CAdmintabControl('tabControlInner', $innerTabs);
		$innerTabControl->Begin();
		$innerTabControl->BeginNextTab();
		?>
		<tr>
			<td>
				<table class="lm-settings-statuses-relations wholesale lm-settings-statuses adm-list-table" style="margin-bottom:40px; width:60%;">
					<thead>
						<tr class="adm-list-table-header">
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_NAME')?></div></td>
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_RELATION_CHANGES_TO')?></div></td>
						</tr>
					</thead>
					<tbody>
						<?foreach($statuses AS $status_id => $status){?>
						<tr class="adm-list-table-row status-row" data-status-id="<?=$status_id?>">
							<td class="adm-list-table-cell">[<?=$status_id?>] <?=$status['NAME']?></td>
							<td class="adm-list-table-cell adm-list-table-cell-last">
								<select name="statuses[<?=$status_id?>][RELATIONS][wholesale]">
								<option value="">-</option>
								<?foreach($statuses AS $rel_status_id => $rel_status){?>
									<option <?=($rel_status['ID'] == $status['RELATIONS']['wholesale']) ? 'selected':''?> value="<?=$rel_status_id?>">[<?=$rel_status['ID']?>] <?=$rel_status['PUBLIC_TITLE']?></option>
								<?}?>
								</select>
							</td>
						</tr>
						<?}?>
					</tbody>
				</table>
			</td>
		</tr>
		<? $innerTabControl->EndTab();?>
		<? $innerTabControl->BeginNextTab();?>
		<tr>
			<td>
				<table class="lm-settings-statuses-relations retail lm-settings-statuses adm-list-table" style="width:60%;">
					<thead>
						<tr class="adm-list-table-header">
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_NAME')?></div></td>
							<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage('LM_AUTO_STATUS_RELATION_CHANGES_TO')?></div></td>
						</tr>
					</thead>
					<tbody>
						<?foreach($statuses AS $status_id => $status){?>
						<tr class="adm-list-table-row status-row" data-status-id="<?=$status_id?>">
							<td class="adm-list-table-cell">[<?=$status_id?>] <?=$status['NAME']?></td>
							<td class="adm-list-table-cell adm-list-table-cell-last">
								<select name="statuses[<?=$status_id?>][RELATIONS][retail]">
								<option value="">-</option>
								<?foreach($statuses AS $rel_status_id => $rel_status){?>
									<option <?=($rel_status['ID'] == $status['RELATIONS']['retail']) ? 'selected':''?> value="<?=$rel_status_id?>">[<?=$rel_status['ID']?>] <?=$rel_status['PUBLIC_TITLE']?></option>
								<?}?>
								</select>
							</td>
						</tr>
						<?}?>
					</tbody>
				</table>
			</td>
		</tr>
		<? $innerTabControl->EndTab();?>
		<? $innerTabControl->End();?>
        
		
	</td>
</tr>
<? $oTabControl->EndTab();?>


<? $oTabControl->Buttons();?>
<input type="submit" name="Update" value="<?=GetMessage('LM_AUTO_STATUSES_SUBMIT')?>" />
<? $oTabControl->End(); ?>

	
</form>


<style>
div.color {margin-top: 3px;width: 100%; height: 5px}
.adm-list-table-row > td{border-bottom:1px solid #BEC7C8;}
</style>

<script type="text/javascript">
    $(document).ready(function() {
        $('.color-picker').ColorPicker({
            onSubmit: function(hsb, hex, rgb, el) {
                $(el).val('#' + hex.toUpperCase());
                $(el).ColorPickerHide();
                $(el).siblings('div.color').css('backgroundColor', '#' + hex);
            },
            onBeforeShow: function () {
                $(this).ColorPickerSetColor(this.value);
            },
            onShow: function (colpkr) {
                $(colpkr).fadeIn(300);
                return false;
            },
            onHide: function (colpkr) {
                $(colpkr).fadeOut(300);
                return false;
            },
        })
        .bind('keyup', function() {
            $(this).ColorPickerSetColor(this.value);
            $(this).siblings('div.color').css('backgroundColor', this.value);
        });
        
        
        
        $('#reset-statuses').click(function(e){
	        e.preventDefault();
	        $('.status-row').each(function(){
		        var status_id = $(this).data('status-id');
		        $(this).find('select option[value='+status_id+']').attr('selected', 'selected');
	        })
	        
	        
        })
        
    });
</script>

<?
require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');