<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
    IncludeTemplateLangFile(__FILE__);
    $wizTemplateId = COption::GetOptionString("main", "wizard_template_id", "eshop_vertical", SITE_ID);
    CUtil::InitJSCore();
    $curPage = $APPLICATION->GetCurPage(true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
    <meta name='wmail-verification' content='ff445a99a49ffa38957686b617ab060e' />
    <meta name='yandex-verification' content='5aca97f004d65975' />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width">
    <link rel="shortcut icon" type="image/x-icon" href="<?=SITE_TEMPLATE_PATH?>/favicon.ico" />
    <?if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") && !strpos($_SERVER['HTTP_USER_AGENT'], "MSIE 10.0")):?>

        <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/ie.css');?>
        <?endif?>


    <?$APPLICATION->ShowHead();?>




    <?$APPLICATION->SetAdditionalCSS('/bitrix/templates/'.SITE_TEMPLATE_ID.'/js/fancybox/jquery.fancybox-1.3.1.css');?>
    <?$APPLICATION->SetAdditionalCSS('http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic');?>
    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/bootstrap/css/bootstrap.min.css');?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/js/jquery-1.8.2.min.js');?>
    <?$APPLICATION->AddHeadScript('/bitrix/templates/'.SITE_TEMPLATE_ID.'/js/jquery.cookie.js');?>

    <?
        /*
        * Подключим зависимости для AJAX окон
        */
        $APPLICATION->SetAdditionalCSS('/bitrix/panel/main/popup.css');
        $APPLICATION->AddHeadScript('/bitrix/templates/'.SITE_TEMPLATE_ID.'/script.js');
        $APPLICATION->AddHeadScript('/bitrix/templates/'.SITE_TEMPLATE_ID.'/js/fancybox/jquery.fancybox-1.3.1.pack.js');
        $APPLICATION->AddHeadScript('/bitrix/components/linemedia.auto/search.results/templates/.default/js/tablesorter.js');
        $APPLICATION->AddHeadScript('/bitrix/components/linemedia.auto/search.results/templates/.default/script.js');
        $APPLICATION->AddHeadScript('/bitrix/components/linemedia.auto/search.results/templates/.default/menu-script.js');
    ?>


    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/bootstrap/js/bootstrap.min.js');?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/bootstrap/js/bootstrap-carousel.js');?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/bootstrap/js/bootstrap-fileupload.js');?>
    <?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/bootstrap/js/menu-script.js');?>    


    <?$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/bootstrap/css/bootstrap-fileupload.css');?>

    <?$APPLICATION->SetAdditionalCSS('/bitrix/templates/'.SITE_TEMPLATE_ID.'/styles_new.css');?>

    <title><?$APPLICATION->ShowTitle()?></title>

    <!--[if lt IE 7]>
    <style type="text/css">
    #compare {bottom:-1px; }
    div.catalog-admin-links { right: -1px; }
    div.catalog-item-card .item-desc-overlay {background-image:none;}
    </style>
    <![endif]-->

    <!--[if IE]>
    <style type="text/css">
    #fancybox-loading.fancybox-ie div    { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_loading.png', sizingMethod='scale'); }
    .fancybox-ie #fancybox-close        { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_close.png', sizingMethod='scale'); }
    .fancybox-ie #fancybox-title-over    { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_over.png', sizingMethod='scale'); zoom: 1; }
    .fancybox-ie #fancybox-title-left    { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_left.png', sizingMethod='scale'); }
    .fancybox-ie #fancybox-title-main    { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_main.png', sizingMethod='scale'); }
    .fancybox-ie #fancybox-title-right    { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_title_right.png', sizingMethod='scale'); }
    .fancybox-ie #fancybox-left-ico        { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_nav_left.png', sizingMethod='scale'); }
    .fancybox-ie #fancybox-right-ico    { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_nav_right.png', sizingMethod='scale'); }
    .fancybox-ie .fancy-bg { background: transparent !important; }
    .fancybox-ie #fancy-bg-n    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_n.png', sizingMethod='scale'); }
    .fancybox-ie #fancy-bg-ne    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_ne.png', sizingMethod='scale'); }
    .fancybox-ie #fancy-bg-e    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_e.png', sizingMethod='scale'); }
    .fancybox-ie #fancy-bg-se    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_se.png', sizingMethod='scale'); }
    .fancybox-ie #fancy-bg-s    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_s.png', sizingMethod='scale'); }
    .fancybox-ie #fancy-bg-sw    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_sw.png', sizingMethod='scale'); }
    .fancybox-ie #fancy-bg-w    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_w.png', sizingMethod='scale'); }
    .fancybox-ie #fancy-bg-nw    { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?=SITE_TEMPLATE_PATH?>/jquery/fancybox/fancy_shadow_nw.png', sizingMethod='scale'); }
    </style>
    <![endif]-->

    <!--script type="text/javascript">if (document.documentElement) { document.documentElement.id = "js" }</script-->

    <script>
        var _prum = [['id', '51f7dbf8abe53dbc2f000000'],
            ['mark', 'firstbyte', (new Date()).getTime()]];
        (function() {
            var s = document.getElementsByTagName('script')[0]
            , p = document.createElement('script');
            p.async = 'async';
            p.src = '//rum-static.pingdom.net/prum.min.js';
            s.parentNode.insertBefore(p, s);
        })();
    </script>


</head>
<body> 
<script>
    (function(w, d, s, h, id) {
        w.roistatProjectId = id; w.roistatHost = h;
        var p = d.location.protocol == "https:" ? "https://" : "http://";
        var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init";
        var js = d.createElement(s); js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
    })(window, document, 'script', 'cloud.roistat.com', '14840');
</script>
<div id="panel"><?if ($USER->isAdmin()) $APPLICATION->ShowPanel();?></div> 
<div class="wrapper">      
<!-- Header -->
<?if ($GLOBALS["APPLICATION"]->GetCurPage() == "/"):?>
    <style>
        /*.tecdoc_quick_search{
        display:none;
        }
        .table-container{
        display:none;
        }
        .tecdoc{
        display:none;    
        }*/
    </style>
    <?endif;?>
<?if ($GLOBALS["APPLICATION"]->GetCurPage() != "/"):?>
    <div class="type-page2">
        <?endif;?>

    <div class="row-fluid header_border">          
        <div class="container">                       
            <!-- Верхнее меню -->
        </div>

        <div class="container">              
            <div class="row">                  
                <div class="span3 auth">                     
                    <? $APPLICATION->IncludeComponent(
                            "bxmod:auth.dialog", 
                            ".default", 
                            array(
                                "SUCCESS_RELOAD_TIME" => "5",
                                "COMPONENT_TEMPLATE" => ".default"
                            ),
                            false
                        ); ?>
                    <?/*$APPLICATION->IncludeComponent(
                        "bitrix:system.auth.form",
                        "lm",
                        Array(
                        "REGISTER_URL" => SITE_DIR."login/",
                        "FORGOT_PASSWORD_URL" => "",
                        "PROFILE_URL" => SITE_DIR."personal/",
                        "SHOW_ERRORS" => "N"
                        )
                );*/?>                 </div>

                <div class="mob-nav"> </div>

                <div class="span6"> 
                    <div class="mob-menu"> 
                        <?$APPLICATION->IncludeComponent(
                            "bitrix:menu", 
                            "top_lm", 
                            array(
                                "ROOT_MENU_TYPE" => "new-top",
                                "MAX_LEVEL" => "1",
                                "CHILD_MENU_TYPE" => "left",
                                "USE_EXT" => "N",
                                "DELAY" => "N",
                                "ALLOW_MULTI_SELECT" => "N",
                                "MENU_CACHE_TYPE" => "N",
                                "MENU_CACHE_TIME" => "3600",
                                "MENU_CACHE_USE_GROUPS" => "Y",
                                "MENU_CACHE_GET_VARS" => array(
                                ),
                                "COMPONENT_TEMPLATE" => "top_lm"
                            ),
                            false
                        );?> </div>
                    <h3 style="text-align: center; font-weight: normal !important;"> 
                        <br />
                    </h3>

                    <h3 style="text-align: center; font-weight: normal !important;"><img src="/upload/medialibrary/7bf/7bfb408d1422a1f9c4af04a05d442398.png" title="dostavka.png" border="0" alt="dostavka.png" width="205" height="125"  /></h3>

                    <div>
                        <br />
                    </div>

                </div>          
                <!-- Телефон -->


                <div class="span3 phone_top"> 
                    <h3><?$APPLICATION->IncludeComponent(
                                "bitrix:main.include",
                                ".default",
                                Array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => SITE_TEMPLATE_PATH."/include/telephone.php",
                                    "EDIT_TEMPLATE" => ""
                                )
                            );?>
                        <div class="mobile-items-phone2">
                            <a href="tel:+79264150010"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/watsapp.png" height="32" title="i.jpg"></a>
                            <a href="tel:+79264150010"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/sms.png" height="32" title="i.jpg"></a>
                            <a href="tel:+79264150010"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/viber.png" height="32" title="i.jpg"></a>
                            <a href="skype:avtox24.ru?chat"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/skype.png" height="32" title="i.jpg"></a>
                            <a href="mailto:info@avtox24.ru"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/email.png" height="32" title="i.jpg"></a>


                        </div>
                    </h3>
                </div>
            </div>

            <!-- Лого, Поиск -->

            <div class="row logo-rel">                  
                <div class="span3 logo">  
                    <div class="dropdown-main-menu">                 
                        <a href="/" ><?$APPLICATION->IncludeComponent(
                                "bitrix:main.include",
                                "",
                                Array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => SITE_TEMPLATE_PATH."/include/logo.php"
                                )
                            );?></a>                 
                        <div class="dropdown-main-menu-line"></div>

                        <div class="dropdown-main-menu-button">
                            <span><img src="<?=SITE_TEMPLATE_PATH?>/images/dropdown-menu-icon.png"/></span>
                            Меню
                        </div>

                        <div class="dropdown-main-menu-list">
                            <div class="dropdown-main-menu-line"></div>
                            <h3>Личный кабинет</h3>
                            <?$APPLICATION->IncludeComponent(
                                    "bitrix:menu",
                                    "bottom_lm",
                                    Array(
                                        "ROOT_MENU_TYPE" => "left",
                                        "MENU_CACHE_TYPE" => "N",
                                        "MENU_CACHE_TIME" => "3600",
                                        "MENU_CACHE_USE_GROUPS" => "Y",
                                        "MENU_CACHE_GET_VARS" => array(),
                                        "MAX_LEVEL" => "1",
                                        "CHILD_MENU_TYPE" => "left",
                                        "USE_EXT" => "N",
                                        "DELAY" => "N",
                                        "ALLOW_MULTI_SELECT" => "N"
                                    )
                                );?>

                            <div class="dropdown-main-menu-line"></div>
                            <h3>Каталог товаров</h3>
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
                </div>



                <div class="span9 search_block">
                    <div class="main-search-tabs-titles">
                        <ul>
                            <li class="active" data-search-tab-index="1">Номер детали</li>
                            <li data-search-tab-index="2">VIN-номер</li>
                            <li data-search-tab-index="3">Марка</li>
                        </ul>
                    </div>    

                    <div class="row-fluid">                          
                        <div class="span9 search_form">                              
                            <div class="row-fluid">  

                                <div class="span7 search-tab-content active" data-search-tab="1">                                     
                                    <?$APPLICATION->IncludeComponent(
                                            "bitrix:search.title", 
                                            "search_lm", 
                                            array(
                                                "NUM_CATEGORIES" => "1",
                                                "TOP_COUNT" => "5",
                                                "ORDER" => "rank",
                                                "USE_LANGUAGE_GUESS" => "Y",
                                                "CHECK_DATES" => "Y",
                                                "SHOW_OTHERS" => "N",
                                                "PAGE" => SITE_DIR."auto/search/",
                                                "CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
                                                "CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS"),
                                                "CATEGORY_0" => array(
                                                    0 => "iblock_linemedia_auto",
                                                    1 => "iblock_linemedia_autotecdoc",
                                                ),
                                                "SHOW_INPUT" => "Y",
                                                "INPUT_ID" => "title-search-input",
                                                "CONTAINER_ID" => "search",
                                                "COMPONENT_TEMPLATE" => "search_lm",
                                                "CATEGORY_0_iblock_linemedia_auto" => array(
                                                    0 => "23",
                                                ),
                                                "CATEGORY_0_iblock_linemedia_autotecdoc" => array(
                                                    0 => "all",
                                                ),
                                                "PRICE_CODE" => "",
                                                "PRICE_VAT_INCLUDE" => "Y",
                                                "PREVIEW_TRUNCATE_LEN" => "",
                                                "SHOW_PREVIEW" => "Y",
                                                "CONVERT_CURRENCY" => "N"
                                            ),
                                            false
                                        );?>    
                                </div>
                                
                                <div class="span7 search-tab-content" data-search-tab="2">                                     
                                    <?$APPLICATION->IncludeComponent(
                                            "bitrix:search.title", 
                                            "search_lm", 
                                            array(
                                                "NUM_CATEGORIES" => "1",
                                                "TOP_COUNT" => "5",
                                                "ORDER" => "rank",
                                                "USE_LANGUAGE_GUESS" => "Y",
                                                "CHECK_DATES" => "Y",
                                                "SHOW_OTHERS" => "N",
                                                "PAGE" => SITE_DIR."auto/search/",
                                                "CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
                                                "CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS"),
                                                "CATEGORY_0" => array(
                                                    0 => "iblock_linemedia_auto",
                                                    1 => "iblock_linemedia_autotecdoc",
                                                ),
                                                "SHOW_INPUT" => "Y",
                                                "INPUT_ID" => "title-search-input",
                                                "CONTAINER_ID" => "search",
                                                "COMPONENT_TEMPLATE" => "search_lm",
                                                "CATEGORY_0_iblock_linemedia_auto" => array(
                                                    0 => "23",
                                                ),
                                                "CATEGORY_0_iblock_linemedia_autotecdoc" => array(
                                                    0 => "all",
                                                ),
                                                "PRICE_CODE" => "",
                                                "PRICE_VAT_INCLUDE" => "Y",
                                                "PREVIEW_TRUNCATE_LEN" => "",
                                                "SHOW_PREVIEW" => "Y",
                                                "CONVERT_CURRENCY" => "N"
                                            ),
                                            false
                                        );?>    
                                </div>
                                
                                <div class="span7 search-tab-content" data-search-tab="3">                                     
                                    <?$APPLICATION->IncludeComponent(
                                            "bitrix:search.title", 
                                            "search_lm", 
                                            array(
                                                "NUM_CATEGORIES" => "1",
                                                "TOP_COUNT" => "5",
                                                "ORDER" => "rank",
                                                "USE_LANGUAGE_GUESS" => "Y",
                                                "CHECK_DATES" => "Y",
                                                "SHOW_OTHERS" => "N",
                                                "PAGE" => SITE_DIR."auto/search/",
                                                "CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
                                                "CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS"),
                                                "CATEGORY_0" => array(
                                                    0 => "iblock_linemedia_auto",
                                                    1 => "iblock_linemedia_autotecdoc",
                                                ),
                                                "SHOW_INPUT" => "Y",
                                                "INPUT_ID" => "title-search-input",
                                                "CONTAINER_ID" => "search",
                                                "COMPONENT_TEMPLATE" => "search_lm",
                                                "CATEGORY_0_iblock_linemedia_auto" => array(
                                                    0 => "23",
                                                ),
                                                "CATEGORY_0_iblock_linemedia_autotecdoc" => array(
                                                    0 => "all",
                                                ),
                                                "PRICE_CODE" => "",
                                                "PRICE_VAT_INCLUDE" => "Y",
                                                "PREVIEW_TRUNCATE_LEN" => "",
                                                "SHOW_PREVIEW" => "Y",
                                                "CONVERT_CURRENCY" => "N"
                                            ),
                                            false
                                        );?>    
                                </div>


                            </div>
                        </div>

                        <div class="span3">                             <?$APPLICATION->IncludeComponent(
                                "bitrix:sale.basket.basket", 
                                "lm_header", 
                                array(
                                    "COLUMNS_LIST" => array(
                                        0 => "NAME",
                                        1 => "DISCOUNT",
                                        2 => "WEIGHT",
                                        3 => "DELETE",
                                        4 => "DELAY",
                                        5 => "TYPE",
                                        6 => "PRICE",
                                        7 => "QUANTITY",
                                    ),
                                    "PATH_TO_ORDER" => "/auto/order/",
                                    "HIDE_COUPON" => "N",
                                    "QUANTITY_FLOAT" => "N",
                                    "PRICE_VAT_SHOW_VALUE" => "N",
                                    "COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
                                    "USE_PREPAYMENT" => "N",
                                    "SET_TITLE" => "N",
                                    "COMPONENT_TEMPLATE" => "lm_header",
                                    "ACTION_VARIABLE" => "action1"
                                ),
                                false
                            );?>                         </div>
                    </div>
                </div>
            </div>

            <!-- Главное меню -->

            <div class="row main-menu mobile-type">                  
                <div class="span12">                     <?$APPLICATION->IncludeComponent(
                        "bitrix:menu", 
                        "main_lm", 
                        array(
                            "ROOT_MENU_TYPE" => "main",
                            "MENU_CACHE_TYPE" => "N",
                            "MENU_CACHE_TIME" => "3600",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => array(
                            ),
                            "MAX_LEVEL" => "1",
                            "CHILD_MENU_TYPE" => "left",
                            "USE_EXT" => "Y",
                            "DELAY" => "N",
                            "ALLOW_MULTI_SELECT" => "N",
                            "COMPONENT_TEMPLATE" => "main_lm"
                        ),
                        false
                    );?>                 </div>
            </div>
        </div>
    </div>

    <?if ($GLOBALS["APPLICATION"]->GetCurPage() != "/"):?>
    </div>
    <?endif;?>
<?//if ($curPage !== SITE_DIR."index.php"):?>      
<!-- Content -->

<div class="row-fluid content_top"></div>

<div class="row-fluid content_row">          
<div class="container">              
<div class="row">                  

<div class="span9 content"> 

<div class="breadcrumbs"> <?$APPLICATION->IncludeComponent(
    "bitrix:breadcrumb",
    ".default",
    Array(
        "START_FROM" => "1",
        "PATH" => "",
        "SITE_ID" => "s1"
    )
        );?>
     </div>
                              
          <h1><?$APPLICATION->ShowTitle(false)?></h1>
            <?//endif?>