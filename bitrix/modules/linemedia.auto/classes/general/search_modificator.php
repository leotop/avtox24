<?php

/**
 * Linemedia Autoportal
 * Main module
 * search modificator class
 * @author  Linemedia
 * @since   22/01/2012
 * @link    http://auto.linemedia.ru/
 */


/**
 * class for dispatching to proper modificator
 */
class LinemediaAutoSearchModificator
{
	
	/**
	 * directory where lay modificators
	 * @var string DIRECTORY_MODIF
	 */
	const DIRECTORY_MODIF = '../classes/general/modificators';
	
	/**
	 * as it has an paid functionality
	 * use this name in api for checking whether it was paid or not
	 * @var string API_NAME
	 */
	const API_NAME = 'use_search_modifiers';
	
	/**
	 * warning message in debug mode
	 * @var string WARNING_MESSAGE
	 */
	const WARNING_MESSAGE = 'LinemediaAutoSearchModificator is unavailable';
	
	/**
	 * apply modificators to admin panel
	 * @var string ADMIN_SEARCH 
	 */
	const ADMIN_SEARCH = 'admin';
	
	/**
	 * appellation of class
	 * @var string TITLE
	 */
	const TITLE = __CLASS__;
	
	/**
	 * search outcome
	 * @var \Iterator | array $searchOutcome
	 */
	private $searchOutcome;
	
	/**
	 * array of Application\Service\Search\CapableModifySearchInterface objs
	 * @var array $modifyStrategies
	 */
	private $modifyStrategies = array();
	
	/**
	 * @var array $modificatorConfig
	 */
	private $modificatorConfig;
	
	
	
	static $cache = array();
	
	/**
	 * Initialize via constructor injection result of search to be modified and config of modificators.
	 *  
	 * @param \Iterator | array $searchOutcome
	 * @param string $modificatorTitle
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function __construct($searchOutcome, $modificatorTitle)
	{	
		if (!$searchOutcome instanceof \Iterator && !is_array($searchOutcome)) {
			throw new \InvalidArgumentException(sprintf(
				'%s: invalid argument was conveyed to %s. Expected obj implimenting \Iterator interface or array. %s given',
				__METHOD__,
				__FUNCTION__,
				$type = is_object($searchOutcome) ? get_class($searchOutcome) : gettype($searchOutcome)
			));
		}
	    $this->searchOutcome = $searchOutcome;
	    $this->modificatorConfig = self::loadModificatorsConfig($modificatorTitle);
	}
	
	
	/**
	 * Create proper modificator by taking modificator title from config given from self::loadModificatorsConfig.
	 * 
	 * @param \DirectoryIterator $dirIterator
	 * @return void
	 */
	public function initializeModifyingStrategies(\DirectoryIterator $dirIterator)
	{
		$availableModifClasses = array_keys($this->modificatorConfig);
		foreach ($availableModifClasses as $availableModifClass) {
			foreach ($dirIterator as $file) {
				
				if (!$file->isFile() || $file->isDot()) {
					continue;
				}
				$fileName = explode('.', $file->getFilename());
				$class = current($fileName);
				
				if (strstr($availableModifClass, $class)) {
					$this->modifyStrategies []= new $class($this->modificatorConfig[$availableModifClass]);
					continue 2;
				}
			}
		}
	}
	
	
	/**
	 * Main function of processing.
	 * @return
	 */
	public function execute()
	{
		$debugSuccess= array();
		$debugError = array();
		
		// Loop through modificators.
		foreach ($this->modifyStrategies as $modificator) {
			if ($modificator->isAlterationFeasible($this->searchOutcome)) {
				$this->searchOutcome = $modificator->applyModificatorToSearch($this->searchOutcome, $this->modifyStrategies);
				$info = $modificator->getDebugInfo();
                $debugSuccess []= $info;
                $info = array_pop($info);

                LinemediaAutoDebug::add($info['modificatorID'], print_r($info, true), LM_AUTO_DEBUG_WARNING);

                continue;
			}
			$debugError []= $modificator->getDebugInfo();
		}
		
		// Attach debug info.
		count($debugSuccess) > 0  ? \LinemediaAutoDebug::add('Modificators passed checkout', print_r($debugSuccess, true), LM_AUTO_DEBUG_WARNING) : '';
		count($debugError) > 0 ? \LinemediaAutoDebug::add('Modificators not passed checkout by given conditions', print_r($debugError, true), LM_AUTO_DEBUG_WARNING) : '';
		
		return $this->searchOutcome;
	}
	
	
	/**
	 * Load configuration data for appropriate modificator.
	 * 
	 * @param string $modificatorTitle
	 * @return array
	 */
	private static function loadModificatorsConfig($modificatorTitle)
	{
		
		$cache_key = md5(__METHOD__ . json_encode($modificatorTitle));
		if(self::$cache[$cache_key]) {
			return self::$cache[$cache_key];
		}
		
		$setModificators = array();
		$configurations = array();
		$auxiliaryData = array();
	
		// Retrieve all modificator, comprise in block designated above.
		$rawModif = CIBlockElement::GetList(
			array('SORT' => 'ASC'),
			array(
				'IBLOCK_ID' => COption::GetOptionInt('linemedia.auto', 'LM_AUTO_IBLOCK_MODIFICATOR'),
				'ACTIVE' => 'Y',
				'SECTION_CODE' => $modificatorTitle,
				'INCLUDE_SUBSECTIONS' => 'Y'
			)
		);
		
		$titles = array();
		while ($ob = $rawModif->GetNextElement()) {
			$fields = $ob->GetFields();
			$props = $ob->GetProperties();
			$props['modificatorID'] = $props['action']['VALUE_XML_ID'] . '#' . $fields['ID'];
			$props['partition'] = $modificatorTitle;
			$setModificators[$props['modificatorID']] = $props;
		}
		
		self::$cache[$cache_key]= $setModificators;
		return $setModificators;
	}
}







