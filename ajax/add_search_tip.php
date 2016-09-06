<?require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");?>
<?
CModule::IncludeModule('iblock'); 
global $USER;
if ($USER->IsAuthorized()) {
	$tips = CIBlockElement::GetList(
		Array(),
		Array(
			"IBLOCK_ID"       => TIPS_IBLOCK,
			"NAME"            => $_POST['query'],
			"CREATED_USER_ID" => $USER->GetID()
		),
		false,
		false,
		array("ID")
	);
	if (!$tips->SelectedRowsCount()) {
		checkTipsLimit($_POST['type']);
		
		$element = new CIBlockElement;
		
		$properties = array(
			'328' => $_POST['type']
		);
		
		$fields = Array(
		  "MODIFIED_BY"     => $USER->GetID(),
		  "IBLOCK_ID"       => TIPS_IBLOCK,
		  "NAME"            => $_POST['query'],
		  "ACTIVE"          => "Y",
		  "XML_ID"          => "st" . microtime(),
		  "PROPERTY_VALUES" => $properties,
		);
		
		$element->Add($fields);
	}
}
?>