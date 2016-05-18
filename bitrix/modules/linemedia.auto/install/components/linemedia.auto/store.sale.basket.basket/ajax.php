<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

if (!CModule::IncludeModule('sale')) {
    ShowError(GetMessage('SALE_MODULE_ERROR'));

    return;
}

if (!CModule::IncludeModule('linemedia.auto')) {
    ShowError(GetMessage('LM_AUTO_MAIN_MODULE_ERROR'));

    return;
}

if (!check_bitrix_sessid()) {
    ShowError(GetMessage('LM_AUTO_ERROR_SESSID'));

    return;
}

global $USER;

$arResult = array();

if (!empty($_REQUEST) && $_REQUEST['set_vin_codes'] == 'Y') {
    $post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

    $basket_ids = $post_data['basket_ids'];
    $vin_codes = $post_data['vin_codes'];
    $modification_ids = $post_data['modification_ids'];

    /*
     * Обновление корзин: добавляем значения для свойства VIN
     */
    $basket = new LinemediaAutoBasket();

    foreach (array_combine($basket_ids, $vin_codes) as $basket_id => $vin_code) {
        if ($basket_id > 0 && $vin_code) {
            $arBasket = $basket->getData($basket_id);

            /*
             * Проверяем владельца корзины
             */
            if ($arBasket['FUSER_ID'] == CSaleBasket::GetBasketUserID()) {
                $arProps = array();

                $arProps[] = array(
                    "NAME"  => GetMessage('LM_AUTO_MAIN_BASKET_PROPERTY_NEED_VIN'),
                    "CODE"  => "need_vin",
                    "VALUE" => $vin_code
                );

                $props = $basket->setProperty($basket_id, $arProps);
            }
        }
    }

    /*
     * Добавляем VIN в гараж, если его там нет
     */
    if (CModule::IncludeModule('linemedia.autogarage') && $USER->IsAuthorized()) {
        $garage_iblock_id = COption::GetOptionInt('linemedia.autogarage', 'LM_AUTO_IBLOCK_GARAGE');
        $garage = new LinemediaAutoGarage($garage_iblock_id);

        /*
         * Не нужно проверяем есть ли там наш авто, т.к. он туда попадает автоматом при выборе машины
         */
        $user_cars = $garage->getUserList($USER->GetID());

        foreach (array_combine($modification_ids, $vin_codes) as $modification_id => $vin_code) {
            /*
             * Выбираем нашу машину по modification_id, т.к. это поле уникально
             */
            $car_iblock_id = 0;

            foreach ($user_cars as $user_car) {
                if ($user_car['PROPERTY_MODIFICATION_ID_VALUE'] == $modification_id) {
                    /*
                     * Если вин уже есть, то переходим к след. модификации
                     */
                    if ($user_car['PROPERTY_VIN_VALUE']) {
                        continue 2;
                    } else {
                        $car_iblock_id = $user_car['ID'];
                        break;
                    }
                }
            }

            if ($car_iblock_id) {
                CIBlockElement::SetPropertyValuesEx($car_iblock_id, $garage_iblock_id, array('vin' => $vin_code));
            }
        }
    }

    die();
}

if (!empty($_REQUEST) && isset($_REQUEST['BASKET_ID'])) {
    $basket_id = (int)$_REQUEST['BASKET_ID'];
    $quantity = (int)$_REQUEST['QUANTITY'];

    /*
     * Обновление корзины
     */
    if ($basket_id > 0 && $quantity > 0) {
        $basket = new CSaleBasket();
        $arBasket = $basket->GetByID($basket_id);

        if (!($arOrder = CSaleOrder::GetByID($arBasket['ORDER_ID']))) {
            if ($arOrder['USER_ID'] != $USER->GetID()) {
                ShowError(GetMessage('LM_AUTO_ERROR_BASKET_ID'));

                return;
            }
            $basket->Update($basket_id, array('QUANTITY' => $quantity));
        }
    }

    /*
     * Установим параметры.
     */
    $arParams = (array)$_REQUEST['PARAMS'];
    $arParams['AJAX_MODE'] = 'Y';

    /*
     * Очистим весь вывод.
     */
    ob_end_clean();

    /*
     * Рассчитаем общую стоимость.
     */
    $result = $APPLICATION->IncludeComponent(
        'linemedia.auto:store.sale.basket.basket',
        '',
        $arParams,
        false
    );

    $arResult['TOTAL_PRICE'] = $result['allNOVATSum_FORMATED'];
}

echo json_encode($arResult);
exit();
