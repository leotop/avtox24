<?php
/**
 * Административный скрипт информации о удаленном поставщике
 */
/**
 * @author  Linemedia
 * @since   01/08/2012
 *
 * @link    http://auto.linemedia.ru/
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");


if(!CModule::IncludeModule('linemedia.auto'))
{
    ShowError(GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_NO_MAIN_MODULE'));
    return;
}

if(!CModule::IncludeModule('linemedia.autoremotesuppliers'))
{
    ShowError(GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_NO_MODULE'));
    return;
}



$saleModulePermissions = $APPLICATION->GetGroupRight("linemedia.autoremotesuppliers");

if ($saleModulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);


$SUPPLIER_IBLOCK_ID = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS');

$APPLICATION->SetTitle(GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_INFO_TITLE'));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>

<?=CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage("LM_AUTO_SPHINX_REMOTE_SUPPLIERS_CHECK_WARNING"), 'TYPE' => 'ERROR'));?>


<?= BeginNote() ?>
<?= GetMessage('LM_AUTO_SPHINX_REMOTE_SUPPLIERS_CHECK_DESC', array('#IBLOCK_ID#' => $SUPPLIER_IBLOCK_ID)) ?>
<?= EndNote() ?>

<?
if (CModule::IncludeModule('iblock') && CModule::IncludeModule('linemedia.auto')) {
    $suppliers = LinemediaAutoSupplier::GetList(array(), array('ACTIVE' => 'Y', '!PROPERTY_api' => false));

    foreach($suppliers as $supplier) {
        if($supplier['PROPS']['api']['VALUE']['LMRSID'] == 'emex') {
            $emex_supplier_id = $supplier['PROPS']['supplier_id']['VALUE'];
        }
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/linemedia.autoremotesuppliers/classes/general/profiles/emex.supplier.php';;

    $emex_supplier = new EmexRemoteSupplier();

    $iblock_id = COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_SUPPLIERS', 0);

    if ($iblock_id > 0 && isset($emex_supplier_id)) {
        $rs = CIBlockElement::GetList(array(), array('PROPERTY_supplier_id' => $emex_supplier_id, 'IBLOCK_ID' => $iblock_id), 0, 0, array('ID', 'IBLOCK_ID', 'PROPERTY_api'));
        if ($rs && $rs->SelectedRowsCount() > 0) {
            $supplier_data = $rs->Fetch();
            $emex_supplier->setOptions($supplier_data['PROPERTY_API_VALUE']);
        }
    }
}

if(is_object($emex_supplier) && $emex_supplier->getOptions()) {
    /** @var EmexRemoteSupplier $supplier */
    try {
        $emex_brands = $emex_supplier->loadMakers();
    } catch (Exception $e) {
        echo ShowError(GetMessage('LM_AUTO_REMOTE_SUPPLIERS_EMEX_ERROR')) . ' ' . $e->getMessage();
    }


    if(is_array($emex_brands) && count($emex_brands) > 0) {
        $emex_brands = array_keys($emex_brands);
        sort($emex_brands);
        $columns = 6;
        $cols = array();
        $col_size = ceil(count($emex_brands) / $columns);
        for($i=0; $i<$columns; $i++) {
            $col = array_slice($emex_brands, $i*$col_size, $col_size);
            $cols[] = $col;
        }
        ?>
        <div class="adm-info-message grey-theme" style="background-color: #fff;  padding:20px;">

            <h3><?=GetMessage('LM_AUTO_REMOTE_SUPPLIERS_EX')?> <a href="http://emex.ru">emex.ru</a></h3>

            <div style="width:100%; height: 500px; overflow: auto;">

                <table class="lm-auto-check lm-auto-check-main">
                    <? for($i=0; $i<$col_size; $i++) { ?>
                        <tr>
                            <? foreach($cols as $col) { ?>
                                <td><?=$col[$i]?></td>
                            <? } ?>
                        </tr>
                    <? } ?>
                </table>
            </div>
        </div>
    <?
    }
} ?>


<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');