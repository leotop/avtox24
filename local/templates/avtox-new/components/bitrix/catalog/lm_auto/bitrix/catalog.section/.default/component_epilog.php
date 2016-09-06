<?//$APPLICATION->AddHeadScript('/bitrix/templates/'.SITE_TEMPLATE_ID.'/js/fancybox/jquery.fancybox-1.3.1.pack.js');?>
<?
$APPLICATION->SetAdditionalCSS('/bitrix/templates/'.SITE_TEMPLATE_ID.'/components/bitrix/catalog/lm_auto/bitrix/catalog.element/.default/style.css');
$APPLICATION->SetAdditionalCSS('/bitrix/components/linemedia.auto/search.results/templates/.default/style.css');
?>


<?



$res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => IntVal(3), "SECTION_ID" => IntVal($arResult["ID"])), false, Array(), Array("ID", "NAME", "IBLOCK_SECTION_ID"));

if($res->GetNextElement() == NULL){?>
	<script type="text/javascript">
		$("form.smartfilter").css("display", "none");
		$("div.sort").css("display", "none");
	</script>
<?}?>