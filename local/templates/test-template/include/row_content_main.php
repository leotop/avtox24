<!-- Статьи -->
<div class="row-fluid content_main_top"></div>
<div class="row-fluid content_main">
	<div class="container">
		<div class="row">
			<div class="span6">
				<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_TEMPLATE_PATH . "/include/warranty.php"), false);?>
			</div>
			<div class="span6">
				<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_TEMPLATE_PATH . "/include/availability.php"), false);?>
			</div>
		</div>
	</div>
</div>
<div class="row-fluid content_main_bottom"></div>