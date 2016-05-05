<?php

/**
 * Linemedia Autoportal
 * Autotecdoc module
 * LinemediaAutoTecDocTecDocRights
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);
 
/**
 * mysql implementation of class LinemediaAutoTecDocTecDocRights
 */
class LinemediaAutoTecDocTecDocRights extends LinemediaAutoTecDocTecDocRightsAll
{
    /**
     * db title
     * @var string $dbname
     */
    protected static $dbname = 'b_lm_tecdoc_rights';
    
    
    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    
    /**
     * getting list of fields
     */
    public function getList()
    {
        
    }
}