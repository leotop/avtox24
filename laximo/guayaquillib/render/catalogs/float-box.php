<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'template.php';

class GuayaquilCatalogsList extends GuayaquilTemplate
{
	var $iconsFolder = 'images/';
	var $catalogs = NULL;

	function __construct(IGuayaquilExtender $extender)
	{
		parent::__construct($extender);

		$this->iconsFolder = $this->Convert2uri(dirname(__FILE__).DIRECTORY_SEPARATOR.$this->iconsFolder).'/';
	}

	function Draw($catalogs)
	{
        $html = '';
		foreach ($catalogs->row as $row)
		{ 
			$link = $this->FormatLink('catalog', $row, (string)$row->code);
			$html .= $this->DrawItem($row, $link);
		}

		return $html;
	}

	function DrawItem($catalog, $link)
	{
		$html = '<div class="g_catalog_float_box" onclick="window.location=\''.$link.'\'">';
		$html .= '<div class="g_catalog_float_icon"><a class="guayaquil_tablecatalog" href="'.$link.'"><img border="0" width="40" height="40" src="'.$this->iconsFolder.strtolower($catalog['icon']).'"></a></div>';
		$html .= '<div class="g_catalog_float_name"><a class="guayaquil_tablecatalog" href="'.$link.'">'.$catalog['name'].'</a></div>';
		$html .= '</div>';
		return $html;
	}
}
