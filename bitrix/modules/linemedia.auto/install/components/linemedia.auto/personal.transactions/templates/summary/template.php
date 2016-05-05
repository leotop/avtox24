<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!empty($arResult)) { ?>
    <p>Установленный кредит: <b><nobr><?=$arResult['CREDIT']?></nobr></b></p>
    <p>Доступный остаток: <b><nobr><?=$arResult['cash']?></nobr></b></p>
    <!--p>Зарезервировано: <b><nobr><?=$arResult['sum_to_pay_currency']?></nobr></b></p-->
    <? foreach($arResult['STATUSES'] as $status) {
        if(intval($status['SUM']) > 0) { ?>
            <p><?=$status['NAME']?>: <b><nobr><?=LinemediaAutoPrice::userPrice($status['SUM']);?></nobr></b></p>
    <? }
    } ?>
    <p>Дата выплаты по кредиту: <b><nobr><?=ConvertTimestamp(time() + 60*60*24*$arResult['DELAY'])?></nobr></b></p>

    <input type="button" style="width:170px; height:30px;" id="price" class="btn" value="Зачислить" onclick="window.location.href = '/personal/pay/';">
    <hr />

<?
}

