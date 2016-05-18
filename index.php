<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("tags", "оригинал, неоригинал, запчасти, доставка по Росиии");
$APPLICATION->SetPageProperty("keywords_inner", "Оригинальные и неоригинальные  запчасти");
$APPLICATION->SetPageProperty("title", "Автозапчасти для иномарок");
$APPLICATION->SetTitle("AvtoX24.ru");
?>

<?$APPLICATION->IncludeComponent("linemedia.autotecdoc:tecdoc.catalog2", "ajax1", Array(
	"DETAIL_URL" => "/auto/part-detail/#ARTICLE_ID#/#ARTICLE_LINK_ID#/",	// Путь к детальной странице (доступны шаблоны #ARTICLE_ID# и #ARTICLE_LINK_ID#)
		"SEARCH_ARTICLE_URL" => "",	// Путь к поиску запчастей (доступны шаблоны #ARTICLE# и #BRAND_TITLE#)
		"COLUMNS_COUNT" => "4",	// Количество столбцов для вывода
		"ADD_SECTIONS_CHAIN" => "Y",	// Добавлять в цепочку навигации
		"SHOW_ORIGINAL_ITEMS" => "Y",	// Выводить оригинальные номера
		"GROUP_MODELS" => "Y",	// Группировать модификации автомобилей
		"TECDOC_BRAND_TYPES" => array(	// Типы брендов TecDoc
			0 => "1",
			1 => "2",
			2 => "3",
		),
		"MODIFICATIONS_SET" => "default",	// Применять набор модификаций
		"HIDE_UNAVAILABLE" => "Y",	// Скрывать запчасти которых нет в базе
		"INCLUDE_PARTS_IMAGES" => "Y",	// Отображать картинки запчастей в таблице
		"ANTI_BOTS" => "Y",	// Включить защиту от парсинга незарегистрированными пользователями (рекомендуется)
		"MANUAL_TECDOC_GROUPS" => "",	// Выбор групп для показа
		"SWIFT_FILTER" => "N",	// Скрывать запчасти
		"CONTEMPORARY_YEAR" => "1999",	// Установка фильтра для кнопки современные (в годах)
		"SEF_MODE" => "Y",	// Включить поддержку ЧПУ
		"SEF_FOLDER" => "/auto/tecdoc/",	// Каталог ЧПУ (относительно корня сайта)
		"COMPONENT_TEMPLATE" => "ajax",
		"PATH_TO_TECDOC" => "/auto/tecdoc/",	// Путь к TecDoc
	),
	false
);?>

 <?/*$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_slider.php"
	),
false,
Array(
	'ACTIVE_COMPONENT' => 'Y'
)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_brands.php"
	),
false,
Array(
	'ACTIVE_COMPONENT' => 'N'
)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_hits.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_catalogs.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_content_main.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_new_parts.php"
	)
);?> 	<?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => SITE_TEMPLATE_PATH."/include/row_news.php"
	)
);*/?> 	 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>