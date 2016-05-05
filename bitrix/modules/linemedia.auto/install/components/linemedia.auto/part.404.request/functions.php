<?php

IncludeTemplateLangFile(__FILE__);

function check404FormExistence()
{
    $rsf = CForm::GetBySID('LM_AUTO_REQUEST_PART_FORM');
    $ret = (!$rsf || $rsf->SelectedRowsCount() > 0);
    if ($ret) {
        $tmp = $rsf->Fetch();
        $ret = $tmp['ID'];
    }
    return $ret;
}

function create404Form()
{
$rs = CSite::GetList(($by="sort"), ($order="asc"));
while ($ar = $rs->Fetch()) {
    if ($ar["DEF"]=="Y") $def_site_id = $ar["ID"];
    $arrSites[$ar["ID"]] = $ar;
}
    $arFields = array(
                        'NAME'=>GetMessage('LM_AUTO_404_FORM_NAME'),
                        'SID'=>'LM_AUTO_REQUEST_PART_FORM',
                        'USE_CAPTCHA'=>'Y',
                        'arMENU'=>array('s1'=>GetMessage('LM_AUTO_404_MENU_ITEM_RU'),'ru'=>GetMessage('LM_AUTO_404_MENU_ITEM_RU'), 'en'=>GetMessage('LM_AUTO_404_MENU_ITEM_EN')),
                        'arSITE'=>array_keys($arrSites),
                    );
$rs = CGroup::GetList(($by="c_sort"), ($order="desc"),array('ACTIVE'=>'Y'));
while ($g = $rs->Fetch()) {
    $arFields['arGROUP'][ $g['ID'] ] =  10;//фиксированная константа, право на "заполнение".
}
$fid = CForm::Set($arFields, false, 'N');
global $APPLICATION;
if (!$fid)
    return;

$questions = array(
                    array(
                        'SID'=>'NAME',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'PHONE',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'EMAIL',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'WHAT_FIND',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'Y',
                    ),
                    array(
                        'SID'=>'COMMENT',
                        'ACTIVE'=>'Y',
                        'FIELD_TYPE'=>'text',
                        'TITLE_TYPE'=>'text',
                        'C_SORT'=>'',
                        'REQUIRED'=>'N',
                    )
                );
    foreach ($questions as $sort=>$item) {
        $item['TITLE'] = GetMessage('LM_AUTO_404_FIELD_'.$item['SID']);
        $item['FORM_ID'] = $fid;
        $item['C_SORT'] = $sort;
        $item['arANSWER'] = array(
                                    array(
                                         'MESSAGE'=>GetMessage('LM_AUTO_404_FIELD_'.$item['SID']),
                                        'C_SORT'=>$sort*100,
                                        'FIELD_TYPE'=>$item['FIELD_TYPE'],
                                        'ACTIVE'=>'Y',
                                    )
                            );
        CFormField::Set($item, false, 'N');
    }
    $arFields = array(
                        'FORM_ID'=>$fid,
                        'TITLE'=>GetMessage('LM_AUTO_404_NEW_STATUS'),
                        'ACTIVE'=>'Y',
                        'CSS'=>'statusred',
                        'DEFAULT_VALUE'=>'Y'
                    );
    CFormStatus::Set($arFields, false, 'N');
    $arFields = array(
                        'FORM_ID'=>$fid,
                        'TITLE'=>GetMessage('LM_AUTO_404_COMPLETED_STATUS'),
                        'ACTIVE'=>'Y',
                        'CSS'=>'statusgreen',
                        'DEFAULT_VALUE'=>'N'
                    );
    CFormStatus::Set($arFields, false, 'N');
}