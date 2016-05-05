<?php

/**
 * Linemedia Autoportal
 * Suppliers module
 * Requests class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 

/*
 * Requests
 */
class LinemediaAutoSuppliersRequestExporter
{
    const DIR_REQUESTS  = '/upload/linemedia.autosuppliers/requests';
    const DIR_UPLOAD    = '/upload/linemedia.autosuppliers/upload';
    
    protected $request  = null;
    protected $filename = 'file';
	protected $currency_title = '';
    
    
    public function __construct()
    {
        CModule::IncludeModule('linemedia.auto');
    }
    
    
    public function getRequestIDByName($filename)
    {
        
    }
    
    
    /**
     * Установка заявки.
     */
    public function setRequest(LinemediaAutoSuppliersRequest $request)
    {
        $this->request = $request;
    }
    
    
    /**
     * Генерация внешнего названия файла.
     */
    public function getFileTitle($ext = 'xls')
    {
        $data = $this->request->getArray();
        
        $supplier = new LinemediaAutoSupplier($data['supplier_id']);
        
        $filename = str_replace(
            array('#NUM#', '#DATE#', '#FROM#', '#SUPPLIER#'),
            array(
                $this->request->getID(),
                date('Y-m-d G:i', strtotime($data['date'])),
                COption::GetOptionString('main', 'site_name'),
                $supplier->get('NAME')
            ),
            GetMessage('LM_AUTO_SUPPLIERS_FILENAME')
        );
        
        $filename .= '.'.$ext;
        
        return str_replace(array('\'', '"'), '', $filename);
    }


    /**
     * Создание шапки.
     */
    public function getTitle()
    {
        $data = $this->request->getArray();
        
        $supplier = new LinemediaAutoSupplier($data['supplier_id']);
        
        /*
         * Создание excel-файла.
         */
        $excel = new LinemediaAutoExcel();
        
        $title = str_replace(
            array('#NUM#', '#DATE#', '#FROM#', '#SUPPLIER#'),
            array(
                $this->request->getID(),
                date('Y-m-d G:i', strtotime($data['date'])),
                COption::GetOptionString('main', 'site_name'),
                $supplier->get('NAME')
            ),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_NOTE')
        );
        
        return $title;
    }
    
    
    /**
     * Получение данных для выгрузки.
     */
    public function getData()
    {
        $data = $this->request->getArray();
        
        $supplier = new LinemediaAutoSupplier($data['supplier_id']);
        
        $currencies = array();
        $lcur = CCurrency::GetList(($b = "name"), ($o = "asc"), LANGUAGE_ID);
        while ($lcur_res = $lcur->Fetch()) {
            $currencies[$lcur_res["CURRENCY"]] = $lcur_res;
        }
        
        // Базовая валюта.
        $base_currency = $supplier->get('currency');
        
        // Обозначение валюты.
        $this->currency_title = $currencies[$base_currency]['CURRENCY'];
        
        
        /*
         * Получим нужные корзины и список заказов
         */
        $result = array();
        $dbBasketItems = CSaleBasket::GetList(array(), array('ID' => $data['basket_ids']), false, false, array('ID', 'PRODUCT_ID', 'QUANTITY', 'PRICE', 'WEIGHT', 'ORDER_ID', 'NAME'));
        while ($basket = $dbBasketItems->Fetch()) {
            $props_res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID']));
            while ($prop = $props_res->Fetch()) {
                $basket['PROPS'][$prop['CODE']] = $prop;
            }
            
            $brand_title = $basket['PROPS']['brand_title']['VALUE'];
            
            // Выгружаем оригинальные артикулы или стандартные.
            if (COption::GetOptionString($sModuleId, 'LM_AUTO_SUPPLIERS_EXPORT_ORIGINAL_ARTICLES', 'N') == 'Y') {
            	$article = $basket['PROPS']['original_article']['VALUE'];
            } else {
            	$article = $basket['PROPS']['article']['VALUE'];
            }
            
            // Если артикул пустой, прописываем стандартный артикул.
			if (empty($artilce)) {
				$article = $basket['PROPS']['article']['VALUE'];
			}
            $quantity = $basket['QUANTITY'];
            
            $price = $basket['PROPS']['base_price']['VALUE'];
            
            /*
             * Пересчёт валюты
             */
            if ($basket['CURRENCY'] != '' && $basket['CURRENCY'] != $base_currency) {
                $price = $price * $currencies[$basket['CURRENCY']]['AMOUNT'];
            }
            
            $result[$brand_title][$article][$price]['quantity'] += $quantity;
            $result[$brand_title][$article][$price]['title'] = $basket['NAME'];
        }

        return $result;
    }
    
    
    /**
     * Получение таблицы HMTL.
     */
    public function getHTML()
    {
        $result = $this->getData();
        
        /*
         * Создание excel-файла.
         */
        $excel = new LinemediaAutoExcel();
        
        $excel->addHeader(array(
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_BRAND'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_ARTICLE'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_TITLE'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_PRICE') . ' ('.$this->currency_title.')',
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_QUANTITY_ORDERED'),
            GetMessage('LM_AUTO_SUPPLIERS_COLUMN_QUANTITY_CONFIRMED'),
        ));
        
        $note = $this->getTitle();
        
        $excel->addNote($note);
        
        $excel->addColumnTypes(array(
            LinemediaAutoExcel::TYPE_TEXT,
            LinemediaAutoExcel::TYPE_TEXT,
            LinemediaAutoExcel::TYPE_TEXT,
            LinemediaAutoExcel::TYPE_DECIMAL,
            LinemediaAutoExcel::TYPE_INT,
            LinemediaAutoExcel::TYPE_INT
        ));
        
        foreach ($result as $brand_title => $articles) {
            foreach ($articles as $article => $prices) {
                foreach ($prices as $price => $item) {
                    $excel->addRow(array(
                        $brand_title,
                        $article,
                        $item['title'],
                        $price,
                        $item['quantity'],
                        0
                    ));
                }
            }
        }
        
        return $excel->getResult();
    }
    
    
    /**
     * Сохрнаить как XLS.
     */
    public function getCSV()
    {
        $result = $this->getData();
        
        $output = '';
        $output .= GetMessage('LM_AUTO_SUPPLIERS_COLUMN_BRAND') . ';' . GetMessage('LM_AUTO_SUPPLIERS_COLUMN_ARTICLE') . ';' . GetMessage('LM_AUTO_SUPPLIERS_COLUMN_TITLE') . ';' . GetMessage
			('LM_AUTO_SUPPLIERS_COLUMN_QUANTITY') . ';' . GetMessage('LM_AUTO_SUPPLIERS_COLUMN_PRICE') . ' ('.$this->currency_title.');' . "\n";
        foreach ($result as $brand_title => $articles) {
            foreach ($articles as $article => $prices) {
                foreach ($prices as $price => $data) {
                    $output .= $brand_title . ';' . $article . ';' . $data['title'] . ';' . $data['quantity'] . ';' . $price . "\n";
                }
            }
        }
        $output = iconv('UTF-8', 'windows-1251', $output);
        
        return $output;
    }
    
    
    
    /**
     * Сохрнаить как XLS.
     */
    public function saveXLS()
    {
        $html = $this->getHTML();
        
        $data = $this->request->getArray();
        
        $supplier = new LinemediaAutoSupplier($data['supplier_id']);
        
        $filename = str_replace(
            array('#NUM#', '#DATE#', '#FROM#', '#SUPPLIER#'),
            array(
                $this->request->getID(),
                date('d_m_Y', strtotime($data['date'])),
                COption::GetOptionString('main', 'site_name'),
                $supplier->get('NAME')
            ),
            GetMessage('LM_AUTO_SUPPLIERS_FILENAME')
        );
        
        //$filename = $_SERVER['DOCUMENT_ROOT'].self::DIR.'/'.$filename.'.html'; // '/request_'.$this->request->getID().'.html';
        
        $filename = $_SERVER['DOCUMENT_ROOT'].self::DIR_REQUESTS.'/request_'.$this->request->getID();
        
        file_put_contents($filename, $html);
        
        $filename = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->convert2XLS($filename));
        
        return $filename;
    }
    
    
    
    /**
     * Конвертация файла.
     */
    public function convert2XML($filename, $from = 'xls')
    {
        switch ($from) {
            case 'xls':
                $cmd = 'DISPLAY=:0 ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename . '.xml');
                $cmd_result = shell_exec($cmd);
                
                unlink($filename);
                return $filename . '.xml';
                break;
            default:
                return $filename;
        }
    }
    
    
    /**
     * Конвертация файла.
     */
    public function convert2CSV($filename, $from = 'xls')
    {
        switch ($from) {
            case 'xls':
            case 'xlsx':
                $cmd = 'DISPLAY=:0 ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename . '.csv');
                $cmd_result = shell_exec($cmd);
                
                unlink($filename);
                return $filename . '.csv';
                break;
            default:
                return $filename;
        }
    }
    
    
    /**
     * Конвертация файла.
     */
    public function convert2XLS($filename, $from = 'html')
    {
        switch ($from) {
            case 'html':

				$option = COption::GetOptionString('linemedia.autosuppliers', 'LM_AUTO_SUPPLIERS_XLS_TYPE', 'ssconvert');

				if ($option == 'html') {
					return $filename;
				}

				if($option == 'ssconvert') {
					$cmd = 'DISPLAY=:0 ssconvert ' . escapeshellarg($filename) . ' ' . escapeshellarg($filename . '.xls');
					$cmd_result = shell_exec($cmd);

					unlink($filename);
					return $filename . '.xls';
				}
                break;
            default:
                return $filename;
        }
    }
}