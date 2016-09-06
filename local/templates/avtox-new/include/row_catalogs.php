<!-- Каталоги -->
<div class="row-fluid catalogs">
	<div class="container">
		<div class="row">
			<div class="span12">
				<?$APPLICATION->IncludeComponent("bitrix:catalog.section.list", "main_lm", array(
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "3",
	"SECTION_ID" => $_REQUEST["SECTION_ID"],
	"SECTION_CODE" => "",
	"COUNT_ELEMENTS" => "N",
	"TOP_DEPTH" => "2",
	"SECTION_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"SECTION_USER_FIELDS" => array(
		0 => "",
		1 => "",
	),
	"SECTION_URL" => "",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_GROUPS" => "Y",
	"ADD_SECTIONS_CHAIN" => "Y"
	),
	false
);?>
			</div>
		</div>
	</div>
</div>