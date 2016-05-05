<?php
if(CModule::IncludeModule('linemedia.autobranches')) {

    $director = new LinemediaAutoBranchesDirector($USER->GetID());
    $arResult['DELAY'] = $director->getCurrrentDelay();
    $propCredit = $director->getBranch()->getProperty('credit');
    $credit = intval($propCredit['VALUE']);
    $arResult['CREDIT'] = SaleFormatCurrency($credit, CCurrency::GetBaseCurrency());

    foreach($arResult['BASKETS'] as $basket) {
        $status = $basket['PROPS']['status']['VALUE'];
        $arResult['STATUSES'][$status]['SUM'] += $basket['PRICE'];
    }
}