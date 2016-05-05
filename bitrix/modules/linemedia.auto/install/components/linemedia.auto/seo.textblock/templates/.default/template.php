<?
if (!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true) die();


?>
<div class="textblock">
    <?=$arResult['TEXT']?>
<?if ($APPLICATION->GetShowIncludeAreas()) {?>
    <a title="Ğåäàêòèğîâàòü îáëàñòü" href="javascript:void(0);" data-target="<?=$arParams['WHAT_SHOW']?>" data-id="<?=$arResult['FOUND']?$arResult['ELEMENT']['ID']:0?>" class="seoBlockEdit">
        <img src="<?=$this->GetFolder()?>/i/edit.png">
    </a>
<?}?>
</div>