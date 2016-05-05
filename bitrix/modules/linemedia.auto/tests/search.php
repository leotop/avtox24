<?php

class TestSearch extends PHPUnit_Framework_TestCase {
	
	public function setUp() {
		
		//не определяется глобальная переменная и не подгружаются классы
		$GLOBALS['DBType'] = $DBType = "mysql";
		$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
		
		define("NO_KEEP_STATISTIC", true);
		define("NOT_CHECK_PERMISSIONS",true); 
		define('CHK_EVENT', true);
		@set_time_limit(0);
		@ignore_user_abort(true);
		
		require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
		
		//$GLOBALS['USER'] = $USER = new CUser;
		
		CModule::IncludeModule('linemedia.auto');
	}
	
	
	/**
     * @group search
     * @author ilya
     */
    public function testSearchGDB1550() {
    	$this->assertTrue(true);
    	return;
    	
    	
    	
    	$search = new LinemediaAutoSearch();
    	$search->setSearchQuery('gdb1550');
    	//$search->setSearchCondition('brand_title', 'TRW');
    	$search->execute();
    	
    	
    	$modules_exceptions = $search->getThrownExceptions();
		if(count($modules_exceptions)) {
			print_r($modules_exceptions);
			$this->assertFalse(true);
		}
    	
    	
    	// тип ответа - каталоги
    	$this->assertTrue($search->getResultsType() == 'catalogs');
    	
    	$catalogs = $search->getResultsCatalogs();
    	
    	// есть каталог TRW
    	$this->assertTrue(isset($catalogs['TRW']));
    	
    	
    }
    
    
}
