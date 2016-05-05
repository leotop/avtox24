<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$APPLICATION->AddHeadScript($templateFolder . '/js/jquery.tablesorter.min.js');

if (!empty($arResult)) { ?>

<div class="silver-block">

<p><strong><?=GetMessage('LM_AUTO_TRANSACTIONS_CASH')?></strong>:
    <? if(count($arResult['cash']) > 0) { ?>
        <? foreach($arResult['cash'] as $currency => $cash) { ?>
            <span class="big-font-price"><?=$cash?></span>
        <? } ?>
    <? } ?>
</p>

    <form id="nonpayed_orders" method="post" action="<?=$arParams['ORDERS_PATH']?>?SHOWALL_1=1">
        <p>
            <strong><?=GetMessage('LM_AUTO_TRANSACTIONS_TO_PAY')?></strong>: <span class="big-font-price"><?=LinemediaAutoPrice::userPrice($arResult['sum_to_pay_currency']);?></span><input class="nonpayed_orders_input btn" type="submit" form="nonpayed_orders" value="<?=GetMessage('LM_AUTO_TRANSACTIONS_TO_PAY_ORDERS')?>">
        </p>

		<input type="hidden" name="PAYED" value="N">
		<input type="hidden" name="CANCELED" value="N">
		<? foreach ($arResult['STATUSES'] as $key => $status) {
			 if (in_array($status['ID'], $arResult['EXCLUDED_STATUSES'])) continue;?>
        	<input type="hidden" name="STATUS[<?=$status['ID']?>]" value="<?=$status['ID']?>">
		<?}?>
    </form>

<label><?=GetMessage('LM_AUTO_TRANSACTIONS_DATE_FILTER')?>:</label>

<form action="<?=$APPLICATION->GetCurPage()?>" method="POST" name="time_period">

    <?//echo CalendarPeriod("date_from", "{$arResult['date_from']}", "date_to", "{$arResult['date_to']}", "time_period", "N")?>

    <?$APPLICATION->IncludeComponent(
	"bitrix:main.calendar", 
	".default", 
	array(
		"SHOW_INPUT" => "Y",
		"FORM_NAME" => "time_period",
		"INPUT_NAME" => "date_from",
		"INPUT_NAME_FINISH" => "date_to",
		"INPUT_VALUE" => "{$arResult['date_from']}",
		"INPUT_VALUE_FINISH" => "{$arResult['date_to']}",
		"SHOW_TIME" => "N",
		"HIDE_TIMEBAR" => "N"
	),
	false
);?>
<table class="transactions_filter">

    <tr>

        <td>

            <label for="trans_id"><?=GetMessage('LM_AUTO_TRANSACTIONS_ID_FILTER')?>:</label>

            <input type="text" size="40" name="trans_id" id="trans_id" value="<?=$arResult['trans_id']?>">

        </td>

        <td>

            <label for="order_id"><?=GetMessage('LM_AUTO_TRANSACTIONS_ORDER_ID_FILTER')?>:</label>

            <input type="text" size="40" name="order_id" id="order_id" value="<?=$arResult['order_id']?>">

        </td>

    </tr>

</table>

    <input type="submit" class="btn" value="<?=GetMessage('LM_AUTO_TRANSACTIONS_SHOW')?>">

</form>

</div>

<table class="lm-auto-transactions">

    <thead>

        <tr>

            <th class="id"><?=GetMessage('LM_AUTO_TRANSACTIONS_NUM')?></th>

            <th class="sum"><?=GetMessage('LM_AUTO_TRANSACTIONS_SUM')?></th>

            <th class="description"><?=GetMessage('LM_AUTO_TRANSACTIONS_DESCRIPTION')?></th>

            <th class="order-id"><?=GetMessage('LM_AUTO_TRANSACTIONS_ORDER_ID')?></th>

            <th class="date"><?=GetMessage('LM_AUTO_TRANSACTIONS_TRANS_DATE')?></th>

        </tr>

    </thead>

    <tbody>



    <?php foreach ($arResult['transactions'] as $key => $transaction) { ?>

        <tr<?=($transaction['CLOSED_BY_DEPOSIT'] ? ' class="closed"' : '')?>>

            <td><?=$transaction['ID']?></td>

            <td><span title="<?=(int)$transaction["AMOUNT"]?>"><?=($transaction["DEBIT"]=="Y")?"+ ":"- "?><?=SaleFormatCurrency($transaction["AMOUNT"], $transaction["CURRENCY"])?><br /><small>(<?=($transaction["DEBIT"]=="Y")? GetMessage('LM_AUTO_TRANSACTIONS_PLUS_SUM'):GetMessage('LM_AUTO_TRANSACTIONS_MIN_SUM')?>)</small></span></td>

             <td>
                 <b><?=GetMessage($transaction['TYPE'])?><?=$transaction['REMARK']?></b><br />
                 <!--small><?=$transaction['NOTES']?></small-->
             </td>

            <td><a target="_blank" href="<?=$arParams['ORDERS_PATH']?>?ORDER_ID=<?=$transaction['ORDER_ID']?>"><?=$transaction['ORDER_ID']?></a></td>

            <td><span title="<?=MakeTimeStamp($transaction['TRANSACT_DATE'])?>"><?=$transaction['TRANSACT_DATE']?></span></td>

        </tr>

    <?php } ?>



    </tbody>

</table>

    <?php

}

