<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<? $summ = $arResult['ACCOUNT_LIST']['0']['ACCOUNT_LIST']['CURRENT_BUDGET'] ?>
<?// $summ = current(explode('.', $summ));?>
<? $summ = number_format($summ, 0, '.', ' '); ?>

<? $curr = $arResult["ACCOUNT_LIST"]["0"]["CURRENCY"]["FORMAT_STRING"] ?>
<? $curr = str_replace("# ", "", $curr); ?>

<span class="invoice"><a title="Сумма на вашем счете" href="/auto/balance/"><strong><?=$summ?> <?=$curr?></strong></a></span>