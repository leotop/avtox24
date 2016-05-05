<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $USER;

$allCurrency = $USER->GetParam('CURRENCY');//CSaleLang::GetLangCurrency(SITE_ID);
if(empty($allCurrency)) {
    $allCurrency = CSaleLang::GetLangCurrency(SITE_ID);
}

//$APPLICATION->AddHeadScript($this->getFolder() . '/js/jquery-ui-1.10.0.custom.min.js');
if(strlen($_SESSION['MSG_RETURN_ACTION']) > 0) {
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            alert(<?=$_SESSION['MSG_RETURN_ACTION']?>);
        });
    </script>
    <?
    unset($_SESSION['MSG_RETURN_ACTION']);
}
if(strlen($_SESSION['MSG_CANCEL_ACTION']) > 0) {
    ?>
    <script type="text/javascript">
        $(document).ready(function() {
            alert(<?=$_SESSION['MSG_CANCEL_ACTION']?>);
        });
    </script>
    <?
    unset($_SESSION['MSG_CANCEL_ACTION']);
}
?>
<script type="text/javascript" src="<?= $this->getFolder() ?>/js/datatable.js"></script>
<script type="text/javascript" src="<?= $this->getFolder() ?>/js/tablefilter.js"></script>
<script type="text/javascript" src="<?= $this->getFolder() ?>/js/main.js"></script>

<? function showBasketRow($basket, $arParams, $display = 'auto', $orderID, $component_folder = '') {	
	global $USER;
    $allCurrency = $USER->GetParam('CURRENCY');//CSaleLang::GetLangCurrency(SITE_ID);
    if(empty($allCurrency)) {
        $allCurrency = CSaleLang::GetLangCurrency(SITE_ID);
    }
	$directorGroup = COption::GetOptionInt('linemedia.autobranches', 'LM_AUTO_BRANCHES_USER_GROUP_DIRECTOR');
?>
    <tr
        class="mleft <?= ($key == 'W') ? ('') : ('bag1') ?> tr"
        align="center"<? if ($arParams["USE_STATUS_COLOR"] === 'Y' && isset($arResult['STATUS_COLORS'][$sStatusItem])) { ?>
        bgcolor="<?= $arResult['STATUS_COLORS'][$sStatusItem] ?>"<? } ?>
        rel="order-<?= $basket['ORDER_ID'] ?>"
        style="display: <?= $display ?>;"
    >
        <td class="tda order_date">
            <?= $basket['DATE_INSERT'] ?>
        </td>
        <td class="tdb order_id">
            <?= $basket['ORDER_ID'] ?>
        </td>
        <td class="tdb order_brand">
            <?= $basket['PROPS']['brand_title']['VALUE'] ?>
        </td>
        <td class="tdb order_art">
            <?= $basket['PROPS']['article']['VALUE'] ?>
        </td>
        <td class="tdb order_name">
            <span id="OrItemTitleID_<?= $basket['ID'] ?>"><?= $basket['NAME'] ?></span>
        </td>
        <td class="tdb order_quantity">
            <span id="OrItemQuantityID_<?= $basket['ID'] ?>"><?= $basket['QUANTITY'] ?></span>
        </td>
        <td class="tdb order_currency">
            <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['PRICE'], $basket['CURRENCY'], $allCurrency), $allCurrency) ?>
            <br />
        </td>
        <td class="tdb order_sum">
            <? $sum = (float) $basket['QUANTITY'] * $basket['PRICE']; ?>
            <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($sum, $basket['CURRENCY'], $allCurrency), $allCurrency) ?>
            <br />
        </td>
        <td class="tdb order_delivery_sum">
            <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['ORDER']['PRICE_DELIVERY'], $basket['ORDER']['CURRENCY'], $allCurrency), $allCurrency) ?>
        </td>
        <td class="tdb  order_status">
            <? if ($basket['PROPS']['payed']['VALUE'] == 'Y') { ?>

                <a href="#" id="payed_link_<?= $basket['ID'] ?>" class="showpaylink"><?= GetMessage('SPOL_YES') ?></a>
                <div style="display: none;" class="showpaydialog" id="dialog_payed_link_<?= $basket['ID'] ?>">
                    <h4 style="margin: 0 0 5px 0;">
                        <?= str_replace('#ID#', $basket['ORDER']['ID'], GetMessage('SPOL_POPUP_TITLE')) ?>.
                    </h4>
                    <table>
                        <tr>
                            <td align="right"><?= GetMessage('SPOL_POPUP_ORDER_DATE') ?>:</td>
                            <td style="font-weight: bold;">
                                <?= $basket['ORDER']['DATE_INSERT_FORMAT'] ?>
                            </td>
                        </tr>
                        <? if (isset($basket['PROPERTIES']['DATE_PAYED']['VALUE'])) { ?>
                            <tr>
                                <td align="right"><?= GetMessage('SPOL_POPUP_PAYED_DATE') ?>:</td>
                                <td style="font-weight: bold;">
                                    <?= $basket['PROPERTIES']['DATE_PAYED']['VALUE'] ?>
                                </td>
                            </tr>
                        <? } ?>
                        <tr>
                            <td align="right"><?= GetMessage('SPOL_POPUP_AMOUNT') ?>:</td>
                            <td style="font-weight: bold;">
                                <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['PRICE'] * $basket['QUANTITY'], $basket['CURRENCY'], $allCurrency), $allCurrency) ?>
                            </td>
                        </tr>
                        <? if (isset($basket['ORDER']['PAY_SYSTEM']['NAME']) && !empty($basket['ORDER']['PAY_SYSTEM']['NAME'])) { ?>
                            <tr>
                                <td align="right"><?= GetMessage('SPOL_POPUP_PAYSYSTEM') ?>:</td>
                                <td style="font-weight: bold;">
                                    <?= $basket['ORDER']['PAY_SYSTEM']['NAME'] ?>
                                </td>
                            </tr>
                        <? } ?>
                        <? if (isset($basket['ORDER']['DELIVERY']['NAME']) && !empty($basket['ORDER']['DELIVERY']['NAME'])) { ?>
                            <tr>
                                <td align="right"><?= GetMessage('SPOL_POPUP_DELIVERY') ?>:</td>
                                <td style="font-weight: bold;">
                                    <?= $basket['ORDER']['DELIVERY']['NAME'] ?>
                                </td>
                            </tr>
                        <? } ?>
                    </table>
                </div>

            <? } else { ?>
                <? $ORDER_ID = $basket['ORDER_ID'] ?>
                <? if ($basket['ORDER']['PAY_SYSTEM']['PSA_NEW_WINDOW'] == 'Y') { ?>
                    <?= GetMessage('SPOL_NO') ?>
                    <? if (
                        !isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) ||
                        (isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) && $basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE'] === 'Y')
                    ) {
                    ?>
                        <br />
                        (<a href="<?= $basket['ORDER']['PAY_SYSTEM']['PSA_ACTION_FILE'] ?>" class="paylink2" target="_blank"><?= GetMessage('SPOL_RECEIPT') ?></a>)
                    <? } ?>
                <? } else { ?>
                        <?= GetMessage('SPOL_NO') ?>
                    <? if(!LinemediaAutoBasket::isCanceled($basket['ID'])) { ?>
                        <? if (
                            !isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) ||
                            (isset($basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE']) && $basket['ORDER']['PROPERTIES']['ALLOW_PAYMENT']['VALUE'] === 'Y')
                        ) {?>
                            <br />(<a href="<?= $arParams['PATH_TO_PAYMENT'] ?>?ORDER_ID=<?= $basket['ORDER']['ID'] ?>"
                                      id="payed_<?= $basket['ID'] ?>"
                                      target="_blank"
                                      
                                      <?php if ((bool) $orderID && in_array($directorGroup, $USER->GetUserGroupArray())) {?>
                                      
                                           class="paylink2" <?php echo $orderID != $ORDER_ID ? 'style="pointer-events: none;cursor:default;"' : 'style="color:red"'; ?> ><?= GetMessage('SPOL_PAY') ?></a>)
                                      <?php } else {?>
                                      
                                      class="paylink2"><?= GetMessage('SPOL_PAY') ?></a>)
                                      
                                      <?php }?>
                            <div style="display: none;" class="payblock">
                                <? include($basket['ORDER']['PAY_SYSTEM']['PSA_ACTION_FILE']); ?>
                            </div>
                        <? } ?>
                    <? } ?>
                <? } ?>
            <? } ?>
            <?/*------------------------ RETURN GOODS -------------------------*/?>
            <? if(LinemediaAutoReturnGoods::isEnabled() &&
                LinemediaAutoReturnGoods::isSupplierEnabled($basket['PROPS']['supplier_id']['VALUE']) &&
                LinemediaAutoReturnGoods::isClientStatusMatch($basket['PROPS']['status']['VALUE'])) { ?>
                <br />(<a href="javascript:void(0);" class="return_goods" rel="<?=$basket['ORDER_ID']?>-<?= $basket['ID'] ?>" title="<?=str_replace('#NAME#', $basket['NAME'], GetMessage('SPOL_RETURN_TITLE'))?>"><?=GetMessage('SPOL_RETURN')?></a>)
            <? } ?>
            <?/*------------------------ /RETURN GOODS -------------------------*/?>
            <?/*------------------------ CANCEL BASKET -------------------------*/?>
            <? if(LinemediaAutoBasket::isClientCancelEnabled($basket['PROPS']['status']['VALUE'])) { ?>
                <? if(LinemediaAutoBasket::isCanceled($basket['ID'])) { ?>
                    <!--br />(<a href="javascript:void(0);" class="remove_cancel_basket" rel="<?=$basket['ORDER_ID']?>-<?= $basket['ID'] ?>" title="<?=str_replace('#NAME#', $basket['NAME'], GetMessage('SPOL_REMOVE_CANCEL_BASKET_TITLE'))?>"><?=GetMessage('SPOL_REMOVE_CANCEL_BASKET')?></a>)-->
                <? } else { ?>
                    <br />(<a href="javascript:void(0);" class="cancel_basket" rel="<?=$basket['ORDER_ID']?>-<?= $basket['ID'] ?>" title="<?=str_replace('#NAME#', $basket['NAME'], GetMessage('SPOL_CANCEL_BASKET_TITLE'))?>"><?=GetMessage('SPOL_CANCEL_BASKET')?></a>)
                <? } ?>
            <? } ?>
            <?/*------------------------ /CANCEL BASKET ------------------------*/?>
        </td>
        <td class="tdbt order_update">
            <?= $basket['DATE_UPDATE'] ?>
        </td>
        <td class="tdb order_status">
            <? if(LinemediaAutoBasket::isCanceled($basket['ID'])) { ?>
                <?= GetMessage('STPOL_BASKET_CANCELED') ?>
            <? } else { ?>
                <? if ($arParams['USE_STATUS_COLOR']) { ?>
                    <span style="font-size: 16px; color: <?= $basket['STATUS_COLOR'] ?>;">&bull;</span>
                <? } ?>
                <?php echo $basket['STATUS_NAME'] ?>
                <?/*------------------------ PRINTABLE DOCUMENTS -------------------------*/?>
                <? if(array_key_exists($basket['ID'], $basket['ORDER']['FILE_LINKS']['basket'])) { ?>
                    <? foreach($basket['ORDER']['FILE_LINKS']['basket'] as $link) { ?>
                        <a target="_blank" title="<?=$link['FILE_NAME']?>" href="<?=$link['SHORT_LINK']?>"><?=$link['TYPE_NAME']?></a><br />
                    <? } ?>
                <? } ?>
                <?/*------------------------ /PRINTABLE DOCUMENTS -------------------------*/?>
            <? } ?>
        </td>
    </tr>
<? } ?>


<div class="silver-block">
<form method="post">
    <?=bitrix_sessid_post();?>
    <div class="filter_orders">
        <table width="100%">
            <tr>
                <td valign="top" width="50%">
                    <h4><?= GetMessage('SPOL_FILTER_ORDER_ID') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="ORDER_ID" value="<?= (!empty($_REQUEST['ORDER_ID']) ? trim($_REQUEST['ORDER_ID']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <h4><?= GetMessage('SPOL_FILTER_NAME') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="NAME" value="<?= (!empty($_REQUEST['NAME']) ? trim($_REQUEST['NAME']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <h4><?= GetMessage('SPOL_FILTER_ARTICLE') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="ARTICLE" value="<?= (!empty($_REQUEST['ARTICLE']) ? trim($_REQUEST['ARTICLE']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <h4><?= GetMessage('SPOL_FILTER_BRAND') ?></h4>
                    <table>
                        <tr>
                            <td><input type="text" name="BRAND" value="<?= (!empty($_REQUEST['BRAND']) ? trim($_REQUEST['BRAND']) : '') ?>" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top">
                    <h4><?= GetMessage('SPOL_PAYED_F') ?></h4>
                    <table>
                        <tr>
                            <td><input type="radio" name="PAYED" value=""<?=($_REQUEST['PAYED'] == '' ? ' checked="checked"' : '')?> /> - <?= GetMessage('SPOL_ALL') ?></td>
                            <td><input type="radio" name="PAYED" value="Y"<?=($_REQUEST['PAYED'] == 'Y' ? ' checked="checked"' : '')?> /> - <?= GetMessage('SPOL_YES') ?></td>
                            <td><input type="radio" name="PAYED" value="N"<?=($_REQUEST['PAYED'] == 'N' ? ' checked="checked"' : '')?> /> - <?= GetMessage('SPOL_NO') ?></td>
                        </tr>
                    </table>
                </td>
                <td valign="top">
                    <h4><?= GetMessage('STPOL_CANCELED') ?></h4>
                    <table>
                        <tr>
                            <td><input type="radio" name="CANCELED" value=""<?=($_REQUEST['CANCELED'] == '' ? ' checked="checked"' : '')?> /> - <?= GetMessage('SPOL_ALL') ?></td>
                            <td><input type="radio" name="CANCELED" value="Y"<?=($_REQUEST['CANCELED'] == 'Y' ? ' checked="checked"' : '')?> /> - <?= GetMessage('SPOL_YES') ?></td>
                            <td><input type="radio" name="CANCELED" value="N"<?=($_REQUEST['CANCELED'] == 'N' ? ' checked="checked"' : '')?> /> - <?= GetMessage('SPOL_NO') ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="filter_orders ">
        <h4><?= GetMessage('SPOL_FILTER_STATUS') ?></h4>
        <table cellpadding="0" cellspacing="0" class="filter_orders">
            <tr>
                <? $i = 1 ?>
                <? foreach ($arResult['STATUSES'] as $status) { ?>
                <td>
                    <input
                        type="checkbox"
                        name="STATUS[<?= $status['ID'] ?>]"
                        id="status-<?= $status['ID'] ?>-id"
                        value="<?= $status['ID'] ?>"
                        <?= ($_REQUEST['STATUS'][$status['ID']]) ? ('checked') : ('') ?>
                    />
                    <label for="status-<?= $status['ID'] ?>-id">
                        <?= $status['NAME'] ?>
                    </label>
                </td>
                <?
                if ($i++ == 4) {
                    echo "</tr><tr>";
                    $i = 1;
                }
                ?>
                <? } ?>
            </tr>
        </table>
        <br/>
        <input type="submit" value="<?= GetMessage('SPOL_FILTER_SHOW') ?>" />
    </div>
</form>

</div>


<div style="clear: both;"></div>
<? if (empty($arResult['BASKETS'])) { ?>
    <center><?= GetMessage('STPOL_NO_ORDERS') ?></center>
<? } else { ?>
    <? if (!empty($arResult['BASKETS'])) { ?>
        <?
            $totalPrice = 0.0;
            $totalCount = 0;
        ?>
        <table cellpadding="0" cellspacing="0" border="0" id="lm-auto-orders-table-id">
            <thead>
                <tr align="center" valign="top">
                    <th filter="false">
                        <div class="tt" style="margin: 5px 5px;"><?= GetMessage('SPOL_HEAD_DATE') ?></div>
                        <div class="dcal"></div>
                    </th>
                    <th>
                        <div class="tt"><?= GetMessage('SPOL_ORDER') ?></div>
                    </th>
                    <th>
                        <div class="tt"><?= GetMessage('SPOL_FIRM') ?></div>
                    </th>
                    <th>
                        <div class="tt"><?= GetMessage('SPOL_ARTICLE') ?></div>
                    </th>
                    <th>
                        <div class="tt"><?= GetMessage('SPOL_DESCRIPTION') ?></div>
                    </th>
                    <th filter="false">
                        <div class="tt"><?= GetMessage('SPOL_QUANTITY') ?></div>
                    </th>
                    <th filter="false">
                        <div class="tt"><?= GetMessage('SPOL_PRICE') ?><?$arCurFormat = CCurrencyLang::GetCurrencyFormat($allCurrency);?><?= "(".$arCurFormat['FULL_NAME'].")"; ?></div>
                    </th>
                    <th filter="false">
                        <div class="tt"><?= GetMessage('SPOL_AMOUNT') ?><?= "(".$arCurFormat['FULL_NAME'].")";?></div>
                    </th>
                    <th filter="false">
                        <div class="tt"><?= GetMessage('SPOL_DELIVERY_PRICE') ?><?= "(".$arCurFormat['FULL_NAME'].")";?></div>
                    </th>
                    <th filter="false">
                        <div class="tt"><?= GetMessage('SPOL_PAYED') ?></div>
                    </th>
                    <th filter="false">
                        <div class="tt"><?= GetMessage('SPOL_CHANGED') ?></div>
                        <div class="dcal"></div>
                    </th>
                    <th filter="false">
                        <div class="tt"><?= GetMessage('SPOL_STATE') ?></div>
                        <div class="dque"></div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <? $first = true; ?>
                <? foreach ($arResult['GROUPS'] as $group) { ?>
                    <? if ($arParams['UNION_BY_ORDERS']) { ?>
                        <? $basket = $arResult['BASKETS'][reset($group)]; ?>
                        <? $class   = ($first) ? ('lm-auto-order-toggle-expand') : ('lm-auto-order-toggle-turn'); ?>
                        <? $display = ($first) ? ('auto') : ('none'); ?>
                        <? $first = false; ?>
                        <tr class="lm-auto-order-group">
                            <td>
                                <?= $basket['DATE_INSERT'] ?>
                            </td>
                            <td colspan="2">
                                <?= GetMessage('SPOL_ORDER') ?>: <b><?= $basket['ORDER_ID'] ?></b>
                            </td>
                            <td colspan="3">
                                <?/*------------------------ PRINTABLE DOCUMENTS -------------------------*/?>
                                <? if(count($basket['ORDER']['FILE_LINKS']['order']) > 0) { ?>
                                    <? foreach($basket['ORDER']['FILE_LINKS']['order'] as $link) { ?>
                                        <a target="_blank" title="<?=$link['FILE_NAME']?>" href="<?=$link['SHORT_LINK']?>"><?=$link['TYPE_NAME']?></a><br />
                                    <? } ?>
                                <? } ?>
                                <?/*------------------------ /PRINTABLE DOCUMENTS -------------------------*/?>
                            </td>
                            <td colspan="2">
                                <b><?=CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['ORDER']['PRICE'], $basket['ORDER']['CURRENCY'], $allCurrency), $allCurrency, $basket['ORDER']['DATE_INSERT']); ?></b>
                            </td>
                            <td>
                                <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($basket['ORDER']['PRICE_DELIVERY'], $basket['ORDER']['CURRENCY'], $allCurrency, $basket['ORDER']['DATE_INSERT']), $allCurrency); ?>
                            </td>
                            <td colspan="3" align="right">
                                <div class="lm-auto-order-toggle <?= $class ?>" rel="<?= $basket['ORDER_ID'] ?>"></div>
                            </td>
                        </tr>
                    <? } ?>
                    <?  // Подсчет общих сумм и вывод корзины.
                        $totalCount += $basket['QUANTITY'];
                    ?>
                    <? foreach ($group as $basketID) {
                         $basket = $arResult['BASKETS'][$basketID];
                        $totalPrice += CCurrencyRates::ConvertCurrency($arParams['UNION_BY_ORDERS']?$basket['ORDER']['PRICE']:($basket['PRICE']*$basket['QUANTITY']), $basket['ORDER']['CURRENCY'], $allCurrency, $basket['ORDER']['DATE_INSERT']);
                         showBasketRow($basket, $arParams, $display, $arResult['obsoleteOrder'], $this->__component->getPath()); ?>
                    <? } ?>
                <? } ?>
            </tbody>
            <tfoot>
                <tr class="tr" align="center" style="height: 40px; font-size: 18px;">
                    <td colspan="5">
                        <?= GetMessage('SPOL_TOTAL') ?>:
                    </td>
                    <td align="center">
                        <?= $arResult['TOTAL_COUNT'] ?>
                    </td>
                    <td colspan="6" align="right">
                        <?= CurrencyFormat(CCurrencyRates::ConvertCurrency($arResult['TOTAL_PRICE'], $basket['ORDER']['CURRENCY'], $allCurrency), $allCurrency, $basket['ORDER']['DATE_INSERT']) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    <? } ?>

    <? if (intval($arResult["NAVRECORDCOUNT"]) > $arResult['COUNT_ON_PAGE'][0] && !isset($_REQUEST['SHOWALL_1'])) { ?>
        <div class="pstr">
            <span><?= GetMessage('SPOL_SHOW_ROWS') ?>:</span>
            <? foreach ($arResult['COUNT_ON_PAGE'] as $countOnPage) { ?>
                <? if ($countOnPage == $_SESSION['COUNT_ON_PAGE']) { ?>
                    <span class="vid"><?= $countOnPage ?></span> |
                <? } else { ?>
                    <a href="?pagesize=<?= $countOnPage ?>"><?= $countOnPage ?></a> |
                <? } ?>
            <? } ?>
        </div>
    <? } ?>
    <br />
    <?= $arResult['NAV_STRING'] ?>
<? } ?>