<div class="row left_menu">
    <div class="span12">
        <h5>Личный кабинет</h5>
        <?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
                "AREA_FILE_SHOW" => "sect",
                "AREA_FILE_SUFFIX" => "left_menu",
                "AREA_FILE_RECURSIVE" => "Y",
                "EDIT_TEMPLATE" => ""
                ),
                false
            );?>
        <?if( in_array(11,CUser::GetUserGroup(CUser::GetID()))||in_array(12,CUser::GetUserGroup(CUser::GetID()))||in_array(1,CUser::GetUserGroup(CUser::GetID()))||in_array(5,CUser::GetUserGroup(CUser::GetID())) )
            {?>
            <ul class="nav obrabotka"><li class=" lvl1">
                    <span style="position:relative; display: block;"><a href="/obrabotka-vin/">Обработка VIN</a></span>
                </li></ul>
            <?} ?>
    </div>
</div>
<div class="row">
    <div class="span12">
        <h5 style="cursor:pointer" onclick="location.href='/catalog/'">Каталог товаров</h5>
        <?$APPLICATION->IncludeComponent("bitrix:catalog.section.list", "side_bar", array(
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
                "ADD_SECTIONS_CHAIN" => "N"
                ),
                false
            );?>
    </div>
</div>