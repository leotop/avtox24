<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/linemedia.autosuppliers/include.php"); // ������������� ������
IncludeModuleLangFile(__FILE__);
$modulePermissions = $APPLICATION->GetGroupRight("linemedia.autosuppliers");
if ($modulePermissions == 'D') {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule("linemedia.auto")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("linemedia.autosuppliers")) {
    ShowError('LM_AUTO MODULE NOT INSTALLED');
    return;
}

if (!CModule::IncludeModule("sale")) {
    ShowError('SALE MODULE NOT INSTALLED');
    return;
}

set_time_limit(0);

/*
 * ��������� ������.
 */
@setlocale(LC_ALL, "ru_RU");


$POST_RIGHT = 'W';



/*
 * ����������.
 */
$suppliers = LinemediaAutoSupplier::GetList();

$message = null;//new CAdminMessage();

/*
 * �������� ������.
 */
$filename = $_SERVER['DOCUMENT_ROOT'].'/upload/linemedia.autosuppliers/upload/102/102a04822b24dc6fe591b188fdc62e0e.xls.csv';


 
if ($REQUEST_METHOD == "POST" && !empty($_FILES['REQUEST']) && check_bitrix_sessid()) {
    $arFile = $_FILES['REQUEST'];
    
    /*
     * ����� ������ ������.
     */
    preg_match('/#(\d+)/', $arFile['name'], $match);
    $requestID = (int) $match[1];
    
    if ($requestID > 0) {
        $request = new LinemediaAutoSuppliersRequest($requestID);
        
        /*
         * ��������� ���.
         */
        $step = new LinemediaAutoSuppliersStep($request->get('step'));
        
        /*
         * ���������� � ����������� �����.
         */
        $fileID = CFile::SaveFile($_FILES['REQUEST'], str_replace('/upload/', '', LinemediaAutoSuppliersRequestExporter::DIR_UPLOAD));
        
        if ($fileID > 0) {
            
            $filename = $_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($fileID);
            
            die($filename);
            /*
            $encoding = mb_detect_encoding(file_get_contents($filename));
            if ($encoding != 'UTF-8) {
                $cmd = 'iconv -f ' . $encoding . ' -t utf8 "' . escapeshellarg($filename) . '" -o "' . escapeshellarg($filename) . '.utf"';
                $cmd_result = shell_exec($cmd);
                unlink($filename);
                rename($filename . '.utf', $filename);
            }
            */
            //$exporter = new LinemediaAutoSuppliersRequestExporter();
            //$filename = $exporter->convert2CSV($filename);
            
            $importer = new LinemediaAutoSuppliersRequestImporter();
            
            try {
                $arData = $importer->import($filename);
            } catch (Exception $e) {
                $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => GetMessage('LM_AUTO_SUPPLIERS_ERROR_FILE_READ')));
            }
             
             print_r($arData); die();
             
            /*
            try {
                $handle = fopen($filename, "r");
                
                // ���������� ������������� ����.
                if (!flock($handle, LOCK_EX)) {
                    fclose($handle);
                    return;
                }
            } catch (Exception $e) {
                $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => GetMessage('LM_AUTO_SUPPLIERS_ERROR_FILE_READ')));
            }
            */
            if (empty($message)) {
                
                /*
                 * ������ �� ������� ����� �������� ������� ������� �������.
                 */
                $status = $step->get('default-status');
                
                if (!empty($status)) {
                    
                    
                    /*
                     * ������� �������.
                     */
                    $res = LinemediaAutoSuppliersRequestBasket::GetList(array(), array('request_id' => $request->get('id'), 'supplier_id' => $request->get('supplier_id')));
                    $basket_ids = array();
                    while ($request_basket = $res->Fetch()) {
                        $basket_ids []= (int) $request_basket['basket_id'];
                    }
                    
                    // ������ ������.
                    $baskets = array();
                    $res = CSaleBasket::GetList(array(), array('ID' => $basket_ids));
                    while ($basket = $res->Fetch()) {
                        $baskets[$basket['ID']] = $basket;
                    }
                    
                    // ������ �������.
                    $res = CSaleBasket::GetPropsList(array(), array('BASKET_ID' => $basket_ids), false, false, array('CODE', 'NAME', 'VALUE', 'SORT', 'BASKET_ID'));
                    while ($prop = $res->Fetch()) {
                        $basket_id = $prop['BASKET_ID'];
                        unset($prop['BASKET_ID']);
                        $baskets[$basket_id]['PROPS'][$prop['CODE']] = $prop;
                    }
                    
                    $arResult = array();
                    
                    /*
                     * ���������� ��������������� ������
                     
                    $result = array();
                    foreach ($baskets as $basket) {
                        $brand_title = $basket['PROPS']['brand_title']['VALUE'];
                        $article     = $basket['PROPS']['article']['VALUE'];
                        $quantity    = $basket['QUANTITY'];
                        
                        $result[$brand_title][$article]['quantity'] += $quantity;
                        $result[$brand_title][$article]['basket_ids'][] = $basket['ID'];
                    }
                    */
                    
                    $lmcart = new LinemediaAutoBasket();
                    
                    /*
                     * ���������� �� ��������.
                     */
                    foreach ($baskets as $basket) {
                        /*
                         * ������� ������ �������.
                         */
                        $basket['PROPS'] = LinemediaAutoSuppliersRequestBasket::loadBasketProps($basket['ID']);
                        
                        $last_status = $basket['PROPS']['status']['VALUE']; 
                        
                        /*
                         * ���������� ��� �������� � ����� ������.
                         */
                        $brand   = $basket['PROPS']['brand_title']['VALUE'];
                        $article = $basket['PROPS']['article']['VALUE'];
                        
                        $left = (int) $arData[$brand][$article]['quantityA'];
                        
                        // ���� ������ ������ �� ����� - ��������� � ���������� ������.
                        if ($left <= 0) {
                            continue;
                        }
                        
                        
                        /*
                         * ��������� ��������� �������
                         */
                        define('LM_AUTO_SUPPLIERS_ALLOW_BASKET_CHANGE_' . $basket['ID'], true);
                        
                        /*
                         * ����� ������ �� ����! ���� ��������� �������.
                         */
                        if ($left < $basket['QUANTITY']) {
                            $diff = array(
                                'PROPS' => array(
                                    'status' => array(
                                        'VALUE' => $last_status
                                    ),
                                    'date_status' => array(
                                        'VALUE' => date('d.m.Y G:i:s')
                                    )
                                )
                            );
                            $new_basket_id = LinemediaAutoSuppliersRequestBasket::splitBasket($basket, $left, $diff);
                            
                            /*
                             * ������ ������� ���������, ��� ���� �������������!
                             */
                            $props = LinemediaAutoSuppliersRequestBasket::loadBasketProps($basket['ID']);
                            
                            
                            $lmcart->statusItem($basket['ID'], $status);
                            unset($props['status']);
                            $props['date_status']['VALUE'] = date('d.m.Y G:i:s');
                            LinemediaAutoBasket::setProperty($basket['ID'], array_values($props));
                        } else {
                            /*
                             * ����� ������ ����
                             * ������ ������� �������� � ��
                             */
                            $lmcart->statusItem($basket['ID'], $status);
                            unset($props['status']);
                            $props['date_status']['VALUE'] = date('d.m.Y G:i:s');
                            LinemediaAutoBasket::setProperty($basket['ID'], array_values($props));
                        }
                        
                        
                        // ���������� �������.
                        $arResult[$brand][$article] = array('quantity' => $left, 'change' => 'Y');
                        
                        
                        /*
                         * �������� ������� ���������� ������ � ������
                         */
                        LinemediaAutoSuppliersRequestBasket::checkDuplicateBaskets($basket['ORDER_ID']);
                        
                        /*
                         * ������ ������� � ���� ������� ������ ��� - ������� � ��������� ��������� ������
                         */ 
                        $left -= $basket['QUANTITY'];
                        if ($left <= 0) {
                            break;
                        }
                    }
                    
                    
                    /*
                     * �������� ������ ������!
                     */
                    if ($left > 0) {
                        throw new Exception('Supplier [' . $supplier_id . '], brand [' . $brand_title . '], article [' . $article . '] left ' . $left . ' details');
                    }
                    
                    
                    echo '<pre>';
                    print_r($arResult);
                    echo '</pre>';
                    
                    /*
                     * ������ ���� ����� ��������.
                     */
                    //CFile::Delete($fileID);
                     
                     /*
                      * �������� �� ������� � ���������.
                      */
                    // $step = LinemediaAutoSuppliersStep::getNextStepByKey($request->get('step'));
                    // LocalRedirect('/bitrix/admin/linemedia.autosuppliers_step.php?key='.$step->get('key').'&find_supplier_id='.$request->get('supplier_id'));
                    // exit();
                } else {
                    $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => GetMessage('LM_AUTO_SUPPLIERS_ERROR_STATUS')));
                }
            }
        } else {
            $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => GetMessage('LM_AUTO_SUPPLIERS_ERROR_FILE_SAVE')));
        }
    } else {
        $message = new CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => GetMessage('LM_AUTO_SUPPLIERS_ERROR_REQUEST_NOT_FOUND')));
    }
    
    
    
    /*
    if (strlen($URL_DATA_FILE) > 0) {
        $URL_DATA_FILE = trim(str_replace("\\", "/", trim($URL_DATA_FILE)), '/');
        $FILE_NAME = rel2abs($_SERVER['DOCUMENT_ROOT'], '/'.$URL_DATA_FILE);
        
        
        
        /*
         * �������� �����.
        $exporter = new LinemediaAutoSuppliersRequestExporter();
        $filename = $exporter->convert2CSV($_SERVER['DOCUMENT_ROOT'].$FILE_NAME);
        
        echo $filename;
        
        var_dump(is_file($filename));
        
        echo file_get_contents($filename);
        
        //var_dump($filename);
    }*/
}




CUtil::InitJSCore(array('window'));


$APPLICATION->AddHeadScript('http://yandex.st/jquery/1.8.2/jquery.min.js');

// ��������� ��������� ��������
$APPLICATION->SetTitle(GetMessage('LM_AUTO_SUPPLIERS_IN_TITLE'));

// �� ������� ��������� ���������� ������ � �����
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("LM_AUTO_SUPPLIERS_TAB"), "ICON" => "iblock", "TITLE" => GetMessage("LM_AUTO_SUPPLIERS_TAB")),
);


$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);


?>

<? if (!empty($message)) { ?>
    <?= $message->Show() ?>
<? } ?>

<form name="dataload" id="dataload" method="post" action="<?= $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
<input type="hidden" name="lang" value="<?= LANG ?>" />
<?= bitrix_sessid_post(); ?>
<? $tabControl->Begin(); ?>
<? $tabControl->BeginNextTab(); ?>
<tr>
    <td><?= GetMessage("LM_AUTO_SUPPLIERS_FILE") ?>:</td>
    <td>
        
        <input type="file" name="REQUEST" />
        <?/*
        <input type="text" name="URL_DATA_FILE" value="<?= htmlspecialchars($URL_DATA_FILE) ?>" size="30" />
        <input type="button" value="<?= GetMessage("LM_AUTO_SUPPLIERS_OPEN") ?>" OnClick="BtnClick()" />
        <?  // �������� �����.
            CAdminFileDialog::ShowScript
            (
                Array(
                    "event" => "BtnClick",
                    "arResultDest" => array("FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_DATA_FILE"),
                    "arPath" => array('SITE' => SITE_ID, 'PATH' => '/'.COption::GetOptionString("main", "upload_dir", "upload") . LinemediaAutoSuppliersRequestExporter::DIR_UPLOAD . '/'),
                    "select" => 'F', // F - file only, D - folder only
                    "operation" => 'O', // O - open, S - save
                    "showUploadTab" => true,
                    "showAddToMenuTab" => false,
                    "fileFilter" => 'xls',
                    "allowAllFiles" => false,
                    "SaveConfig" => true,
                )
            );
        */?>
    </td>
</tr>
<? $tabControl->EndTab(); ?>
<? $tabControl->Buttons(); ?>

<input type="submit" name="import" value="<?= GetMessage("LM_AUTO_SUPPLIERS_SUBMIT") ?>" />

<? $tabControl->End(); ?>
</form>

<? require ($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
