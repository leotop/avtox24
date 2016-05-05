<?php
$params = $arParams;
$params['SEF_FOLDER'] = str_replace('//','/', $arParams['SEF_FOLDER'].'/'.$arResult['BRAND'].'/');
$APPLICATION->IncludeComponent('linemedia.auto:original.catalog.'.$arResult['BRAND'],
                                '',
                                $params,
                                $component);