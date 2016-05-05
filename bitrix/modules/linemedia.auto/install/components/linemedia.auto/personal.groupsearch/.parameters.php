<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        'ACTION_VAR'     =>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  'act',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_ACTION_VAR')
        ),
        
        'SEARCH_ARTICLE_URL'=>  array(
            'TYPE'      =>  'STRING',
            'DEFAULT'   =>  '/auto/search/#ARTICLE#/',
            'PARENT'    =>  'BASE',
            'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_ARTICLE_URL')
        ),
    	
        'DELAY'=>  array(
    		'TYPE'      =>  'CHECKBOX',
        	'DEFAULT'   => 'Y',
    		'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_DEALY')
    	),
    	
    	'CALCULATE_DELAY'=>  array(
    		'TYPE'      =>  'CHECKBOX',
    		'DEFAULT'   => 'Y',
    		'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_CALCULATE_DEALY')
        ),
    		
    	'LIMIT'=>  array(
    		'TYPE'      =>  'CHECKBOX',
    		'DEFAULT'   => 'Y',
    		'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_LIMIT')
    	),
    		
    	'FUNDS_ON_ACCOUT'=>  array(
    		'TYPE'      =>  'CHECKBOX',
    		'DEFAULT'   => 'Y',
    		'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_FUNDS')
    	),
    		
       'ORDER'=>  array(
    		'TYPE'      =>  'CHECKBOX',
    		'DEFAULT'   => 'Y',
    		'NAME'      =>  GetMessage('LM_AUTO_MAIN_SEARCH_ORDER')
    	),
    )
);