<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'template.php';

class GuayaquilWizard extends GuayaquilTemplate
{
	var $wizard = NULL;
	var $catalog = NULL;

	function __construct(IGuayaquilExtender $extender)
	{
		parent::__construct($extender);
	}

	function Draw($catalog, $wizard)
	{
		$this->wizard = $wizard;
		$this->catalog = $catalog;

        
		$html = '<div class="DrawHeader">';
		/*
        $html .= '<script type="text/javascript">';
		$html .= 'function openWizard(ssd) {';
        $html .= 'var url = \''.$this->FormatLink('wizard', null, $catalog).'\'.replace(\'\\$ssd\\$\', ssd);';
        $html .= 'window.location = url;';
        $html .= '}';
		$html .= '</script>';
        */
		$html .= '<form name="findByParameterIdentifocation" id="findByParameterIdentifocation">';
		$html .= '<table border="0" width="100%">';
        
        /*
        $html = '<div class="DrawHeader">';
        $html .= '<script type="text/javascript">';
        $html .= 'function openWizard(ssd) {';
        $html .= 'var url = \''.$this->FormatLink('wizard', null, $catalog).'\'.replace(\'\\$ssd\\$\', ssd);';
        $html .= '$("#wizard-wrap").load("url #wizard-wrap > *");';
        $html .= 'history.replaceState(3, "", url);';
        $html .= '}';
        $html .= '</script>';
        $html .= '<form name="findByParameterIdentifocation" id="findByParameterIdentifocation">';
        $html .= '<table border="0" width="100%">'; 
        */
		
		$html .= $this->DrawHeader();

		foreach($wizard->row as $condition)
			$html .= $this->DrawConditionRow($catalog, $condition); 		
		
		$html .= '</table>';
		$html .= '</form>';

		if ($wizard->row['allowlistvehicles'] == 'true')
			$html .= $this->DrawVehiclesListLink($catalog, $wizard);

		$html .= '</div>';

		return $html;
	}

	function DrawHeader()
	{
		return '';
	}

	function DrawConditionRow($catalog, $condition)
	{
        $condition_name = strtolower("wizard_".$this->DrawConditionName($catalog, $condition));
        $condition_name_localized = CommonExtender::LocalizeString($condition_name);
		$html = '<tr width="60%"'.($condition['automatic'] == 'false' ? ' class="guayaquil_SelectedRow"' : '').'>';
		$html .= '<td class="wizardConditionName">'.$condition_name_localized.'</td></tr>';

		$html .= '<tr><td>';
		if ($condition['determined'] == 'false') {
			$html .= $this->DrawSelector($catalog, $condition);
        } else if ($condition['automatic'] == 'true') {
            $html .= $this->DrawAutomaticSelector($catalog, $condition);
        } else {
            $html .= $this->DrawManualSelector($catalog, $condition);
        }
		
		$html .= '</td></tr>';

		return $html;
	}

	function DrawConditionName($catalog, $condition)
	{
		return $condition['name'];
	}

	function DrawSelector($catalog, $condition)
	{
        $html = '<div class="wizardSelectWrapper">';
		$html .= '<select style="width:250px" name="Select'.$condition['type'].'" onChange="openWizard(this.options[this.selectedIndex].value); return false;">';  

		$html .= '<option value="null">&nbsp;</option>';

		foreach($condition->options->row as $row) {
			$html .= $this->DrawSelectorOption($row);
		}

		$html .= '</select>';
        $html .= '<div class="selectOpenButton">&#9662;</div>';
        $html .= '</div>';

		return $html;
	}

	function DrawAutomaticSelector($catalog, $condition)
	{
        $html = '<div class="wizardSelectWrapper">';
		$html .= '<select disabled style="width:250px" name="Select'.$condition['type'].'">';
		$html .= $this->DrawDisabledSelectorOption($condition);
		$html .= '</select>';
        $html .= '<div class="selectOpenButton">&#9662;</div>';
        $html .= '</div>';

		return $html;
	}

	function DrawManualSelector($catalog, $condition)
	{
        $html = '<div class="wizardSelectWrapper">';
		$html = '<select disabled style="width:250px" name="Select'.$condition['type'].'">';
		$html .= $this->DrawDisabledSelectorOption($condition);
		$html .= '</select>';
        $html .= '<div class="selectOpenButton">&#9662;</div>';
        $html .= '</div>';
        $removeFile = $this->Convert2uri(__DIR__ . DIRECTORY_SEPARATOR . 'images/remove.png');
		$html .= '<a class="remove_param" href="'.str_replace('$ssd$', $condition['ssd'], $this->FormatLink('wizard', null, $catalog)).'"><img src="'.$removeFile.'"></a>';

		return $html;
	}

	function DrawSelectorOption($row)
	{
		return '<option value="'.$row['key'].'">'.$row['value'].'</option>';
	}

	function DrawDisabledSelectorOption($condition)
	{
		return '<option disabled selected value="null">'.$condition['value'].'</option>';
	}

	function DrawVehiclesListLink($catalog, $wizard)
	{
		$html = '<a class="gWizardVehicleLink" href="'.$this->FormatLink('vehicles', $wizard, $catalog).'">';
		$html .= $this->GetLocalizedString('List vehicles');
		$html .= '</a>';

		return $html;
	}
}

?>