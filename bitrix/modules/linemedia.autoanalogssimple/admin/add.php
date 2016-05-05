<?php
/**
 * Административный файл для добавления кроссов
*/

/**
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("linemedia.autoanalogssimple")) {
    ShowError('MODULE NOT INSTALLED');
    return;
}


if (!CModule::IncludeModule("sale")) {
    ShowError('SALE MODULE NOT INSTALLED');
    return;
}

$linemedia_autodownloaderModulePermissions = $APPLICATION->GetGroupRight("linemedia.autoanalogssimple");
if ($linemedia_autodownloaderModulePermissions < "W") {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
IncludeModuleLangFile(__FILE__);

ClearVars();


/*
 * Варианты колонок
 */
$columns = array(
	'article_original',
    'article_analog',
    'brand_title_original',
    'brand_title_analog'
);


$arAnalogGroups = LinemediaAutoPart::getAnalogGroups();

// Уберем группу "Искомые аналоги".
unset($arAnalogGroups[LinemediaAutoPart::ANALOG_GROUP_ORIGINAL]);



$ID = IntVal($ID);
if ($ID > 0) {
	$obj = new LinemediaAutoAnalogsSimpleAnalog();
	$analog = $obj->GetByID($ID);
	$analog = $analog->Fetch();
}


if ($_GET['action'] == 'delete' && $linemedia_autodownloaderModulePermissions=="W" && check_bitrix_sessid()) {
	$analog_obj = new LinemediaAutoAnalogsSimpleAnalog();
    $analog_obj->clear(array('id' => $ID));
	LocalRedirect("linemedia.autoanalogssimple_list.php?lang=".LANG.GetFilterParams("filter_", false));
}


$strError = "";
$bInitVars = false;
if ((strlen($save)>0 || strlen($apply)>0) && $REQUEST_METHOD=="POST" && $linemedia_autodownloaderModulePermissions=="W" && check_bitrix_sessid()) {

	/*
	 * Main
	 */
	$group_original         = trim(strval($_POST['group_original']));
    $group_analog           = trim(strval($_POST['group_analog']));
	$article_original 		= trim(strval($_POST['article_original']));
	$article_analog 		= trim(strval($_POST['article_analog']));
	$brand_title_original 	= trim(strval($_POST['brand_title_original']));
	$brand_title_analog 	= trim(strval($_POST['brand_title_analog']));
	
    
    /*
     * Проверка валидности данных.
     */
	if ($group_original == '') {
	    $strError .= GetMessage('ERROR_ANALOG_GROUP') .'<br>';
	}
    if ($group_analog == '') {
        $strError .= GetMessage('ERROR_ANALOG_GROUP_REVERSE') .'<br>';
    }
	if ($article_original == '') {
		$strError .= GetMessage('ERROR_ARTICLE_ORIGINAL') .'<br>';
    }
	if ($article_analog == '') {
		$strError .= GetMessage('ERROR_ARTICLE_ANALOG') .'<br>';
    }
	if ($brand_title_original == '') {
		$strError .= GetMessage('ERROR_BRAND_TITLE_ORIGINAL') .'<br>';
    }
	if ($brand_title_analog == '') {	
		$strError .= GetMessage('ERROR_BRAND_TITLE_ANALOG') .'<br>';
    }
	
    
	if (strlen($strError) <= 0) {
		unset($arFields);
		$arFields = array(
			"import_id"			     => 'manual',
			"group_original"         => $group_original,
            "group_analog"           => $group_analog,
			"article_original"       => $article_original,
			"article_analog"         => $article_analog,
			"brand_title_original"   => $brand_title_original,
			"brand_title_analog"     => $brand_title_analog,
		);
		
		if ($ID > 0) {
			$analog_obj = new LinemediaAutoAnalogsSimpleAnalog();
			if (!$analog_obj->Update($ID, $arFields)) {
				$strError .= GetMessage("ERROR_EDIT")."<br>";
            }
		} else {
			$analog_obj = new LinemediaAutoAnalogsSimpleAnalog();
			$ID = $analog_obj->add($arFields);
			if ($ID <= 0) {
				$strError .= GetMessage("ERROR_ADD")."<br>";
            }
		}
	}
	
	
	if (strlen($strError) > 0) {
	    $bInitVars = True;
    }
	if (strlen($save) > 0 && strlen($strError) <= 0) {
		LocalRedirect("linemedia.autoanalogssimple_list.php?lang=".LANG.GetFilterParams("filter_", false));
    }
	if (strlen($apply) > 0 && strlen($strError) <= 0) {
		LocalRedirect("linemedia.autoanalogssimple_list.php?ID=".$ID."&lang=".LANG.GetFilterParams("filter_", false));
    }
}

if ($bInitVars) {
	$analog = array_map('htmlspecialchars', $_POST);
}



$sDocTitle = ($ID > 0) ? str_replace("#ID#", $ID, GetMessage("LM_AUTO_AS_EDIT")) : GetMessage("LM_AUTO_AS_NEW");
$APPLICATION->SetTitle($sDocTitle);

$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.7.1/jquery.min.js');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/*********************************************************************/
/********************  BODY  *****************************************/
/*********************************************************************/
?>
<?
$aMenu = array(
		array(
				"TEXT" => GetMessage("LM_AUTO_ASS_LIST"),
				"LINK" => "/bitrix/admin/linemedia.autoanalogssimple_list.php?lang=".LANG,
				"ICON" => "btn_list"
			)
	);

if ($ID > 0 && $linemedia_autodownloaderModulePermissions >= "W") {
	$aMenu[] = array("SEPARATOR" => "Y");

	$aMenu[] = array(
			"TEXT" => GetMessage("LM_AUTO_AS_NEW"),
			"LINK" => "/bitrix/admin/linemedia.autoanalogssimple_add.php?lang=".LANG.GetFilterParams("filter_"),
			"ICON" => "btn_new"
		);

	$aMenu[] = array(
			"TEXT" => GetMessage("LM_AUTO_AS_DELETE"),
			"LINK" => "javascript:if(confirm('".GetMessage("LM_AUTO_AS_DELETE_CONFIRM")."')) window.location='/bitrix/admin/linemedia.autoanalogssimple_add.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."#tb';",
			"ICON" => "btn_delete"
		);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?php
if (strlen($strError)>0) {
	echo CAdminMessage::ShowMessage(Array("DETAILS"=>$strError, "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SDEN_ERROR"), "HTML"=>true));
}	
?>

<form method="POST" action="/bitrix/admin/linemedia.autoanalogssimple_add.php?lang=<?= LANG ?>&ID=<?= $ID ?>" name="form1" id="lm-auto-analogssimple-add-task-frm">
    <?= GetFilterHiddens("filter_") ?>
    <input type="hidden" name="Update" value="Y">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="ID" value="<?= $ID ?>">
    <?= bitrix_sessid_post() ?>
    
    <?
    $aTabs = array(
    	array("DIV" => "edit1", "TAB" => GetMessage("LM_AUTO_AS_TAB_MAIN"), "ICON" => "linemedia.autoanalogssimple.main", "TITLE" => GetMessage("LM_AUTO_AS_TAB_MAIN")),
    );
    
    $tabControl = new CAdminTabControl("tabControl", $aTabs);
    $tabControl->Begin();
    ?>
    
    <? $tabControl->BeginNextTab(); ?>
    
    	<? if ($ID > 0) { ?>
    		<tr>
    			<td width="40%"><?= GetMessage('LM_AUTO_AS_ID') ?>:</td>
    			<td width="60%"><?= $ID ?></td>
    		</tr>
    	<? } ?>
        
        <tr class="adm-detail-required-field">
            <td><?= GetMessage("LM_AUTO_AS_ANALOG_GROUP") ?>:</td>
            <td>
                <select name="group_original">
                    <? foreach ($arAnalogGroups as $id => $title) { ?>
                        <option value="<?= $id ?>" <?= ($analog['group_original'] == $id) ? ('selected') : ('') ?>>
                            <?= $title ?>
                        </option>
                    <? } ?>
                </select>
            </td>
        </tr>
        <tr class="adm-detail-required-field">
            <td><?= GetMessage("LM_AUTO_AS_ANALOG_GROUP_REVERSE") ?>:</td>
            <td>
                <select name="group_analog">
                    <? foreach ($arAnalogGroups as $id => $title) { ?>
                        <option value="<?= $id ?>" <?= ($analog['group_analog'] == $id) ? ('selected') : ('') ?>>
                            <?= $title ?>
                        </option>
                    <? } ?>
                </select>
            </td>
        </tr>
    	<tr class="adm-detail-required-field">
    		<td width="40%"><?= GetMessage("LM_AUTO_AS_PART_ORIGINAL") ?>:</td>
    		<td width="60%">
    			<input type="text" name="article_original" value="<?= $analog['article_original'] ?>" placeholder="<?= GetMessage("LM_AUTO_AS_ARTICLE_ORIGINAL") ?>" size="20" />
    			<input type="text" name="brand_title_original" value="<?= $analog['brand_title_original'] ?>" placeholder="<?= GetMessage("LM_AUTO_AS_BRAND_TITLE_ORIGINAL") ?>" size="20" />
    		</td>
    	</tr>
    	<tr class="adm-detail-required-field">
    		<td width="40%"><?= GetMessage("LM_AUTO_AS_PART_ANALOG") ?>:</td>
    		<td width="60%">
    			<input type="text" name="article_analog" value="<?= $analog['article_analog'] ?>" placeholder="<?= GetMessage("LM_AUTO_AS_ARTICLE_ANALOG") ?>" size="20" />
    			<input type="text" name="brand_title_analog" value="<?= $analog['brand_title_analog'] ?>" placeholder="<?= GetMessage("LM_AUTO_AS_BRAND_TITLE_ANALOG") ?>" size="20" />
    		</td>
    	</tr>
    
    <? $tabControl->EndTab(); ?>
    
    <?
    $tabControl->Buttons(
    	array(
    			"disabled" => ($linemedia_autodownloaderModulePermissions < "W"),
    			"back_url" => "/bitrix/admin/linemedia.autoanalogssimple_list.php?lang=".LANG.GetFilterParams("filter_")
    		)
    );
    ?>
    
    <? $tabControl->End(); ?>
</form>

<? require($DOCUMENT_ROOT."/bitrix/modules/main/include/epilog_admin.php"); ?>