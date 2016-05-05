<?php

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.quicksearch.js');


?>

<div class="tlm-auto-original kia quicksearch">
    <span><?=GetMessage('LM_AUTO_QUICK_FILTER')?></span><br />
    <input type="text" name="quick_search" id="quick_search" placeholder="<?=GetMessage('LM_AUTO_QUICK_FILTER_HINT')?>" />
</div>