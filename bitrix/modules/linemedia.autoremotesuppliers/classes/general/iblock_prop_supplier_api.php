<?php

/**
 * Linemedia Autoportal
 * Main module
 * Iblock property for supplier APIs
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);


/**
 * Class LinemediaAutoRemoteSuppliersIblockPropertyApi
 */
class LinemediaAutoRemoteSuppliersIblockPropertyApi
{
    /**
     * @return array
     */
    function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'supplier_api',
            'DESCRIPTION' => GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_SUPPLIER_API_TITLE'),

            'CheckFields' => array('LinemediaAutoRemoteSuppliersIblockPropertyApi', 'CheckFields'),
            'GetLength' => array('LinemediaAutoRemoteSuppliersIblockPropertyApi', 'GetLength'),
            'GetPropertyFieldHtml' => array('LinemediaAutoRemoteSuppliersIblockPropertyApi', 'GetEditField'),
            'GetAdminListViewHTML' => array('LinemediaAutoRemoteSuppliersIblockPropertyApi', 'GetFieldView'),
            'GetPublicViewHTML' => array('LinemediaAutoRemoteSuppliersIblockPropertyApi', 'GetFieldView'),
            'GetPublicEditHTML' => array('LinemediaAutoRemoteSuppliersIblockPropertyApi', 'GetEditField'),
            "ConvertToDB" => array("LinemediaAutoRemoteSuppliersIblockPropertyApi","ConvertToDB"),
            "ConvertFromDB" => array("LinemediaAutoRemoteSuppliersIblockPropertyApi","ConvertFromDB"),
        );
    }

    /**
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    function ConvertFromDB($arProperty, $value)
    {
        $val = $value['VALUE'];
        $value['VALUE'] = json_decode($value['VALUE'], true);
        if(empty($value['VALUE'])) $value['VALUE'] = $val;
        return $value;
    }

    /**
     * @param $arProperty
     * @param $value
     * @return array|string
     */
    function ConvertToDB($arProperty, $value)
    {
        if (empty($value) || empty($value['VALUE'])) {
            return $value;
        }
        CModule::IncludeModule('linemedia.autoremotesuppliers');

        $tmp = json_decode($value['VALUE'], 1);

        if (is_array($tmp)) {//already jsoned
            return $value;
        }

        if (!is_array($value['VALUE'])) { //old version
            $supplier_id = strval($value['VALUE']);
        } else {//new version
            return json_encode($value['VALUE']);
        }
            try{
                $supplier = LinemediaAutoRemoteSuppliersSupplier::load($supplier_id);
            } catch (Exception $e) {
                echo $e->GetMessage();
            }

            
        $config = $supplier->getConfigVars();
        $config['cache_time'] = array(
        	'title' => GetMessage('LM_AUTO_RS_ACCOUNT_CACHE_TIME'),
            'type'  => 'float',
        );

	    $config['timeout'] = array(
		    'title' => GetMessage('LM_AUTO_RS_ACCOUNT_TIMEOUT'),
		    'type'  => 'float',
	    );
        
        $prefs = array('LMRSID'=>$supplier_id);
        foreach ($config as $config_code => $config_data) {
            $id = 'lmrs_' . $config_code;
            if (isset($_REQUEST[ $id ])) {//detail element edit
                $prefs[ $config_code ] = strval($_REQUEST[ $id ]);
            } else if (is_array($value['VALUE']) && isset($value['VALUE'][$config_code])) {//inline edit
                $prefs[ $config_code ] = $value['VALUE'][ $config_code ];
            } else {
                $prefs[ $config_code ] = false;
            }
        }
        return json_encode($prefs);
    }

    /**
     * @param $arProperty
     * @param $value
     * @return array
     */
    function CheckFields($arProperty, $value) {
        return array();
    }

    /**
     * @param $arProperty
     * @param $value
     * @return int
     */
    function GetLength($arProperty, $value) {
        return strlen($value['VALUE']);
    }

    /**
     * @param $arProperty
     * @param $value
     * @param $htmlElement
     * @return string
     */
    function GetEditField($arProperty, $value, $htmlElement)
    {
//         out($value);
    	CModule::includeModule('linemedia.autoremotesuppliers');
        $remote_suppliers = LinemediaAutoRemoteSuppliersSupplier::getList();
        CJSCore::Init(array('jquery'));
        ob_start();
    ?>
    <script type="text/javascript">
        var loadRemoteSupplierForm=function(supp_id)
        {
            $('#LMRSpropsWrp').load('/bitrix/admin/linemedia.autoremotesuppliers_ajaxprop.php?sid='+supp_id);
        }
    </script>
    <?
    $js = ob_get_clean();

        $str = $js.'<select name="' . $htmlElement['VALUE'] . '" onchange="loadRemoteSupplierForm(this.value);">';
        $str .= '<option value="">' . GetMessage('LM_AUTO_MAIN_IBLOCK_PROP_SUPPLIER_API_ALL') . '</option>';
        
        foreach ($remote_suppliers as $code => $title) {
            $selected = ($value['VALUE']['LMRSID'] == $code || $value['VALUE'] == $code) ? ' selected' : '';
            $str .= '<option value="' . $code . '"' . $selected . '>' . $title . '</option>';
        }
        
        $str .= '</select>';

        $str .='<div id="LMRSpropsWrp">'.(!empty($value['VALUE']['LMRSID'])?self::showSupplierOptionsForm($value['VALUE']['LMRSID'], $value['VALUE']):'').'</div>';
        return $str;
    }

    /**
     * @param $arProperty
     * @param $value
     * @param $htmlElement
     * @return mixed
     */
    function GetFieldView($arProperty, $value, $htmlElement)
    {
        if (is_array($value['VALUE']))
            return $value['VALUE']['LMRSID'];
        else
            return $value['VALUE'];
    }

    /**
     * форма настроек поставщика. вариантов два: данные есть и данных нет, потому что элемент создают.
     * @param $supplier_id
     * @param bool $arData
     * @return string
     */
    function showSupplierOptionsForm($supplier_id, $arData=false)
    {
        CModule::IncludeModule('linemedia.autoremotesuppliers');
        $supplier = LinemediaAutoRemoteSuppliersSupplier::getSupplierClass($supplier_id);

//         out($supplier_id);
        $config = $supplier->getConfigVars();
        $config['cache_time'] = array(
        	'title' => GetMessage('LM_AUTO_RS_ACCOUNT_CACHE_TIME'),
            'type'  => 'float',
        );
	    $config['timeout'] = array(
		    'title' => GetMessage('LM_AUTO_RS_ACCOUNT_TIMEOUT'),
		    'type'  => 'float',
	    );
        
        if ($arData===false) {
            $vals = $supplier->getOptions();
        } else {
            $vals = $arData;
        }
        /*
            * Все настройки поставщика
            */
        ob_start();
        ?>
        <div style="margin-top:10px;padding-left: 10px;border:1px solid silver;">
            <table>
                <tr>
                    <th align="left"><?=GetMessage('LM_AUTO_RS_ACCOUNT_SETTINGS')?></th>
                </tr><?

//         out($supplier_id, $vals);
        foreach ($config as $config_code => $config_data) {
            $id = 'lmrs_' . $config_code;
            $value = $vals[ $config_code ];
            if (empty($value)) {
                $value = $config_data['default'];
            }
            ?>
            <tr>
                <td width="50%" valign="top">
                    <label for="<?= $id ?>"><?= $config_data['title'] ?>:</label>
                </td>
            </tr><tr>
                <td valign="top">
                    <?
                    switch ($config_data['type']) {
                        case 'password':
                            echo '<input type="password" name="' . $id . '" id="' . $id . '" value="' . $value . '" />';
                            break;
                        case 'checkbox':
                            echo '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="true" ' . ($value ? 'checked' : '') . ' />';
                            break;
                        case 'list':
                            echo '<select name="', $id, '">';
                            foreach ($config_data['values'] as $k => $v) {
                                echo '<option value="', $k, '" ', (($k == $value) ? ('selected') : ('')), '>';
                                echo $v;
                                echo '</option>';
                            }
                            echo '</select>';
                            break;
                        case 'string':
                        default:
                            echo '<input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" />';
                    }
                    ?>
                    <p><?= $config_data['description'] ?></p>
                </td>
            </tr>
            <?
        }?></table></div><?
        return ob_get_clean();
    }//sub
}
