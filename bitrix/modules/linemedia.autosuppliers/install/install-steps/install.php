<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? IncludeModuleLangFile(__FILE__); ?>

<form action="<?= $APPLICATION->GetCurPage() ?>" id="lm_auto_main" class="well" method="post">
	<?= bitrix_sessid_post() ?>
	<input type="hidden" name="lang" value="<?= LANG ?>" />
	<input type="hidden" name="id" value="linemedia.autosuppliers" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="install_step_id" value="finish" />
	
    <h2><?=GetMessage('LM_AUTO_SUPPLIERS_TITLE')?></h2>
    <!--p>
        <label>
            <?=GetMessage('LM_AUTO_SUPPLIERS_INSTALL_STATUSES')?>
            <input type="checkbox" name="install-statuses" value="Y" />
        </label>
    </p-->
    <p>
        <input type="submit" value="<?=GetMessage('LM_AUTO_SUPPLIERS_CONTINUE')?>" />
    </p>
</form>
