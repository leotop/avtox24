<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Avtox24");
$APPLICATION->SetTitle("Запрос по VIN");
?><?$APPLICATION->IncludeComponent("linemedia.auto:personal.request.vin.iblock", "template1", Array(
    "SEF_MODE" => "Y",    // Включить поддержку ЧПУ
        "TICKETS_PER_PAGE" => "10",    // Количество обращений на одной странице
        "TICKET_SORT_ORDER" => "desc",    // Направление для сортировки запросов
        "SET_PAGE_TITLE" => "Y",    // Устанавливать заголовок страницы
        "SEF_FOLDER" => "/auto/vin/",    // Каталог ЧПУ (относительно корня сайта)
        "SEF_URL_TEMPLATES" => array(
            "list" => "",
            "edit" => "#ID#/",
        ),
        "VARIABLE_ALIASES" => array(
            "edit" => "",
            "list" => "",
        )
    ),
    false
);?> 
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>