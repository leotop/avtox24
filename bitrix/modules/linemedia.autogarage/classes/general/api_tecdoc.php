<?php
/**
 * Linemedia Autoportal
 * Main module
 * Connection to Linemedia API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
IncludeModuleLangFile(__FILE__);
 
/**
 * Обертка над запросами к API
 */
class LinemediaAutoGarageApiTecDoc
{
    protected $api      = null; // LinemediaAutoApiTecDocDriver
    protected $rights   = null; // LinemediaAutoTecDocRights
    
    
    public function __construct($set_id='default')
    {
        if (!CModule::IncludeModule('linemedia.auto')) {
            throw new Exception('No install modules linemedia.auto');
            return;
        }

	    if (!CModule::IncludeModule("linemedia.autotecdoc")) {
		    throw new Exception('No install modules linemedia.autotecdoc');
		    return;
	    }
        
        $api_id     = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_ID');
        $api_key    = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_KEY');
        $api_url    = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_URL');
        $api_format = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_FORMAT');
        
        $this->api = new LinemediaAutoTecDocApiDriver($api_id, $api_key, $api_url, $api_format);
        $this->api->changeModificationsSetId($set_id);
        
        $this->rights = LinemediaAutoTecDocRights::getInstance();
    }
    
    
    /**
     * Получение списка брендов.
     */
    public function getBrands()
    {
        $result = array('ITEMS' => false, 'ACCESS_LIST_DISABLE' => array());
        
        $brands = $this->api->query('Main.Tecdoc.getBrands', $data = array());
        if (is_array($brands) && $brands['status'] === 'ok' && ($brands['data']) > 0 ) {
            $result['ITEMS'] = $brands['data']['brands'];
        }
        
        foreach ($result['ITEMS'] as $i => $item) {
            if ($item['hidden'] == 'Y') {
                unset($result['ITEMS'][$i]);
            }
        }
        
        return $result;
    }
    
    
    /**
     * Получение списка моделей.
     */
    public function getModels($arParams = array())
    {
        $result = array('ITEMS' => false, 'ACCESS_LIST_DISABLE' => array());
        
        if (isset($arParams['brand_id']) && !empty($arParams['brand_id'])) {
            
            $models = $this->api->query('Main.Tecdoc.getModels', $data = array('brand_id' => $arParams['brand_id']));
            if (is_array($models) && $models['status'] === 'ok' && ($models['data']) > 0 ) {
                $result['ITEMS'] = $models['data']['models'];
            }
            
            foreach ($result['ITEMS'] as $i => $item) {
                if ($item['hidden'] == 'Y') {
                    unset($result['ITEMS'][$i]);
                }
            }
        }
        
        return $result;
    }
    
    
    /**
     * Получение списка модификаций.
     */
    public function getModifications($arParams = array())
    {
        $result = array('ITEMS' => false, 'ACCESS_LIST_DISABLE' => array());
        
        if (isset($arParams['brand_id']) && !empty($arParams['brand_id']) && isset($arParams['model_id']) && !empty($arParams['model_id'])) {
            
            $modifications = $this->api->query('Main.Tecdoc.getModifications', $data = array('brand_id' => $arParams['brand_id'], 'model_id' => $arParams['model_id']));
            if (is_array($modifications) && $modifications['status'] === 'ok' && ($modifications['data']) > 0 ) {
                $result['ITEMS'] = $modifications['data']['modifications'];
            }
            
            foreach ($result['ITEMS'] as $i => $item) {
                if ($item['hidden'] == 'Y') {
                    unset($result['ITEMS'][$i]);
                }
            }
        }
        
        return $result;
    }
}