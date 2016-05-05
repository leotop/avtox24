<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<div id="lm-auto-select-brands-wrap" class="lm-auto-select-wrap">
    <select id="lm-auto-select-brands-id" name="auto-select-brands">
        <option>(<?= GetMessage('LM_AUTO_GARAGE_CHOOSE_BRAND') ?>)</option>
        <? foreach ($arResult['brands'] as $item) { ?>
            <option value="<?= $item['manuId'] ?>"><?= $item['manuName'] ?></option>
        <? } ?>
    </select>
</div>

<div id="lm-auto-select-models-wrap" class="lm-auto-select-wrap"></div>

<div id="lm-auto-select-modifications-wrap" class="lm-auto-select-wrap"></div>